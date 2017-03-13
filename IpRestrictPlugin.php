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
        'config'
        );
    
    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_items_form_tabs'
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
           CREATE TABLE IF NOT EXISTS `$db->IpRestrict` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `record_id` int(10) unsigned NOT NULL,
            `active` int(1) NOT NULL DEFAULT '0',
            `IPv4` varchar(31) COLLATE utf8_unicode_ci NOT NULL,
            `dont_show_anything` int(1) NOT NULL DEFAULT '0',
            `dont_show_media` int(1) NOT NULL,
            `dont_allow_download` int(1) NOT NULL,            
            `only_thumbnail` int(1) NOT NULL,
            PRIMARY KEY (`id`)
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
        //$tabs['IP Restriction'] = require dirname(__FILE__) . '/config_form.php';
        //$tabs['IP Restrict'] = 'TESTE';
        return $tabs;
    }
    
    /**
     * Return form admin of an item
     */ 
    private function _getFormItem($item){
        // checkbox to active filter 
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('active', __('Active filter to this item'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $html .=    get_view()->formCheckBox('active', true, array('checked'=>false));;
        $html .= ' </div></div>';
        // IP range that can access the item
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('allowedIP', __('IP/Mask allowed'));
        $html .= ' </div>';
        $html .= ' <div class="inputs two columns omega">';
        $html .=    get_view()->formText('allowedIP', 'IP');
        $html .= ' </div>';
        $html .= ' <div class="inputs two columns omega">';
        $html .=    get_view()->formText('mask', 'Mask');
        $html .= ' </div></div>';
        // Options to the restriction
        $html .= '<div class="field">';
        $html .= ' <div class="two columns alpha">';
        $html .=    get_view()->formLabel('option', __('Choose the restriction'));
        $html .= ' </div>';
        $html .= ' <div class="inputs five columns omega">';
        $options[1] = __('Dont show media');
        $options[2] = __('Dont allow download of media');
        $options[3] = __('Dont show intire item');
        $html .=    get_view()->formSelect('option', $option, null,$options);
        $html .= ' </div></div>';
        return $html;
    }
    
}

