<?php


namespace TheCodingMachine\GraphQLite\Laravel\Mappers\Parameters;


use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Validation\Factory as ValidationFactory;
use TheCodingMachine\GraphQLite\Laravel\Exceptions\ValidateException;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use function array_combine;
use function array_keys;
use function implode;

class ParameterValidator implements InputTypeParameterInterface
{
    /**
     * @var InputTypeParameterInterface
     */
    private $parameter;
    /**
     * @var string
     */
    private $rules;
    /**
     * @var ValidationFactory
     */
    private $validationFactory;
    /**
     * @var string
     */
    private $parameterName;

    public function __construct(InputTypeParameterInterface $parameter, string $parameterName, string $rules, ValidationFactory $validationFactory)
    {
        $this->parameter = $parameter;
        $this->rules = $rules;
        $this->validationFactory = $validationFactory;
        $this->parameterName = $parameterName;
    }


    /**
     * @param array<string, mixed> $args
     * @param mixed $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        $value = $this->parameter->resolve($source, $args, $context, $info);

        $validator = $this->validationFactory->make([$this->parameterName => $value], [$this->parameterName => $this->rules]);

        if ($validator->fails()) {
            $errorMessages = [];
            foreach ($validator->errors()->toArray() as $field => $errors) {
                $errorMessages[] = implode(', ', $errors);
            }
            throw new ValidateException(implode(', ', $errorMessages));
        }

        return $value;
    }

    public function getType(): InputType
    {
        return $this->parameter->getType();
    }

    public function hasDefaultValue(): bool
    {
        return $this->parameter->hasDefaultValue();
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->parameter->getDefaultValue();
    }
}
