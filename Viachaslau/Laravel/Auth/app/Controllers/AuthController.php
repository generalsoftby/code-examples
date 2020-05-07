<?php

namespace App\Http\Controllers;

use App\Dto\Request\Login\LoginInputDto;
use App\Dto\Request\Login\LoginRefreshInputDto;
use App\Dto\Request\Login\TokenInputDto;
use App\Dto\Response\LoginResponse;
use App\Exceptions\Managers\TokenExpiredException;
use App\Exceptions\Repositories\ExternalLoginException;
use App\Exceptions\Repositories\NotFoundException;
use App\Exceptions\Repositories\WrongPasswordException;
use App\Exceptions\ValidateException;
use App\Managers\Core\DtoManager;
use App\Managers\Users\LoginManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/login",
     *     @OA\RequestBody(
     *         required=true,
     *         description="",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     description="login",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="password",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 description="token",
     *             ),
     *             @OA\Property(
     *                 property="firstName",
     *                 type="string",
     *                 description="firstName",
     *             ),
     *             @OA\Property(
     *                 property="middleName",
     *                 type="string",
     *                 description="middleName",
     *             ),
     *             @OA\Property(
     *                 property="lastName",
     *                 type="string",
     *                 description="lastName",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="message",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="wrong password",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="message",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="validate error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="message",
     *             ),
     *         )
     *     ),
     * )
     * @param Request $request
     * @return LoginResponse
     */
    public function login(Request $request): LoginResponse
    {
        try {
            $loginDto = DtoManager::getManager()->generateByRequest(LoginInputDto::class, $request->toArray());
            return LoginManager::getManager()->login($loginDto);
        } catch (ValidateException $validateException) {
            throw new HttpException(422, $validateException->getMessage());
        } catch (NotFoundException $notFoundException) {
            throw new HttpException(404);
        } catch (WrongPasswordException $notFoundException) {
            throw new HttpException(401);
        } catch (ExternalLoginException $externalLoginException) {
            throw new HttpException(401, $externalLoginException->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login/refresh",
     *     @OA\Parameter(
     *         name="X-API-KEY",
     *         description="temporary_token",
     *         required=true,
     *         in="header",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="password",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 description="token",
     *             ),
     *             @OA\Property(
     *                 property="firstName",
     *                 type="string",
     *                 description="firstName",
     *             ),
     *             @OA\Property(
     *                 property="middleName",
     *                 type="string",
     *                 description="middleName",
     *             ),
     *             @OA\Property(
     *                 property="lastName",
     *                 type="string",
     *                 description="lastName",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="wrong password",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="message",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="token not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="message",
     *             ),
     *         )
     *     ),
     * )
     * @param Request $request
     * @return LoginResponse
     */
    public function refresh(Request $request)
    {
        try {
            $dto = new LoginRefreshInputDto();
            $dto->token = $request->header('X-API-KEY');
            $dto->password = $request->get('password');

            return LoginManager::getManager()->updateUserTokens($dto);
        } catch (ValidateException $validateException) {
            if ($validateException->getField() === 'token'){
                throw new HttpException(403);
            }
            if ($validateException->getField() === 'password'){
                throw new HttpException(401);
            }
            throw new HttpException(500, $validateException->getMessage());
        } catch (NotFoundException $notFoundException) {
            throw new HttpException(403);
        } catch (WrongPasswordException $notFoundException) {
            throw new HttpException(401);
        } catch (ExternalLoginException $externalLoginException) {
            throw new HttpException(401, $externalLoginException->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     @OA\Parameter(
     *         name="X-API-KEY",
     *         description="temporary_token",
     *         required=true,
     *         in="header",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="token not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="message",
     *             ),
     *         )
     *     ),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        try{
            $dto = new TokenInputDto();
            $dto->token = $request->header('X-API-KEY');
            LoginManager::getManager()->removeToken($dto);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (ValidateException $validateException) {
            throw new HttpException(401, $validateException->getMessage());
        } catch (NotFoundException|TokenExpiredException $notFoundException) {
            throw new HttpException(401);
        }
    }
}
