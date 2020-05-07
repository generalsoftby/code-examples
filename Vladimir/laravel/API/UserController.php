<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\Admin\User\UserCollectionResource;
use App\Http\Resources\Admin\User\UserResource;
use App\Repositories\Admin\User\UserRepository;

class UserController extends BaseApiController
{
    protected $userRepository;

    public function __construct(UserRepository $users)
    {
        $this->userRepository = $users;
    }

    /**
     * @SWG\Get(
     *      path="/api/admin/user/list",
     *      tags={"Админка"},
     *      summary="Список пользователей",
     *      description="Возвращает список пользователей",
     *      @SWG\Parameter(
     *          name="flt_type",
     *          description="Фильтр - Тип пользователя",
     *          required=false,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="flt_fio",
     *          description="Фильтр - ФИО",
     *          required=false,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="flt_email",
     *          description="Фильтр - Email",
     *          required=false,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="flt_admin",
     *          description="Фильтр - Администратор",
     *          required=false,
     *          type="boolean",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="sort",
     *          description="register_asc, last_action_desc, last_action_asc, name_asc, name_desc",
     *          required=false,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="page",
     *          description="Пагинация - номер страницы",
     *          required=false,
     *          type="integer",
     *          in="query"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="success"
     *      )
     * )
     */
    public function list(Request $request)
    {

        $filter = $request->all();
        $users = $this->userRepository->getList($filter);

        return new UserCollectionResource($users);
    }

    /**
     * @SWG\Put(
     *      path="/api/admin/user/set/is_admin/{user_id}",
     *      tags={"Админка"},
     *      summary="Установить/снять идентификатор администратора",
     *      description="Установить/снять идентификатор администратора",
     *      @SWG\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="Public id пользователя",
     *         required=true,
     *         type="integer",
     *     ),
     *      @SWG\Parameter(
     *          name="is_admin",
     *          description="Да / Нет",
     *          required=false,
     *          type="boolean",
     *          in="query"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="success"
     *      )
     * )
     */
    public function setIsAdmin(Request $request, User $user)
    {
        $isAdminRequest = $request->get('is_admin');
        $isAdmin = ($isAdminRequest === 'true') ? true : false;
        $user->save();
        $user = $this->userRepository->setIsAdmin($user, $isAdmin);

        return new UserResource($user);
    }



}
