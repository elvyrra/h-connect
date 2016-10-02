<?php

namespace Hawk\Plugins\HConnect;

App::router()->prefix('/h-connect', function(){

    App::router()->auth(App::session()->isAllowed('h-connect.view-contact'), function(){
        App::router()->get('h-connect-contact-index', '', array('action' => 'ConnectController.index'));

        App::router()->get('h-connect-contact-list', '/list-contact', array('action' => 'ConnectController.getListContact'));

        App::router()->get('h-connect-group-list', '/list-group', array('action' => 'ConnectController.getListGroup'));

        App::router()->any('h-connect-contact-edit', '/{contactId}/edit', array(
            'where' => array(
                'contactId' => '\d+',
            ),
            'action' => 'ConnectController.editContact'
        ));

        App::router()->any('h-connect-contact-import', '/import', array('action' => 'ConnectController.importForm'));

        App::router()->get('h-connect-contact-delete', '/{contactId}/remove', array(
            'where' => array(
                'contactId' => '\d+'
            ),
            'action' => 'ConnectController.deleteContact'
        ));

        App::router()->any('h-connect-group-edit', '/group/{groupId}/edit', array(
            'where' => array(
                'groupId' => '\d+',
            ),
            'action' => 'ConnectController.editGroup'
        ));

        App::router()->get('h-connect-group-delete', '/group/{groupId}/remove', array(
            'where' => array(
                'groupId' => '\d+'
            ), 
            'action' => 'ConnectController.deleteGroup'
        ));
    });

    // Admin 
    App::router()->auth(App::session()->isAllowed('h-connect.admin-contact'), function(){
	   	/**
         * Manage contacts questions
         */
        App::router()->any('h-connect-contact-questions', '/contact-questions/', array(
            'action' => 'SettingsController.listQuestions'
        ));

        App::router()->any('h-connect-edit-contact-question', '/contact-questions/{name}', array(
            'where' => array(
                'name' => '[^\/]+'
            ),
            'action' => 'SettingsController.edit'
        )); 
    });

    App::router()->get('h-connect-remove-contact-question', '/contact-questions/{name}/remove', array(
        'where' => array(
            'name' => '[^\/]+'
        ),
        'action' => 'SettingsController.deleteQuestion'
    ));

    App::router()->any('h-connect-contact-export', '/export-contacts', array(
        'action' => 'ConnectController.export'
    ));


    // Autocomplete contact
    App::router()->get('h-connect-contact-autocomplete', '/autocomplete-contact', array(
        'action' => 'ConnectController.autoCompleteContact'
    ));
});