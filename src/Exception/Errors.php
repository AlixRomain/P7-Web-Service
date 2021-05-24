<?php

namespace App\Exception;

use FOS\RestBundle\Controller\AbstractFOSRestController;

class Errors extends AbstractFOSRestController
{
    /**
     * @throws ResourceValidationException
     */
    public function violation($violations)
    {
        if(count($violations)) {
            $message = 'The JSON sent contains invalid data : ' ;

            foreach ($violations as $violation){
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
          throw new ResourceValidationException($message);
        }
    }
}


