<?php

namespace App\Repositories\Database;

use App\Annotations\Repositories\EntityRepository;
use App\Dto\Request\Login\LoginInputDto;
use App\Exceptions\Repositories\NotFoundException;
use App\Exceptions\Repositories\WrongPasswordException;
use Generated\Models\User;

/**
 * @EntityRepository(User::class)
 */
abstract class UserRepository extends AbstractDatabaseRepository
{
    /**
     * @param LoginInputDto $loginInputDto
     * @return User
     * @throws NotFoundException
     * @throws WrongPasswordException
     */
    public abstract function findByLoginInput(LoginInputDto $loginInputDto);

}
