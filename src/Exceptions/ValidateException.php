<?php

namespace TheCodingMachine\GraphQLite\Laravel\Exceptions;

use GraphQL\Error\ClientAware;

class ValidateException extends \Exception implements ClientAware
{
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
    
    

}
