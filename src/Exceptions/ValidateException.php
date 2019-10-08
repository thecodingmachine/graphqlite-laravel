<?php

namespace TheCodingMachine\GraphQLite\Laravel\Exceptions;

use GraphQL\Error\ClientAware;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface;

class ValidateException extends \Exception implements GraphQLExceptionInterface
{
    /**
     * @var string
     */
    private $argumentName;

    public static function create(string $message, string $argumentName)
    {
        $exception = new self($message, 400);
        $exception->argumentName = $argumentName;
        return $exception;
    }

    /**
     * @return bool
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return 'Validate';
    }


    /**
     * Returns the "extensions" object attached to the GraphQL error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return [
            'argument' => $this->argumentName
        ];
    }
}
