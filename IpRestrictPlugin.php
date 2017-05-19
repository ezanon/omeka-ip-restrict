<?php

require_once dirname(__FILE__) . '/views/helpers/IPRestrictFunctions.php';

class IpRestrictPlugin extends Omeka_Plugin_AbstractPlugin {
    
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'install', 
        'uninstall', 
        'upgrade', 
        'initialize',
        'config_form', 
        'config',
        'after_save_record'
        );
    
    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_items_form_tabs',
        'display_elements',
        'file_markup',
        'addInfoToTitle' => array('Display', 'Item', 'Dublin Core', 'Title')
    );
    
    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'ip_restrict_message' => ''
    );
    
    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
       $db = $this->_db;
       $sql = "
            CREATE TABLE IF NOT EXISTS `omeka_ip_restricts` (
             `id` int(10) unsigned NOT NULL,
               `item_id` int(10) unsigned NOT NULL,
               `resource` char(1) COLLATE utf8_unicode_ci NOT NULL,
               `active` int(1) NOT NULL DEFAULT '0',
               `firstIPv4` char(15) COLLATE utf8_unicode_ci NOT NULL,
               `lastIPv4` char(15) COLLATE utf8_unicode_ci NOT NULL,
               `option` int(1) NOT NULL,
               `comments` text COLLATE utf8_unicode_ci
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
       ";
       $db->query($sql);
       $this->_installOptions();
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {        
        // Drop the table.
        $db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `$db->IpRestrict`";
        $db->query($sql);
        $this->_uninstallOptions();
    }
    
    /**
     * Upgrade the plugin.
     */
    public function hookUpgrade()
    {        
        // 
        $this->_upgradeOptions();
    }
    
    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
        get_view()->addHelperPath(dirname(__FILE__) . '/views/helpers', 'IpRestrict_View_Helper_');
    }
    
    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }
    
    /**
     * Set the options from the config form input.
     */
    public function hookConfig()
    {
        set_option('ip_restrict_message', $_POST['restriction_text']);
    }
    
    /**
     * Insert IP Restrict tab in the Admin
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];
        $tabs['IP Restriction'] = $this->_getFormItem($item);
        return $tabs;
    }
    
    /**
     * Return form admin of an item
     */ 
    private function _getFormItem($item){
        
        $iprestrict = $this->_db->getTable('IpRestrict')->getIpRestrictByItem($item,TRUE);;
        if ($iprestrict){
            $active = ($iprestrict['active']==1) ? true : false;
            $firstIPv4 = $iprestrict['firstIPv4'];
            $lastIPv4 = $iprestrict['lastIPv4'];
            $option = $iprestrict['option'];
            $comments = $iprestrict['comments'];
        }
        else{
            $active = false;
            $firstIPv4 = __('First IP');
            $lastIPv4 = __('Last IP');
            $option = 1;
            $comments = '';
        }
        // checkbox to active filter 
        $html = '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[active]', __('Active filter to this item'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $html .=    get_view()->formCheckBox('iprestrict[active]', true, array('checked'=>$active));;
        $html .= " </div></div>\n";
        // IP range that can access the item
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[firstIPv4]', __('IPv4 Range'));
        $html .= ' </div>';
        $html .= ' <div class="inputs two columns omega">';
        $html .=    get_view()->formText('iprestrict[firstIPv4]', $firstIPv4);
        $html .= ' </div>';
        $html .= ' <div class="inputs one columns omega">';
        $html .=    __('to');
        $html .= ' </div>';
        $html .= ' <div class="inputs two columns omega">';
        $html .=    get_view()->formText('iprestrict[lastIPv4]', $lastIPv4);
        $html .= " </div></div>\n";
        // Options to the restriction
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[option]', __('Choose the restriction'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $options[1] = __('Show only thumbnails');
        $options[2] = __('Dont show any media');
        $options[3] = __('Dont allow download of media');
        $options[4] = __('Dont show intire item');
        $html .=    get_view()->formSelect('iprestrict[option]', $option, null,$options);
        $html .= " </div></div>\n";
        // some comments 
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[comments]', __('Comments'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $html .=    get_view()->formTextarea('iprestrict[comments]', $comments);
        $html .= " </div></div>\n";
        // div header
        $html = '<div id="IpRestrictConfigForm">' . $html . "</div>\n";
        return $html;
    }
    
    /**
     * Save IP restriction to an item
     */
    public function hookAfterSaveRecord($args){
        if (!$args['post']) {
            return;
        }
        $item = $args['record']; 
        $post = $args['post'];
        
        // if dont have iprestrict tab
        if (!isset($post['iprestrict'])) {
            return;
        }
        
        // get iprestrict form values
        $iprestrictPost = $post['iprestrict'];
        
        // find the data for the item
        $iprestrictIds = $this->_db->getTable('IpRestrict')->getIpRestrictIdsByItem($item);
        
        // loop over all ipRestricts of an item
        if ((!empty($iprestrictPost)) && ($iprestrictPost['active']==1)){
            $iprestrictPost['resource'] = 'i';
            // if register exists, update
            if (!empty($iprestrictIds)){
                foreach ($iprestrictIds as $id){
                    $iprestrict = $this->_db->getTable('IpRestrict')->getIpRestrictByIdIP($item,$id);
                    break; // delete this line when capable to register more than one IP range
                }   
            }
            else {
                // new object
                $iprestrict = new IpRestrict;
                $iprestrict->item_id = $item->id;
            }
            $iprestrict->setPostData($iprestrictPost);
            $iprestrict->save();  
        }
        // Delete all registers if before exists and now not active
        elseif((!empty($iprestrictIds)) && ($iprestrictPost['active']==0)){
            foreach ($iprestrictIds as $id){
                $iprestrict = $this->_db->getTable('IpRestrict')->getIpRestrictByIdIP($item,$id);
                $iprestrict->delete();
            }             
        }
        return;
    }
    
    public function filterDisplayElements($elementSets){
        return $elementSets;
    }
    
    public function addInfoToTitle($text,$args){
        return $text;
        //* @todo : verify IP to add info to title
        return $text . "<br><em>" . __('(Restrict)') . "</em>";
    }
    
    public function filterFileMarkup($html, $args){
        $file = $args['file'];
        
        if ($file instanceof File) {
            $item = get_record_by_id('item', $file->item_id);
            $iprestrictIds = $this->_db->getTable('IpRestrict')->getIpRestrictIdsByItem($item);
            if ($iprestrictIds) return "<em>" . __('(Restrict access to file)') . "</em><br>";
        }
        return $html;
    }
    
}

