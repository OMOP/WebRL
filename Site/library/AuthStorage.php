<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Storage class for Zend_Auth

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

================================================================================*/class AuthStorage implements Zend_Auth_Storage_Interface {


    /*
     * Default name of the variable in the session
     */
    const SESSION_DEFAULT = 'current_user';

    /*
     * Name of the variable in the session
     */
    private $_sessionVariable;

    /*
     * Sets session storage options
     * @param string $session_variable
     * return void
     */
    public function __construct($session_variable = self::SESSION_DEFAULT) {

        $this->_sessionVariable = $session_variable;

    }


    /**
     * Returns true if and only if storage is empty
     *
     * @throws Zend_Auth_Storage_Exception If it is impossible to
     *                                     determine whether storage
     *                                     is empty
     * @return boolean
     */
    public function isEmpty()
    {
        if (! isset($_SESSION[$this->_sessionVariable]) || !$_SESSION[$this->_sessionVariable])
            return true;
        return false;
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws Zend_Auth_Storage_Exception If reading contents from
     *                                     storage is impossible
     * @return mixed
     */
    public function read()
    {
        return $_SESSION[$this->_sessionVariable];
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws Zend_Auth_Storage_Exception If writing $contents to
     *                                     storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $_SESSION[$this->_sessionVariable] = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @throws Zend_Auth_Storage_Exception If clearing contents from
     *                                     storage is impossible
     * @return void
     */
    public function clear()
    {
        unset($_SESSION[$this->_sessionVariable]);
    }
}

?>
