/*global app, ko */

'use strict';

require(['app'], function() {

    $(".delete-group").click(function(){
        if(confirm(Lang.get("h-connect.delete-group-confirmation"))){
            $.get(app.getUri("h-connect-group-delete", {groupId : $(this).data("group")}), function(){
                app.load(app.getUri("h-connect-contact-index") + "?tabs=group");
            });
        }
    });
});