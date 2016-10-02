</div>

<div ko-visible="contacts().length > 0">
	<hr />
	<h3>{text key="h-connect.group-contacts-label"}</h3>

	<!-- ko foreach: contacts -->
	<div class="alert alert-info group-list-contact">
	    <span ko-text="label"></span>
	    <i class="icon icon-close icon-lg pull-right text-primary pointer" ko-click="$parent.removeContact.bind($parent)"></i>
	</div>
	<!-- /ko -->
</div>
<div>