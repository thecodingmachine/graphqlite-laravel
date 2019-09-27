<?php


namespace TheCodingMachine\GraphQLite\Laravel\Security;

use Illuminate\Contracts\Auth\Access\Gate;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * @var Gate
     */
    private $gate;
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    public function __construct(Gate $gate, AuthenticationServiceInterface $authenticationService)
    {
        $this->gate = $gate;
        $this->authenticationService = $authenticationService;
    }

    /**
     * Returns true if the "current" user has access to the right "$right"
     *
     * @param mixed $subject The scope this right applies on. $subject is typically an object or a FQCN. Set $subject to "null" if the right is global.
     */
    public function isAllowed(string $right, $subject = null): bool
    {
        return $this->gate->forUser($this->authenticationService->getUser())->check($right, $subject);
    }
}
