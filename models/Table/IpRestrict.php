<?php

class Table_IpRestrict extends Omeka_Db_Table {
    
    /**
     * Get ids of IpRestrict table for an item
     * 
     * @param type $item
     * @return type array() with the IDs of IpRestrict of the item
     */
    public function getIpRestrictIdsByItem($item){
        
        $db = get_db();
        
        if (($item instanceof Item) && !$item->exists()) {
            return array();
        } else if (is_array($item) && !count($item)) {
            return array();
        }
        
        $alias = $this->getTableAlias();
        // Create a SELECT statement for the IpRestrict table
        $select = $db->select()->from(array($alias => $db->IpRestrict), "$alias.*");
        
        // Create a WHERE condition that will pull down all the IpRestrict info
        if (is_array($item)) {
            $itemIds = array();
            foreach ($item as $it) {
                $itemIds[] = (int)(($it instanceof Item) ? $it->id : $it);
            }
            $select->where("$alias.item_id IN (?)", $itemIds);
        } else {
            $itemId = (int)(($item instanceof Item) ? $item->id : $item);
            $select->where("$alias.item_id = ?", $itemId);
        }
         
        // Get the iprestrictions of the item.
        $iprestricts = $this->fetchObjects($select);
       
        $IprestrictIds = array();
        foreach ($iprestricts as $k => $ipr) {
            $IprestrictIds[] = $ipr['id'];
        }
        return $IprestrictIds;   
    }
    
    /**
     * Get one IpRestrict Information 
     * Return first founded or the required by the id
     * 
     * @param type $item
     * @param type $id_iprestrict - id of ip_restrict table refers to the item
     * @return type 
     */
    public function getIpRestrictByIdIP($item,$id_iprestrict=FALSE){
        
        $db = get_db();
        
        if (($item instanceof Item) && !$item->exists()) {
            return array();
        } else if (is_array($item) && !count($item)) {
            return array();
        }
        
        $alias = $this->getTableAlias();
        // Create a SELECT statement for the IpRestrict table
        $select = $db->select()->from(array($alias => $db->IpRestrict), "$alias.*");
        
        // erickson
        if ($id_iprestrict){
            $select->where("$alias.id = ?", $id_iprestrict);
        }
        else {
            if (is_array($item)) {
                $itemIds = array();
                foreach ($item as $it) {
                    $itemIds[] = (int)(($it instanceof Item) ? $it->id : $it);
                }
                $select->where("$alias.item_id IN (?)", $itemIds);
            } else {
                $itemId = (int)(($item instanceof Item) ? $item->id : $item);
                $select->where("$alias.item_id = ?", $itemId);
            }
        }

        $iprestrict = $this->fetchObject($select);
        return $iprestrict; 
    }
    
    public function getIpRestrictByItem($item, $findOnlyOne = false){
        
        $db = get_db();
        
        if (($item instanceof Item) && !$item->exists()) {
            return array();
        } else if (is_array($item) && !count($item)) {
            return array();
        }
        
        $alias = $this->getTableAlias();
        // Create a SELECT statement for the IpRestrict table
        $select = $db->select()->from(array($alias => $db->IpRestrict), "$alias.*");
        
        // Create a WHERE condition that will pull down all the IpRestrict info
        if (is_array($item)) {
            $itemIds = array();
            foreach ($item as $it) {
                $itemIds[] = (int)(($it instanceof Item) ? $it->id : $it);
            }
            $select->where("$alias.item_id IN (?)", $itemIds);
        } else {
            $itemId = (int)(($item instanceof Item) ? $item->id : $item);
            $select->where("$alias.item_id = ?", $itemId);
        }
        
        if ($findOnlyOne) {
            $iprestrict = $this->fetchObject($select);
            return $iprestrict;
        }
        else {return;}
         
       
    }
    
}
