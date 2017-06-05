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
        'admin_collections_form_tabs',
        'display_elements',
        'file_markup',
        'addInfoToTitle' => array('Display', 'Item', 'Dublin Core', 'Title')
    );
    
    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'ip_restrict_message' => '',
        'ip_ranges' => ''
    );
    
    protected $numFiles = 0;

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $db = $this->_db;
        $sql = "
             CREATE TABLE IF NOT EXISTS `omeka_ip_restricts` (
              `id` int(10) unsigned NOT NULL,
                `record_id` int(10) unsigned NOT NULL,
                `resource` char(1) COLLATE utf8_unicode_ci NOT NULL,
                `active` int(1) NOT NULL DEFAULT '0',
                `ip_ranges` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `option` int(1) NOT NULL,
                `comments` text COLLATE utf8_unicode_ci
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $db->query($sql);
        $sql = "ALTER TABLE `omeka_ip_restricts` ADD PRIMARY KEY (`id`)";
        $db->query($sql);
        $sql = "ALTER TABLE `omeka_ip_restricts` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT";
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
    public function hookUpgrade($args)
    {        
        if (version_compare($args['old_version'], '1.0.2', '<')){
            $db = $this->_db;
            $sql = "ALTER TABLE `omeka_ip_restricts` ADD PRIMARY KEY (`id`)";
            $db->query($sql);
            $sql = "ALTER TABLE `omeka_ip_restricts` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT";
            $db->query($sql);
        }
        // $this->_upgradeOptions();
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
        if (validateIPRanges($_POST['ip_ranges'])){
            set_option('ip_restrict_message', $_POST['restriction_text']);
            set_option('ip_ranges', $_POST['ip_ranges']);
        }
        else {
            set_option('ip_restrict_message', __('Invalid IP Ranges Sintax'));
            set_option('ip_ranges', '');
        }
    }
    
    /**
     * Insert IP Restrict tab in the Item Admin
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];
        $tabs['IP Restriction'] = $this->_getFormRecord($item);
        return $tabs;
    }
    
    /**
     * Insert IP Restrict tab in the Collection Admin
     */
    public function filterAdminCollectionsFormTabs($tabs, $collection)
    { 
        $tabs['IP Restriction'] = $this->_getFormRecord($collection);
        return $tabs;
    }
    
    /**
     * Return form admin of an item
     */ 
    private function _getFormRecord($record){
        
        if (get_option('ip_ranges') == '') return __('None IP Range configured. Go to plugins page to configure IP Restrict.');
        
        $iprestrict = $this->_db->getTable('IpRestrict')->getIpRestrictByRecord($record);
        if ($iprestrict){
            $active = ($iprestrict['active']==1) ? true : false;
            $ip_ranges = $iprestrict['ip_ranges'];
            $option = $iprestrict['option'];
            $comments = $iprestrict['comments'];
        }
        else{
            $active = false;
            $ip_ranges = false;
            $option = 1;
            $comments = '';
        }
        // checkbox to active filter 
        $html = '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[active]', __('Active filter to this register'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $html .=    get_view()->formCheckBox('iprestrict[active]', true, array('checked'=>$active));
        $html .= " </div></div>\n";
        
        // Choose IP ranges
        $actualRanges = explode(',',$ip_ranges);
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[ipv4Range]', __('IPv4 Ranges Granted'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $ranges = get_option('ip_ranges');
        $rangeAliases = getRangesAliases($ranges);
        foreach ($rangeAliases as $alias) {
            if (in_array($alias, $actualRanges))
                $html .= get_view()->formCheckBox("iprestrict[ip_range][$alias]", true, array('checked'=>TRUE)) . " $alias<br />";
            else
                $html .= get_view()->formCheckBox("iprestrict[ip_range][$alias]", true) . " $alias<br />";
        }
        $html .= " </div></div>\n";
                 
        // Options to the restriction
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('iprestrict[option]', __('Choose the restriction'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $options[1] = __('Show only thumbnails');
        $options[2] = __('Dont show any media');
        //$options[3] = __('Dont allow download of media');
        //$options[4] = __('Dont show intire item');
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
     * Save IP restriction to an record
     */
    public function hookAfterSaveRecord($args){
        if (!$args['post']) {
            return;
        }
        $record = $args['record']; 
        $post = $args['post'];
        
        // if dont have iprestrict tab
        if (!isset($post['iprestrict'])) {
            return;
        }
        
        // get iprestrict form values
        $iprestrictPost = $post['iprestrict'];
        
        // loop over all ipRestricts of an item
        if ((!empty($iprestrictPost)) && ($iprestrictPost['active']==1)){
            
            if ($record instanceof Item){
                $iprestrictPost['resource'] = 'i';
            } 
            elseif ($record instanceof Collection){
                $iprestrictPost['resource'] = 'c';
            }
            else return FALSE;
            // get ip ranges selected
            $rangesSelected = array();
            foreach ($iprestrictPost['ip_range'] as $alias => $value){
                if ($value == 1) $rangesSelected[] = $alias;
            }
            unset($iprestrictPost['ip_range']);
            $iprestrictPost['ip_ranges'] = implode(',', $rangesSelected);
            // if register exists, update
            if ($this->_db->getTable('IpRestrict')->hasIpRestrict($record)){
                $iprestrict = $this->_db->getTable('IpRestrict')->getIpRestrictByRecord($record);   
            }
            else {
                // new object
                $iprestrict = new IpRestrict;
                $iprestrict->record_id = $record->id;
            }
            $iprestrict->setPostData($iprestrictPost);
            $iprestrict->save();  
        }
        // Delete all registers if before exists and now not active
        elseif(($this->_db->getTable('IpRestrict')->hasIpRestrict($record)) && ($iprestrictPost['active']==0)){
            $iprestrict = $this->_db->getTable('IpRestrict')->getIpRestrictByRecord($record);
            $iprestrict->delete();         
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
    
    /**
     * Verify if there is restriction to show files
     * @param type $html
     * @param type $args
     * @return string
     */
    public function filterFileMarkup($html, $args){
        $file = $args['file'];  
        if ($file instanceof File){
            $this->numFiles++;
            try {
                $item = get_current_record('item');
                $shouldRestrictItem = TRUE;
                if (!$this->_db->getTable('IpRestrict')->hasIpRestrict($item)){
                    $shouldRestrictItem = FALSE;
                }
                else{
                    $iprestrictItem = $this->_db->getTable('IpRestrict')->getIpRestrictByRecord($item);               
                    $rangesAvailable = get_option('ip_ranges');
                    $rangesOfIPs = getRangesIPs($rangesAvailable);               
                    $rangesAliasOfItem = explode(',',$iprestrictItem->ip_ranges);
                    foreach ($rangesAliasOfItem as $alias){
                        foreach ($rangesOfIPs[$alias] as $iprange){
                            if (ipInsideRange($_SERVER['REMOTE_ADDR'], $iprange)){ // access granted
                                $shouldRestrictItem = FALSE;    
                            }
                        } 
                    } 
                    if (!$iprestrictItem['active']){ 
                        $shouldRestrictItem = FALSE;
                    }
                }
                // Verify restriction for collection of item
                $collection = get_collection_for_item();
                $shouldRestrictCollection = TRUE;
                if (!$this->_db->getTable('IpRestrict')->hasIpRestrict($collection)){
                    $shouldRestrictCollection = FALSE;
                }
                else {
                    $iprestrictCollection = $this->_db->getTable('IpRestrict')->getIpRestrictByPair('Collection',$collection->id);
                    $rangesAliasOfCollection = explode(',',$iprestrictCollection->ip_ranges);
                    $rangesAvailable = get_option('ip_ranges');
                    $rangesOfIPs = getRangesIPs($rangesAvailable);
                    foreach ($rangesAliasOfCollection as $alias){
                        foreach ($rangesOfIPs[$alias] as $iprange){
                            if (ipInsideRange($_SERVER['REMOTE_ADDR'], $iprange)){ // access granted
                                $shouldRestrictCollection = FALSE;    
                            }
                        } 
                    } 
                    if (!$iprestrictCollection['active']) {
                        $shouldRestrictCollection = FALSE;
                    }
                }
                
                $shouldRestrict = (($shouldRestrictItem || $shouldRestrictCollection) ? TRUE : FALSE );
                
                //Test
                /*echo ($shouldRestrictItem ? 'iTrue ' : 'iFalse ');
                echo ($shouldRestrictCollection ? 'cTrue ' : 'cFalse ');
                echo ($shouldRestrict ? 'sTrue ' : 'sFalse ');
                die();*/
                
                if ($shouldRestrict){
                    $option = ($shouldRestrictCollection ? $iprestrictCollection['option'] : $iprestrictItem['option']);
                    switch ($option) {
                        // show only thumbnails without link to original
                        case 1:
                            if ($file->hasThumbnail()){
                                $htmlWithoutLink = '<div class="item-file image-jpeg">' . file_image('thumbnail', array('class' => 'thumb'), $file) . '</div>';
                                return $htmlWithoutLink;
                            }
                            else return "<em>" . get_option('ip_restrict_message') . "</em><br>"; // access denied + information because dont have thumbnail
                            break;
                        // dont show any media files
                        case 2:
                            if ($this->numFiles > 1) 
                                return '';
                            else 
                                return "<em>" . get_option('ip_restrict_message') . "</em><br>"; // access denied + information
                            break;
                        default:
                            break;
                    }
                }
                else {
                   return $html;
                }
            }
            catch (Omeka_View_Exception $ove){}    
        }
        return $html;
    }
    
}
