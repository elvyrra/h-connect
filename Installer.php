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
        App::db()->query(
            "CREATE TABLE IF NOT EXISTS `" . HContact::getTable() . "` (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `userId` INT(11) NOT NULL,
                `firstName` VARCHAR(64) NOT NULL,
                `lastName` VARCHAR(64) NOT NULL,
                `job` VARCHAR(64) NOT NULL,
                `company` VARCHAR(64) NOT NULL,
                `phoneNumber` VARCHAR(16) NOT NULL,
                `cellNumber` VARCHAR(16) NOT NULL,
                `personalNumber` VARCHAR(16) NOT NULL,
                `email` VARCHAR(64) NOT NULL,
                `address` text NOT NULL,
                `city` VARCHAR(128) NOT NULL,
                `postcode` VARCHAR(10) NOT NULL,
                `country` varchar(32) NOT NULL,
                `ctime` INT(11) NOT NULL,
                `mtime` INT(11) NOT NULL,
                INDEX (`userId`),
                CONSTRAINT `UserId_ibfk` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        App::db()->query(
            "CREATE TABLE IF NOT EXISTS `" . HContactQuestion::getTable() . "` (
                `name` varchar(32) NOT NULL,
                `userId` INT(11) NOT NULL,
                `type` varchar(16) NOT NULL,
                `parameters` text NOT NULL,
                `displayInList` TINYINT(1) NOT NULL,
                `order` int(11) NOT NULL,
                PRIMARY KEY (`name`),
                INDEX (`userId`),
                UNIQUE KEY `question_c` (`name`,`userId`)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        App::db()->query(
            "CREATE TABLE IF NOT EXISTS `" . HContactValue::getTable() . "` (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `userId` INT(11) NOT NULL,
                `contactId` INT(11) NOT NULL,
                `question` VARCHAR(32) NOT NULL,
                `value` text NOT NULL,
                UNIQUE KEY `question_2` (`question`,`contactId`),
                KEY `question` (`question`),
                CONSTRAINT `ContactQuestionValue_ibfk_1` FOREIGN KEY (`question`) REFERENCES `" . HContactQuestion::getTable() . "` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        App::db()->query(
            "CREATE TABLE IF NOT EXISTS `" . HDirectoryGroup::getTable() . "` (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `name` VARCHAR(128) NOT NULL,
                `description` TEXT,
                `userId` INT(11) NOT NULL,
                INDEX (`name`),
                INDEX (`userId`),
                `ctime` INT(11) NOT NULL,
                `mtime` INT(11) NOT NULL
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        App::db()->query(
            "CREATE TABLE IF NOT EXISTS `" . HGroupContact::getTable() . "` (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `groupId` INT(11) NOT NULL,
                `contactId` INT(11) NOT NULL,
                INDEX (`groupId`),
                INDEX (`contactId`),
                CONSTRAINT `HGroupId_ibfk` FOREIGN KEY (`groupId`) REFERENCES `HDirectoryGroup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `HContactId_ibfk` FOREIGN KEY (`contactId`) REFERENCES `HContact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `groupContact` UNIQUE (`groupId`, `contactId`)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ); 

        Permission::add($this->_plugin . '.admin-contact', 1, 0);   // Set up
        Permission::add($this->_plugin . '.view-contact', 1, 0);    // Only show list contact


        if(Plugin::existAndIsActive('h-agenda')){
            App::db()->query(
                "CREATE TABLE IF NOT EXISTS `" . \Hawk\Plugins\HAgenda\HAgendaContact::getTable() . "` (
                    `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `eventId` INT(11) NOT NULL,
                    `contactId` INT(11) NOT NULL,
                    INDEX (`eventId`),
                    INDEX (`contactId`),
                    CONSTRAINT `HAgendaEventId_ibfk` FOREIGN KEY (`eventId`) REFERENCES `HAgendaEvent` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `HAgendaContactId_ibfk` FOREIGN KEY (`contactId`) REFERENCES `HContact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `agendaEventContact` UNIQUE (`eventId`, `contactId`)
                )ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );
        }
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . HGroupContact::getTable());

        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . HDirectoryGroup::getTable());

        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . HContactValue::getTable());

        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . HContactQuestion::getTable());

        if(Plugin::existAndIsActive('h-agenda'))
            DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . \Hawk\Plugins\HAgenda\HAgendaContact::getTable());

        DB::get(MAINDB)->query('DROP TABLE IF EXISTS ' . HContact::getTable());

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
            )); 

        MenuItem::add(array(
            'plugin' => $this->_plugin,
            'name' => 'contact',
            'labelKey' => $this->_plugin . '.contact-menu-title',
            'action' => 'h-connect-contact-index',
            'parentId' => $menu->id,
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