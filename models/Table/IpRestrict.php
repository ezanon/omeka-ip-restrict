<?php

class Table_IpRestrict extends Omeka_Db_Table {
    
   /**
     * Get IPRestrict information of a record
     * @return boolean
     */
    public function getIpRestrictByRecord($record){
        
        try {
            $record = get_current_record('item');
        }
        catch (Exception $ex) {}
        try {
            $record = get_current_record('collection');
        } catch (Exception $ex) {}
        
        $db = get_db();
        if (($record instanceof Item) && !$record->exists()) {
            return FALSE;
        } 
        if (($record instanceof Collection) && !$record->exists()) {
            return FALSE;
        }
        elseif (!($record instanceof Item) && !($record instanceof Collection)) {
            return FALSE;
        }
               
        $alias = $this->getTableAlias();
        // Create a SELECT statement for the IpRestrict table
        $select = $db->select()->from(array($alias => $db->IpRestrict), "$alias.*");
        
        // Create a WHERE condition that will pull down all the IpRestrict info
        if ($record instanceof Item){
            $select->where("$alias.record_id = ? and $alias.resource='i'", $record->id);
        } 
        elseif ($record instanceof Collection){
            $select->where("$alias.record_id = ? and $alias.resource='c'", $record->id);
        }
        else {
            return FALSE;
        }
        $iprestrict = $this->fetchObject($select);
        return $iprestrict;
    }
    
    /**
     * Get IPRestrict for an id + model pair
     * @param type $model Item or Collection
     * @param type $id
     * @return boolean
     */
    public function getIpRestrictByPair($model,$id){
        
        $record = get_record_by_id($model, $id);
        
        $db = get_db();
        if (($record instanceof Item) && !$record->exists()) {
            return FALSE;
        } 
        if (($record instanceof Collection) && !$record->exists()) {
            return FALSE;
        }
        elseif (!($record instanceof Item) && !($record instanceof Collection)) {
            return FALSE;
        }
               
        $alias = $this->getTableAlias();
        // Create a SELECT statement for the IpRestrict table
        $select = $db->select()->from(array($alias => $db->IpRestrict), "$alias.*");
        
        // Create a WHERE condition that will pull down all the IpRestrict info
        if ($record instanceof Item){
            $select->where("$alias.record_id = ? and $alias.resource='i'", $record->id);
        } 
        elseif ($record instanceof Collection){
            $select->where("$alias.record_id = ? and $alias.resource='c'", $record->id);
        }
        else {
            return FALSE;
        }
        $iprestrict = $this->fetchObject($select);
        return $iprestrict;
    }
    
    /**
     * Verify if there is a IP Restrict entry for the record instance
     * @param type $record
     * @return boolean
     */
    public function hasIpRestrict($record){
        $db = get_db();
        
        if (($record instanceof Item) && !$record->exists()) {
            return FALSE;
        } 
        if (($record instanceof Collection) && !$record->exists()) {
            return FALSE;
        }
        elseif (!($record instanceof Item) && !($record instanceof Collection)) {
            return FALSE;
        }
        $alias = $this->getTableAlias();
        $select = $db->select()->from(array($alias => $db->IpRestrict), "$alias.*");
        if ($record instanceof Item){
            $select->where("$alias.record_id = ? and $alias.resource='i'", $record->id);
        } 
        elseif ($record instanceof Collection){
            $select->where("$alias.record_id = ? and $alias.resource='c'", $record->id);
        }
        if ($this->fetchObject($select)) 
            return TRUE;
        else 
            return FALSE;
    }
    
}
