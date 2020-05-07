<?php

namespace App\Http\Controllers\Api\Admin\HandBook;

use App\Enums\User\UserCategoriesSpecialists;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;

class UserCategoriesSpecialistHandBookController extends BaseApiController
{

    /**
     * @SWG\Get(
     *      path="/api/handbook/user_category_specialist/list",
     *      tags={"Справочники"},
     *      summary="Категории специалистов",
     *      description="Возвращает список категорий специалистов",
     *      @SWG\Response(
     *          response=200,
     *          description="success"
     *      )
     * )
     */
    public function list(Request $request)
    {

        $listResponse = collect();
        foreach (UserCategoriesSpecialists::getAllTranslated() as $key => $userType) {
            $listResponse->push([
                'key' => $key,
                'label' => $userType,
            ]);
        }

        return $listResponse;
    }

}
