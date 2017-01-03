{assign name="content"}
	{{ $form->fieldsets['general'] }}

	<fieldset e-show="type == 'radio' || type == 'select'">
		<legend>{{ $form->fieldsets['parameters']->legend }}</legend>
		{{ $form->inputs['parameters'] }}

		{{ $form->inputs['options'] }}
	</fieldset>

	{{ $form->fieldsets['_submits'] }}
{/assign}

{form id="{$form->id}" content="{$content}"}

<script type="text/javascript">

/*global app, ko, Lang */

'use strict';

(function(){

    class HQuestionContact extends EMV {
        
        /**
         * Constructor
         * @param  {Object} data The initial data of the element
         */
        constructor(data) {

            super({
            	data : data/*,
		    	computed : {
			        parameters : function() {
			            return JSON.stringify({
							options : this.options ? this.options.split("\n") : [],
						});
			        }
		    	}
            })
        }
    }

    var form = app.forms['h-contact-question-form'];

    const emv = new HQuestionContact({
        type: form.inputs.type.val(),
        otpions: JSON.parse(form.inputs.parameters.val()).options ? JSON.parse(form.inputs.parameters.val()).options.join("\n") : '',
        parameters : JSON.parse(form.inputs.parameters.val())
    });

    emv.$apply(form.node.get(0));
})();
	
	/*
	(function(){

		var parameters = JSON.parse(app.forms[""].inputs['parameters'].val());
		var model = {
			type : ko.observable(app.forms["h-contact-question-form"].inputs['type'].val()),
			options : ko.observable(parameters.options ? parameters.options.join("\n") : ''),
		};
		
		model.parameters = ko.computed(function(){
			return JSON.stringify({
				options : this.options() ? this.options().split("\n") : [],
			});
		}.bind(model));

		ko.applyBindings(model, $("#h-contact-question-form").get(0));
	})();
	*/
</script>