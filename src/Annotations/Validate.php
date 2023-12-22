<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Laravel\Annotations;

use Attribute;
use BadMethodCallException;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface;
use function is_string;
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
#[Attribute(Attribute::TARGET_PARAMETER)]
class Validate implements ParameterAnnotationInterface
{
    /** @var string */
    private $for;
    /** @var string */
    private $rule;

    /**
     * @param array<string, mixed>|string $rule
     */
    public function __construct($rule = [])
    {
        $values = $rule;
        if (is_string($values)) {
            $this->rule = $values;
        } else {
            $this->rule = $values['rule'] ?? null;
            if (isset($values['for'])) {
                $this->for = ltrim($values['for'], '$');
            }
        }
        if (empty($this->rule)) {
            throw new BadMethodCallException('The @Validate annotation must be passed a rule. For instance: "#Validate("email")" in PHP 8+ or "@Validate(for="$email", rule="email")" in PHP 7+');
        }
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The @Validate annotation must be passed a target. For instance: "@Validate(for="$email", rule="email")"');
        }
        return $this->for;
    }

    public function getRule(): string
    {
        return $this->rule;
    }
}
