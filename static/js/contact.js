/*global app, ko */

'use strict';

require(['app'], function() {
    var list = app.lists['h-connect-my-contacts-list'];

    $(".delete-contact").click(function(){
        if(confirm(Lang.get("h-connect.delete-contact-confirmation"))){
            $.get(app.getUri("h-connect-contact-delete", {contactId : $(this).data("contact")}), function(){
                app.load(app.getUri("h-connect-contact-index"));
            });
        }
    });


    $('#export-contact-list-button').click(function() {
        var selectedLines = [];

        list.node.find('.list-select-line:checked').each(function() {
            selectedLines.push($(this).attr('value'));
        });

        window.open(app.getUri('h-connect-contact-export') + (selectedLines.length ? '?contacts=' + selectedLines.join(',') : ''));
    });
});
