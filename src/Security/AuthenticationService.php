<?php


namespace TheCodingMachine\GraphQLite\Laravel\Security;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var AuthFactory
     */
    private $auth;
    /**
     * @var array|string[]
     */
    private $guards;

    /**
     * @param string[] $guards
     */
    public function __construct(AuthFactory $auth, array $guards)
    {
        $this->auth = $auth;
        $this->guards = $guards;
    }

    /**
     * Returns true if the "current" user is logged
     */
    public function isLogged(): bool
    {
        foreach ($this->guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns an object representing the current logged user.
     * Can return null if the user is not logged.
     */
    public function getUser(): ?object
    {
        foreach ($this->guards as $guard) {
            $user = $this->auth->guard($guard)->user();
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
