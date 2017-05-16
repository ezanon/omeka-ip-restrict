<?php

class Api_IpRestrict extends Omeka_Record_Api_AbstractRecordAdapter {
    
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id' => $record->id, 
            'item_id' => $record->item_id,
            'resource' => $record->resource,
            'active' => $record->active,
            'firstIPv4' => $record->allowedIPv4,
            'lastIPv4' => $record->allowedIPv4Mask,
            'option' => $record->option,
            'comments' => $record->comments
        );
        return $representation;
    }
    
    public function setPostData(Omeka_Record_AbstractRecord $record, $data) {
        if (isset($data->item->id)) {
            $record->item_id = $data->item_id;
        }
        if (isset($data->item->resource)) {
            $record->resource = $data->resource;
        }
        if (isset($data->item->active)) {
            $record->active = $data->active;
        }
        if (isset($data->item->firstIPv4)) {
            $record->firstIPv4 = $data->firstIPv4;
        }
        if (isset($data->item->lastIPv4)) {
            $record->lastIPv4 = $data->lastIPv4;
        }
        if (isset($data->item->option)) {
            $record->option = $data->option;
        }
        if (isset($data->item->comments)) {
            $record->comments = $data->comments;
        }  
        else {
            $record->comments = '';
        }
        return;
    }
    
}
