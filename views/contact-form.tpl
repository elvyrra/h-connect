
{{ $form->fieldsets['_submits'] }}

{assign name="general"}
    {{ $form->fieldsets['general'] }}
{/assign}


{assign name="custom"}
    {{ $form->fieldsets['custom'] }}
{/assign}

<div class="row" >
    <div class="col-md-6">{panel type="info" content="{$general}" title="{Lang::get('h-connect.contact-general-title')}"}</div>

    <div class="col-md-6">{panel type="info" content="{$custom}" title="{Lang::get('h-connect.contact-details-title')}"}</div>
</div>