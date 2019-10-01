<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Laravel\Middlewares;

use Closure;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface;
use TheCodingMachine\GraphQLite\Laravel\Exceptions\ValidateException;
use Throwable;
use Webmozart\Assert\Assert;
use function array_combine;
use function array_keys;
use function array_merge;
use function is_array;
use function is_object;
use Illuminate\Validation\Factory as ValidationFactory;

/**
 * A field middleware that reads "Security" Symfony annotations.
 */
class ValidateFieldMiddleware implements FieldMiddlewareInterface
{
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    public function __construct(ValidationFactory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition
    {
        $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();
        /** @var Validate[] $validateAnnotations */
        $validateAnnotations = $annotations->getAnnotationsByType(Validate::class);

        if (empty($validateAnnotations)) {
            return $fieldHandler->handle($queryFieldDescriptor);
        }

        $callable = $queryFieldDescriptor->getCallable();
        Assert::notNull($callable);

        $parameters = $queryFieldDescriptor->getParameters();

        $rules = [];
        foreach ($validateAnnotations as $validateAnnotation) {
            $rules[$validateAnnotation->getTarget()] = $validateAnnotation->getRule();
        }

        $queryFieldDescriptor->setCallable(function (...$args) use ($rules, $callable, $parameters) {
            $argsName = array_keys($parameters);
            $argsByName = array_combine($argsName, $args);

            $validator = $this->validationFactory->make($argsByName, $rules);

            if ($validator->fails()) {
                $errorMessages = [];
                foreach ($validator->errors()->toArray() as $field => $errors) {
                    $errorMessages[] = implode(', ', $errors);
                }
                throw new ValidateException(implode(', ', $errorMessages));
            }

            return $callable(...$args);
        });

        return $fieldHandler->handle($queryFieldDescriptor);
    }
}
