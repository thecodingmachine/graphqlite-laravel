<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Laravel\Annotations;

use BadMethodCallException;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface;
use function ltrim;

/**
 * Use this annotation to validate a parameter for a query or mutation.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("for", type = "string"),
 *   @Attribute("rule", type = "string")
 * })
 */
class Validate implements ParameterAnnotationInterface, MiddlewareAnnotationInterface
{
    /** @var string */
    private $for;
    /** @var string */
    private $rule;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        if (! isset($values['for'])) {
            throw new BadMethodCallException('The @Validate annotation must be passed a target. For instance: "@Validate(for="$email", rule="email")"');
        }
        if (! isset($values['rule'])) {
            throw new BadMethodCallException('The @Validate annotation must be passed a rule. For instance: "@Validate(for="$email", rule="email")"');
        }
        $this->for = ltrim($values['for'], '$');
        $this->rule = $values['rule'] ?? null;
    }

    public function getTarget(): string
    {
        return $this->for;
    }

    public function getRule(): string
    {
        return $this->rule;
    }
}
