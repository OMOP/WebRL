<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    16-Feb-2011

    Zend_Db_Table class for website_event_log

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
class Application_Model_DbTable_WebsiteEvent extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'website_event_log';
    protected $_primary = 'website_log_id';
    
    
    public function find($id)
    {
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(array('wel' => 'website_event_log'))
            ->joinLeft(
                array('u' => 'user_tbl'),
                'u.user_id = wel.user_id',
                array('login_id')
            )
            ->where('website_log_id = ?', $id);
        
        return $this->fetchAll($select);

    }
    
    
}