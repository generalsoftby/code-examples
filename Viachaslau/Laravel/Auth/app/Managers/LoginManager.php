<?php

namespace App\Managers\Users;

use App\Dto\Request\Login\LoginInputDto;
use App\Dto\Request\Login\LoginRefreshInputDto;
use App\Dto\Request\Login\SecretInputDto;
use App\Dto\Request\Login\TokenInputDto;
use App\Dto\Response\LoginResponse;
use App\Exceptions\Managers\TokenExpiredException;
use App\Exceptions\Repositories\NotFoundException;
use App\Exceptions\Repositories\WrongPasswordException;
use App\Managers\Core\AbstractManager;
use App\Repositories\Database\UserRepository;
use App\Repositories\Database\UserTokenRepository;
use App\Utils\TokenGenerator;
use Carbon\Carbon;
use Generated\Enums\Core\ExternalSystemsEnum;
use Generated\Enums\Web\FrontendRoutesEnum;
use Generated\Models\User;
use Generated\Models\UserToken;
use Illuminate\Support\Facades\Hash;

class LoginManager extends AbstractManager
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserTokenRepository
     */
    private $userTokenRepository;

    /**
     * @var User|null
     */
    private $currentUser;

    /**
     * @var int
     */
    private $ttl;

    /**
     * LoginManager constructor.
     * @param UserRepository $userRepository
     * @param UserTokenRepository $userTokenRepository
     */
    public function __construct(UserRepository $userRepository, UserTokenRepository $userTokenRepository)
    {
        $this->userRepository = $userRepository;
        $this->userTokenRepository = $userTokenRepository;
        $this->currentUser = null;
        $this->ttl = config('auth.token_ttl', 0);
    }


    /**
     * @param LoginInputDto $loginInputDto
     * @return LoginResponse
     * @throws NotFoundException
     */
    public function login(LoginInputDto $loginInputDto): LoginResponse
    {
        /** @var User $user */
        $user = $this->userRepository->findByLoginInput($loginInputDto);

        /** @var LoginResponse $token */
        $response = $this->createTokens($user, $loginInputDto->getSecret());

        return $response;
    }

    /**
     * @param TokenInputDto $tokenInputDto
     * @throws TokenExpiredException|NotFoundException
     */
    public function setCurrentUserByToken(TokenInputDto $tokenInputDto)
    {
        /** @var UserToken $token */
        $token = $this->userTokenRepository->findByToken($tokenInputDto);
        if ($this->ttl && Carbon::now()->greaterThan($token->getTokenExpiredAt())) {
            throw new TokenExpiredException();
        }

        if ($this->ttl) {
            $token->setTokenExpiredAt(Carbon::now()->addHours($this->ttl));
            $this->userTokenRepository->saveUserToken($token);
        }

        $this->currentUser = $token->getUser();
    }

    /**
     * @param LoginRefreshInputDto $inputDto
     * @return LoginResponse
     * @throws NotFoundException
     * @throws WrongPasswordException
     */
    public function updateUserTokens(LoginRefreshInputDto $inputDto): LoginResponse
    {
        $tokenInputDto = new TokenInputDto();
        $tokenInputDto->token = $inputDto->getToken();
        /** @var UserToken $token */
        $token = $this->userTokenRepository->findByToken($tokenInputDto);
        $user = $token->getUser();

        if (!$user) {
            throw new NotFoundException();
        }
        if (!Hash::check($inputDto->getPassword(), $user->getPassword())) {
            throw new WrongPasswordException();
        }

        /** @var LoginResponse $token */
        $response = $this->createTokens($user, $inputDto->getSecret());
        $this->userTokenRepository->remove($token);

        return $response;
    }

    /**
     * @param User $user
     * @param SecretInputDto $dto
     * @return LoginResponse
     */
    protected function createTokens(User $user, SecretInputDto $dto)
    {
        $this->updateExternalUserTokens($user, $dto);
        /** @var UserToken $token */
        $token = $this->createUserToken($user);

        return new LoginResponse(
            $token->getToken(),
            $user->getFirstname(),
            $user->getMiddlename(),
            $user->getLastname()
        );
    }

    /**
     * @param TokenInputDto $tokenInputDto
     */
    public function removeToken(TokenInputDto $tokenInputDto)
    {
        /** @var UserToken $token */
        $token = $this->userTokenRepository->findByToken($tokenInputDto);
        $this->userTokenRepository->remove($token);
    }

    public function removeAllTokens()
    {
        $this->userTokenRepository->clear();
    }

    protected function createUserToken(User $user): UserToken
    {
        $token = new UserToken();

        $token->setToken((new TokenGenerator($this->userTokenRepository))->generate()->getToken());
        $token->setTokenExpiredAt(Carbon::now()->addHours($this->ttl));
        $user->addToken($token);

        $this->userTokenRepository->saveUserToken($token);

        return $token;
    }

    protected function updateExternalUserTokens(User $user, SecretInputDto $dto)
    {
        /** @var \App\Enums\Core\ExternalSystemsEnum $externalSystem */
        foreach (ExternalSystemsEnum::values() as $externalSystem) {
            if ($externalSystem->getExternalUserTokenUpdater()->isNeedUpdate($user)) {
                $externalSystem->getExternalUserTokenUpdater()->updateToken($user, $dto);
            }
        }
    }

    /**
     * @return User|null
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

}
