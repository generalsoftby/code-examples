'use strict';

angular
    .module('app.contacts')
    .controller('ContactsDetailsCtrl', ContactsDetailsCtrl);

function ContactsDetailsCtrl($scope,  ContextFactory, $log, $stateParams, $window, ContactsFactory, $translate, cfpLoadingBar, $context, GocMsgBusFactory, $state) {
    $log.info('ContactsDetailsCtrl');

    if (!ContextFactory.current) {
        ContextFactory.getCurrentContext();
    }

    function getContact(result) {
        if (!result) {
            $scope.contact = $scope.legacy.contact = {
                newOne: true,
                id: contactId
            };
        } else {
            $scope.contact = $scope.legacy.contact = result;
            ContactsFactory.setCurrentContact($scope.contact.plain());
        }
    }

    $scope.currentContext = ContextFactory;
    $scope.$state = $state;

    var contactId = $stateParams.contactId;

    $scope.legacy = {
        contact: null
    };

    $scope.savePanelData = {};

    $scope.countOfLabels = 0;

    ContactsFactory.getById(contactId, getContact);
    $scope.$watch('currentContext', function(response) {
        if (response.current) {
            $scope.currentContext = response.current;
        }
    }, true);

    GocMsgBusFactory.onMsg('contacts.information.add.new.field', function(e, args) {
       $scope.savePanelData[args[2]] = angular.copy(args);
       for (var key in $scope.savePanelData) {
           $scope.countOfLabels++;
       }
    });

    $scope.avatarOptions = {
        sending: function (file, xhr) {
            xhr.setRequestHeader('Angular-Request', 'true');
            cfpLoadingBar.start();
        },
        success: function (file) {
            $scope.contact.avatar = {filename: file.name};
            cfpLoadingBar.complete();
        },
        uploadprogress: function (file, progress) {
            cfpLoadingBar.set(progress / 100);
        },
        url: Routing.generate('goc_contact_api_post_context_contact_avatars', {
            context: $context,
            contact: contactId
        })
    };

    $scope.addLabel = function() {
        GocMsgBusFactory.emitMsg('add-b2b-label');
    };

    $scope.getTopInclude = function() {
        if ($stateParams.b2b || ContactsFactory.isB2B($scope.contact)) {
            return '/templates/contacts/details/contacts.details.top.b2b.html';
        }

        return '/templates/contacts/details/contacts.details.top.b2c.html';
    };

    $scope.getContentInclude = function() {
        if ($stateParams.b2b || ContactsFactory.isB2B($scope.contact)) {
            return '/templates/contacts/details/information/b2b/contacts.details.information.blocks.html';
        }

        return '/templates/contacts/details/information/b2c/contacts.details.information.blocks.html';
    };

    $scope.goBack = function() {
        $window.history.back();
    };

    $scope.isPersisted = function(contact) {
        return !angular.isEmpty(contact) && !contact.newOne;
    };

    $scope.isB2B = function() {
        return $stateParams.b2b || ContactsFactory.isB2B($scope.contact);
    };

    $scope.perPageOptions = [];

    for (var i = 10; i <= 50; i += 10) {
        $scope.perPageOptions.push({
           value: i,
           name: $translate.instant('contacts.per_page', {amount: i})
        });
    }
}
