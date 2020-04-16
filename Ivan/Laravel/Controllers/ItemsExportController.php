<?php

namespace App\Http\Controllers\Site;

use App\Enums\Parameters;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Parameter;
use App\Models\Bot;
use App\Services\SteamInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class ItemsExportController
 * @package App\Http\Controllers\Site
 */
class ItemsExportController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $data = Cache::remember('Items.Export', 1, function () {
            $query = Item::query();

            if (Parameter::getValue(Parameters::USER_ASSETS_UNSTABLE_RESTRICTED)) {
                $query->where('stable', true);
            }

            $items = $query->get();

            $data = $items->map(function (Item $item) {
                return
                    [
                        'name' => $item->hash,
                        'price' => SteamInventoryService::getAdjustedPrice($item, Bot::class),
                        'count' => $item->stat_assets_count,
                        'max' => (int)(($item->assets_count_max === null) ? Parameter::getValue(Parameters::ITEMS_ASSETS_COUNT_MAX) : $item->assets_count_max),
                    ];
            });

            return $data;
        });

        return response()->json($data);
    }
}
