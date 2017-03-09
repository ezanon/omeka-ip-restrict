<?php

class FormAdmin extends Omeka_Form_Admin {
    
    public function _getForm($record){
        $this->addElement('checkbox','active',
                array('label' => __('Ativar filtro'),
                    'value' => 0));
        $this->addElement('text','IPv4',
                array('label' => __('Ativar filtro'),
                    'value' => '143.107.76.0/255.255.254.0'));
        $this->addElement('checkbox','dont_show_anything',
                array('label' => __('Ativar filtro'),
                    'value' => 0));
        $this->addElement('checkbox','dont_show_media',
                array('label' => __('Ativar filtro'),
                    'value' => 1));
        $this->addElement('checkbox','dont_allow_download', 
                array('label' => __('Ativar filtro'),
                    'value' => 0));
        $this->addElement('checkbox','only_thumbnail', 
                array('label' => __('Ativar filtro'),
                    'value' => 0));
        
        if($record && $record->exists()) {
            $formOptions['record'] = $record;
        }
        
    }
    
}
