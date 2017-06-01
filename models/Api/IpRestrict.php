<?php

class Api_IpRestrict extends Omeka_Record_Api_AbstractRecordAdapter {
    
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        $representation = array(
            'id' => $record->id, 
            'record_id' => $record->record_id,
            'resource' => $record->resource,
            'active' => $record->active,
            'ip_ranges' => $record->ip_ranges,
            'option' => $record->option,
            'comments' => $record->comments
        );
        return $representation;
    }
    
    public function setPostData(Omeka_Record_AbstractRecord $record, $data) {
        if (isset($data->item->id)) {
            $record->record_id = $data->record_id;
        }
        if (isset($data->item->resource)) {
            $record->resource = $data->resource;
        }
        if (isset($data->item->active)) {
            $record->active = $data->active;
        }
        if (isset($data->item->ip_range)) {
            $record->iprange = $data->iprange;
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
