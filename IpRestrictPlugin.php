<?php

require_once dirname(__FILE__) . '/views/helpers/IPRestrictFunctions.php';

class IpRestrictPlugin extends Omeka_Plugin_AbstractPlugin {
    
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 'uninstall', 'upgrade', 'initialize',
        'define_acl', 'define_routes', 'config_form', 'config',
        'html_purifier_form_submission');
    
    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array();
    
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
    
}

