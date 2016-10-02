{assign name="content"}
	{{ $form->fieldsets['general'] }}

	<fieldset ko-visible="type() == 'radio' || type() == 'select'">
		<legend>{{ $form->fieldsets['parameters']->legend }}</legend>
		{{ $form->inputs['parameters'] }}

		{{ $form->inputs['options'] }}
	</fieldset>

	{{ $form->fieldsets['_submits'] }}
{/assign}

{form id="{$form->id}" content="{$content}"}

<script type="text/javascript">
	(function(){

		var parameters = JSON.parse(app.forms["h-contact-question-form"].inputs['parameters'].val());
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
</script>