<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Laravel\Mappers\Parameters;

use Closure;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use ReflectionParameter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterHandlerInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewareInterface;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameterInterface;
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
use function array_map;
use function array_merge;
use function implode;
use function is_array;
use function is_object;
use Illuminate\Validation\Factory as ValidationFactory;

/**
 * A parameter middleware that reads "Validate" annotations.
 */
class ValidateFieldMiddleware implements ParameterMiddlewareInterface
{
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    public function __construct(ValidationFactory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function mapParameter(ReflectionParameter $refParameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        /** @var Validate[] $validateAnnotations */
        $validateAnnotations = $parameterAnnotations->getAnnotationsByType(Validate::class);

        $parameter = $next->mapParameter($refParameter, $docBlock, $paramTagType, $parameterAnnotations);

        if (empty($validateAnnotations)) {
            return $parameter;
        }

        if (!$parameter instanceof InputTypeParameterInterface) {
            throw InvalidValidateAnnotationException::canOnlyValidateInputType($refParameter);
        }

        // Let's wrap the ParameterInterface into a ParameterValidator.
        $rules = array_map(static function(Validate $validateAnnotation): string { return $validateAnnotation->getRule(); }, $validateAnnotations);

        return new ParameterValidator($parameter, $refParameter->getName(), implode('|', $rules), $this->validationFactory);
    }
}
