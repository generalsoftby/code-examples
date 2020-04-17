<?php


namespace App\Repositories\Database;

use App\Annotations\Repositories\EntityRepository;
use App\Dto\Request\Login\TokenInputDto;
use App\Exceptions\Repositories\NotFoundException;
use App\Repositories\Interfaces\TokenExistsChecker;
use Generated\Models\UserToken;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * @EntityRepository(UserToken::class)
 */
abstract class UserTokenRepository extends AbstractDatabaseRepository implements TokenExistsChecker
{

    /**
     * @param TokenInputDto $inputDto
     * @return mixed
     */
    public function tokenExist(TokenInputDto $inputDto)
    {
        try{
            return $this->findByToken($inputDto);
        }catch (NotFoundException $exception){
            return false;
        }
    }

    /**
     * @param UserToken $token
     * @return UserToken
     */
    public function saveUserToken(UserToken $token){
        EntityManager::persist($token);
        EntityManager::flush();

        return $token;
    }

    /**
     * @param TokenInputDto $tokenInputDto
     * @return UserToken
     * @throws NotFoundException
     */
    public abstract function findByToken(TokenInputDto $tokenInputDto);

}
