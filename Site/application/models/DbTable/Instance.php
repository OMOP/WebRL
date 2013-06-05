<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for instance_tbl table.

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

================================================================================*/
class Application_Model_DbTable_Instance extends Zend_Db_Table_Abstract
{
    protected $_name = 'instance_tbl';
    protected $_primary = 'instance_id';
    
    public function updateName($instanceId,$newName){
        $data = array('assigned_name' => $newName);
        $where = 'instance_id = '.$instanceId;
        $this->update($data, $where);
    }
    
    public function selectInstanceOwner ($instanceId) {
        $select = $this->getAdapter()->select()
                                     ->from(array('i' => 'instance_tbl'),array('ownerId' => 'u.user_id'))
                                     ->join(array('ir' => 'instance_request_tbl'), 
                                                    'i.instance_request_id = ir.instance_request_id',
                                            array()
                                            )
                                     ->join(array('u' => 'user_tbl'), 
                                            'u.user_id = ir.user_id',
                                             array()
                                        )
                                      ->where("i.instance_id = ?",$instanceId);
        $stmt = $select->query();
        return $stmt->fetchAll();
    }
    
    public function selectDupName ($instanceId,$ownerId,$newName) {
        $select = $this->getAdapter()->select()
                                     ->from(array('i' => 'instance_tbl'),array('ownerId' => 'u.user_id'))
                                     ->join(array('ir' => 'instance_request_tbl'), 
                                                    'i.instance_request_id = ir.instance_request_id',
                                            array()
                                            )
                                     ->join(array('u' => 'user_tbl'), 
                                            'u.user_id = ir.user_id',
                                             array()
                                        )
                                      ->where("i.instance_id != {$instanceId} AND i.assigned_name = '{$newName}' AND u.user_id = {$ownerId}");
        $stmt = $select->query();
        return $stmt->fetchAll();
    }
    
}