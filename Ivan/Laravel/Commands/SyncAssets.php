<?php

namespace App\Console\Commands;

use App\Exceptions\SteamInventoryLoadingException;
use App\Models\Bot;
use App\Services\SteamInventoryService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SyncAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize assets from inventories of bots';

    /**
     * Execute the console command.
     *
     * @param SteamInventoryService $steamInventoryService
     */
    public function handle(SteamInventoryService $steamInventoryService)
    {
        /** @var Bot[]|Collection $bots */
        $bots = Bot::query()
            ->where('enabled', true)
            ->get();

        foreach ($bots as $bot) {
            sleep(5);

            try {
                $inventoryItems = $steamInventoryService->fetch($bot->steamid);
            } catch (SteamInventoryLoadingException $exception) {
                logger()->warning('Failed to get inventory of bot ' . $bot->id);
                logger()->warning(get_class($exception) . ' in ' . $exception->getFile());
                continue;
            }

            $steamInventoryService::syncOwnerAssetsWithInventoryItems($bot, $inventoryItems);

            logger()->info('Successfully synced assets of bot ' . $bot->id . ' with inventory items');
        }
    }
}
