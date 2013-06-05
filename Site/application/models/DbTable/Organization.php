<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for organization_tbl table.

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
class Application_Model_DbTable_Organization extends Zend_Db_Table_Abstract
{

    protected $_name = 'organization_tbl';
    protected $_dependentTables = 'OrganizationMethod';

    /*
     * Gets pairs [$id] => [$name]. Should be moved to Mapper.
     */
    public function getList($showAll = false) {
        $db = $this->getAdapter();
        $select = $this->select();
        $select->from($this, array('organization_id', 'organization_name'));
        if (! $showAll)
            $select->where('active_flag = "Y"');
        $select->where("organization_deleted_date is null");
        $select->order('organization_name');
        $orgs = $db->fetchPairs($select);
        return $orgs;
    }
}

