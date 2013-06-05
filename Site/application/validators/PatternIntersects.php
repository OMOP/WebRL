<?php

class Application_Validator_PatternIntersects extends Zend_Validate_Db_NoRecordExists {
   
    protected $_messageTemplates = array(self::ERROR_RECORD_FOUND => 'Record which intersects with pattern %value% was found');
    
    protected function _query($value) 
    { 
        /**
         * Check for an adapter being defined. if not, fetch the default adapter.
         */ 
        if ($this->_adapter === null) {
            $this->_adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
            if (null === $this->_adapter) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('No database adapter present');
            }
        }

        /**
         * Build select object
         */ 
        $select = new Zend_Db_Select($this->_adapter);
        $value_like = str_replace(array('_', '*'), array("|_", '%'), $value);
        $select->from($this->_table, array($this->_field), $this->_schema)
               ->where('('. $this->_adapter->quoteIdentifier($this->_field)." LIKE ? ESCAPE '|'". " OR ".
                $this->_adapter->quote($value)." LIKE REPLACE(REPLACE(".$this->_adapter->quoteIdentifier($this->_field).", '_', '|_'), '*', '%') ESCAPE '|')", $value_like);
        if ($this->_exclude !== null) { 
            if (is_array($this->_exclude)) { 
                $select->where($this->_adapter->quoteIdentifier($this->_exclude['field']).' != ?', $this->_exclude['value']); 
            } else { 
                $select->where($this->_exclude); 
            } 
        } 
        $select->limit(1); 
        /**
         * Run query
         */ 
        $result = $this->_adapter->fetchRow($select, array(), Zend_Db::FETCH_ASSOC); 
         
        return $result; 
    }         
    
}

?>
