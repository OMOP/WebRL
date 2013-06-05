<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    20 Dec 2010

    Factory validator class that gets DbTable metadata 
    and creates appropriate Zend_Validator

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

    Licensed under the Apache License, Version 2.0 (the "License"); you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.

    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.

==============================================================================*/

class Application_Validator_DbMeta
{
    public static function factory(array $metadata)
    {
        $validatorChain = new Zend_Validate();
        if (isset($metadata['DATA_TYPE']))
        $dataType = strtolower($metadata['DATA_TYPE']);
        switch ($dataType) {
            case 'varchar2':case 'varchar':
                $lengthValidator = createDefaultValidator($metadata);
                $validatorChain->addValidator($lengthValidator);
                break;
            case 'number':
                $validatorChain->addValidator(new Zend_Validate_Digits())
                ->addValidator($lengthValidator);
                break;
            default:
                $lengthValidator = createDefaultValidator($metadata);
                $validatorChain->addValidator($lengthValidator);
        }
        if (! $metadata['NULLABLE']) {
            $notEmptyValidator = new Zend_Validate_NotEmpty();
            $validatorChain->addValidator($notEmptyValidator);
        }

        return $validatorChain;
    }
    private static function createDefaultValidator($metadata)
    {
        $length = $metadata['LENGTH'];
        $lengthValidator = new Zend_Validate_StringLength(0, $length);
        return $lengthValidator;
    }

}