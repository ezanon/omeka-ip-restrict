<?php

class IpRestrict extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface{
    
    public $item_id;
    public $resource;
    public $active;
    public $ip_ranges;
    public $option;
    public $comments;
    
    public function _validate() {
        
    }
    
    public function getResourceId()
    {
        return 'IpRestricts';
    }
    
}
