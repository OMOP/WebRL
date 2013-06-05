<?php

/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    11-Feb-2011

    Db Table for security_log table

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
class Application_Model_DbTable_SecurityLog extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'security_log';
    protected $_primary = 'security_log_id';
    
    public function find($id)
    {
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(array('sl' => 'security_log'))
            ->joinLeft(
                array('it' => 'security_issue_type_tbl'), 
                'sl.security_issue_type_id = it.security_issue_type_id',
                array('security_issue_type_description')
            )
            ->joinLeft(
                array('u' => 'user_tbl'),
                'u.user_id = sl.user_id',
                array('login_id')
            )
            ->joinLeft(
                array('i' => 'instance_tbl'),
                'i.instance_id = sl.instance_id',
                array('assigned_name')
            )
            ->where('security_log_id = ?', $id);
        
        return $this->fetchAll($select);

    }
    
}