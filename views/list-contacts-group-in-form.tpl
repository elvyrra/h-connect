</div>

<div e-show="contacts.length > 0">
	<hr />
	<h3>{text key="h-connect.group-contacts-label"}</h3>

	<div e-each="contacts" class="alert alert-info group-list-contact">
	    <span e-text="label"></span>
	    <i class="icon icon-close icon-lg pull-right text-primary pointer" e-click="$root.removeContact.bind($root)"></i>
	</div>
</div>
<div>