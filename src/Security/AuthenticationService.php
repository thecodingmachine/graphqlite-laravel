<?php


namespace TheCodingMachine\GraphQLite\Laravel\Security;

use Illuminate\Contracts\Auth\Guard;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var Guard
     */
    private $guard;

    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Returns true if the "current" user is logged
     */
    public function isLogged(): bool
    {
        return $this->guard->check();
    }

    /**
     * Returns an object representing the current logged user.
     * Can return null if the user is not logged.
     */
    public function getUser(): ?object
    {
        return $this->guard->user();
    }
}
