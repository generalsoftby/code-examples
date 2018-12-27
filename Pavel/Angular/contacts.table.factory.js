'use strict';

angular
    .module('app.contacts')
    .factory('ContactsTable', ContactsTable);


function ContactsTable(ContactsFactory, $translate, $window, GocMsgBusFactory, ContactsRestangular, $uibModal, $log, $filter) {
    var ContactsTable = function (scope, multiselectTable) {
        this.scope = scope;

        this.multiselectTable = multiselectTable;

        this.exports = [
            'resetSelection', 'getContacts', 'paginate', 'perPageChanged',
            'checkAll', 'checkRow', 'checkRow', 'assignModal', 'deleteContacts',
            'notDeleted', 'selectByPermit'
        ];

        this.init();

        this.export();
    };

    ContactsTable.prototype = {
        init: function () {
            var self = this;
            this.scope.loading = true;
            this.scope.contacts = [];
            this.scope.amount = 0;
            this.scope.perPage = null;
            this.scope.deleted = [];
            this.scope.filters = {};
            this.scope.deletedContacts = [];
            this.scope.currentDeletedContactTmp = null;

            this.scope.selection = {
                flags: [],
                all: false,
                rows: []
            };

            this.scope.pagination = {
                perPage: 10,
                page: 1,
                totalPages: 0
            };

            GocMsgBusFactory.onMsg('contacts.filter_applied', function(e, args) {
                self.scope.filters = args;
                self.scope.pagination.page = 1;
                self.scope.pagination.totalPages = 0;

                if ($window.localStorage['openedContact'] === 'true') {
                    $window.localStorage['openedContact'] = false;
                    self.getContacts(parseInt($window.localStorage['paginationPage']));
                } else {
                    self.getContacts();
                }
            }, this.scope);

            GocMsgBusFactory.onMsg('contacts.restore_time_expired', function(e, args) {
                self.scope.deleted.splice(args.index, 1);
            }, this.scope);


            GocMsgBusFactory.onMsg('contacts.restored', function(e, args) {
                var deleted = self.scope.deleted[args.index];

                angular.forEach(deleted, function(contact){
                   contact.deleted = false;
                });

                self.scope.deleted.splice(args.index, 1);

            }, this.scope);

            this.scope.$on('$destroy', angular.bind(this, this.destroy));

            this.scope.perPageOptions = [];

            for (var i = 10; i <= 50; i += 10) {
                this.scope.perPageOptions.push({
                   value: i,
                   name: $translate.instant('contacts.per_page', {amount: i})
                });
            }

            this.scope.perPage = this.scope.perPageOptions[0];
        },

        resetSelection: function () {
            this.scope.selection.flags = [];
            this.scope.selection.all = false;
            this.scope.selection.rows = [];
            this.multiselectTable.clear();
        },

        getContacts: function(page) {
            var self = this;
            var request = angular.copy(this.scope.pagination);

            if (page) {
                request.page = page;
            }

            var promise = ContactsFactory.getFiltered(this.scope.filters, request);

            promise.then(function(data) {
                self.resetSelection();

                self.scope.pagination.total = data.total;
                self.scope.pagination.page = data.page;
                self.scope.pagination.totalPages = data.pages;
                self.scope.loading = false;

                self.scope.contacts = _.each(data.results, function (contact) {
                    contact.groups = _.filter(contact.groups, function (group) {
                        return _.isEmpty(group.deleted_at);
                    });

                    if(!contact.b2b_label) {
                        contact.labels.unshift({
                            'type': 'label',
                            'label': 'Privatkontakt'
                        });
                    }
                });
            });
        },

        paginate: function(page) {
            this.getContacts();
            $window.localStorage['paginationPage'] = page;
        },

        perPageChanged: function(item) {
            this.scope.pagination.perPage = item.value;
            this.getContacts();
        },

        checkAll: function(forced) {
            var self = this;

            if (forced) {
                self.scope.selection.all = forced;
            }

            this.scope.contacts.forEach(function(item, index) {
                self.scope.selection.flags[index] = self.scope.selection.all;
            });

            if (this.scope.selection.all) {
                this.multiselectTable.clear();
                var contacts = $filter('filter')(this.scope.contacts, this.scope.notDeleted);
                this.scope.selection.rows = this.multiselectTable.selectAll(contacts);
            } else {
                this.multiselectTable.clear();
                this.scope.selection.rows = [];
            }
        },

        checkRow: function(row, index) {
            this.scope.selection.all = false;
            this.scope.selection.rows = this.multiselectTable.selectRow(row, index);
        },

        assignModal: function(contact, type, multiple) {
            var self = this;

            if (multiple && this.scope.selection.rows.length === 0) {
                return;
            }
            $uibModal
                .open({
                    templateUrl: 'templates/contacts/contacts.assign.modal.html',
                    controller: 'ContactsAssignModalCtrl',
                    windowClass: 'gridster-modal contacts-modal',
                    resolve: {
                        searchFor: function() {
                            return type;
                        }
                    }
                }).result.then(function(result) {
                    var contacts = multiple ? _.pluck(self.scope.selection.rows, 'data') : [contact];

                    angular.forEach(contacts, function(_contact) {
                        angular.forEach(result, function(item) {

                            if (item.type === 'group') {
                                var found = _.find(_contact.groups, function (obj) { return item.obj.id === obj.id; });

                                if (found) {
                                    return;
                                }
                            }

                            _contact.groups.push(item.obj);
                            ContactsRestangular.one('contacts', _contact.id).one('groups', item.obj.id).put();
                        });

                    });
                });
        },

        deleteContacts: function(items, multiple) {
            var list = [];
            var self = this;

            if (!multiple) {
                items = [items];
            }

            if (items.length === 0) {
                return;
            }

            angular.forEach(items, function(item) {
                var contact = (typeof(item.index) !== 'undefined') ? item.data : item;
                list.push(contact);
                contact.deleted = true;

                // save a contact each time it's deleted (in order to be able to recover it)
                self.scope.deletedContacts.push(contact);
                self.scope.currentDeletedContactTmp = contact;

                ContactsRestangular.one('contacts', contact.id).remove();
            });

            this.scope.deleted.push(list);

            GocMsgBusFactory.emitMsg('contacts.deleted', list);

            this.resetSelection();
        },

        notDeleted: function(value) {
            return !value.deleted;
        },

        selectByPermit: function(value) {
            var self = this;
            this.resetSelection();

            var contacts = $filter('filter')(this.scope.contacts, this.scope.notDeleted);

            angular.forEach(contacts, function(contact, index){
                var contactTypes = contact.contact_types;
                var hasPermission = !!_.find(contactTypes, function(type) {
                    return type.status === 'permission_allowed';
                });

                if (hasPermission === value) {
                    self.scope.checkRow(contact, index);
                    self.scope.selection.flags[index] = true;
                }
            });
        },

        export: function() {
            var self = this;

            angular.forEach(this.exports, function(item) {
                self.scope[item] = angular.bind(self, self[item]);
            });
        }
    };

    return ContactsTable;
}
