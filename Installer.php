<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\HConnect;

/**
 * This class describes the behavio of the installer for the plugin HConnect
 */
class Installer extends PluginInstaller{
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install(){

        HContact::createTable();

        HContactQuestion::createTable();

        HContactValue::createTable();

        HDirectoryGroup::createTable();

        HGroupContact::createTable();

        Permission::add($this->_plugin . '.admin-contact', 1, 0);   // Set up
        Permission::add($this->_plugin . '.view-contact', 1, 0);    // Only show list contact

        if(Plugin::existAndIsActive('h-agenda')){
            \Hawk\Plugins\HAgenda\HAgendaContact::createTable();
        }
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        HGroupContact::dropTable();

        HDirectoryGroup::dropTable();

        HContactValue::dropTable();

        HContactQuestion::dropTable();

        if(Plugin::existAndIsActive('h-agenda')){
            \Hawk\Plugins\HAgenda\HAgendaContact::dropTable();
        }

        HContact::dropTable();

        $permissions = Permission::getPluginPermissions($this->_plugin);
        foreach($permissions as $permission){
            $permission->delete();
        }
    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){
        $menu = MenuItem::getByName('utility.main');

        if(!$menu)
            $menu = MenuItem::add(array(
                'plugin' => 'utility',
                'name' => 'main',
                'labelKey' => $this->_plugin . '.main-menu-title',
                'icon' => 'legal',
                'active' => 1
            )); 

        MenuItem::add(array(
            'plugin' => $this->_plugin,
            'name' => 'contact',
            'labelKey' => $this->_plugin . '.contact-menu-title',
            'action' => 'h-connect-contact-index',
            'parentId' => $menu->id,
            'icon' => 'users',
            'active' => 1
        ));
    }

    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        $menus = MenuItem::getPluginMenuItems($this->_plugin);
        foreach($menus as $menu){
            $menu->delete();
        }
    }

    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if
     * configurable.
     */
    /*
    public function settings(){
    }
    */
}