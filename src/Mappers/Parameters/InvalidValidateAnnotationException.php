<?php


namespace TheCodingMachine\GraphQLite\Laravel\Mappers\Parameters;


use ReflectionParameter;

class InvalidValidateAnnotationException extends \Exception
{
    public static function canOnlyValidateInputType(ReflectionParameter $refParameter): self
    {
        $class = $refParameter->getDeclaringClass();
        $method = $refParameter->getDeclaringFunction();
        return new self('In method '.$class.'::'.$method.', the @Validate annotation is targeting parameter $'.$refParameter->getName().'. You cannot target this parameter because it is not part of the GraphQL Input type. You can only validate parameters coming from the end user.');
    }
}