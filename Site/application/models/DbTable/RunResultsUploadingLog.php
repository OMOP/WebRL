<?php
/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Zend_Db_Table class for result_upload_log_tbl (MySQL) table.

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
class Application_Model_DbTable_RunResultsUploadingLog extends Zend_Db_Table_Abstract
{

    protected $_name = 'result_upload_log_tbl';
    protected $_primary = 'result_upload_log_id';
    protected $_sequence = true;
    protected $_dependentTables = 'result_upload_log_runs_tbl';

    /*public function init()
    {
        //$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
    }*/

    public function fetchSearchResults($sort_order, $sort_direction, $runs_separator)
    {
        $query = '
SELECT CONCAT_WS(\' \', `u`.`first_name`, `u`.`last_name`) as `user_name`, `u`.`user_id`,
    `l`.`result_upload_log_id`, `l`.`dataset`, `l`.`added`, `l`.`is_loaded_s3`, `l`.`is_loaded_oracle`,
	`lr`.`method`, GROUP_CONCAT(`lr`.`run` ORDER BY `lr`.`run` SEPARATOR \''.$runs_separator.'\') as `runs`, `l`.`error`
FROM `result_upload_log_tbl` as `l`
  left join `result_upload_log_runs_tbl` as `lr` ON `l`.`result_upload_log_id` = `lr`.`result_upload_log_id`
  join `user_tbl` as `u` ON `l`.`user_id` = `u`.`user_id`
GROUP BY `lr`.`result_upload_log_id`, `l`.`dataset`, `lr`.`method` 
ORDER BY ' . $sort_order . ' ' . $sort_direction;
        return $this->_db->fetchAll($query);
    }

    /**
     * Execute sql query
     * @param string $sql
     */
    public function query($sql)
    {
        $this->_db->query($sql);
    }

    /**
     * Add quotes and escape special symbols in a given parameter
     * @param mixed $data
     * @return string quoted parameter
     */
    public function quote($data)
    {
        return $this->_db->quote($data);
    }
}

