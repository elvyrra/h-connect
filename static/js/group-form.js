/*global app, ko, Lang */

'use strict';

require(['app'], function() {

    var GroupModel = function() {
        this.search = ko.observable(form.inputs.search.val());
        this.contacts = ko.observableArray(JSON.parse(form.inputs.contacts.val()));
        this.contacts.extend({
            notify: 'always'
        });

        this.contactAutocompleteSource = ko.computed(function() {
            return app.getUri('h-connect-contact-autocomplete');
        });
    };

    /**
     * Add a contact to the group
     * @param {Object} contact The contact to add
     */
    GroupModel.prototype.addContact = function(contact) {
        if (!contact.id) {
            return;
        }

        var id = parseInt(contact.id),
            existing = this.contacts().filter(function(c) {
                return c.id === id;
            });

        if(existing.length) {
            alert(Lang.get('h-connect.contact-already-in-group'));
            return;
        }

        this.contacts.push({
            lastName  : contact.lastName,
            firstName : contact.firstName,
            id        : id,
            label : contact.label
        });
    };

    /**
     * Remove a contact on the group
     * @param   {Obejct} contact The contact to remove
     */
    GroupModel.prototype.removeContact = function(contact) {
        this.contacts.splice(this.contacts.indexOf(contact), 1);
    };

    /**
     * Action to perform wen the user chose a contact by autocomplete
     * @param   {Object} line The chosen line
     */
    GroupModel.prototype.onContactChosen = function(line) {
        if (line) {
            this.addContact(line);
            this.search('');
            this.search = '';
        }
    };

    ko.applyBindings(new GroupModel(), form.node.get(0));
})();

