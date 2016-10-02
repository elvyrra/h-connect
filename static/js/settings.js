/*global app, ko */


'use strict';

require(['app'], function() {

	$(".delete-contact-question").click(function(){
        if(confirm(Lang.get('h-connect.delete-contact-question-confirmation'))) {
            $.get(app.getUri("h-connect-remove-contact-question", {name : $(this).data("question")}), function(){
                app.load(app.getUri("h-connect-contact-index") + "?tabs=settings");
            });
        }
    });
});
