/*global app, ko */

'use strict';

require(['app', 'jquery', 'lang', 'emv'], (app, $, Lang, EMV) => {
    $("#h-connect-group-list .delete-group").click(function(){
        if(confirm(Lang.get("h-connect.delete-group-confirmation"))){
            $.get(app.getUri("h-connect-group-delete", {groupId : $(this).data("group")}), function(){
                app.load(app.getUri("h-connect-contact-index") + "?tabs=group");
            });
        }
    });
});