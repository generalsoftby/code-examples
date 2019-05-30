'use strict';

angular
    .module('app.contacts')
    .controller('AdticketElvisContactsDetailsMailingCtrl', [
        '$scope',
        '$log',
        '$translate',
        '$timeout',
        '$filter',
        'ContactMailing',
        '$stateParams',
        'GocMsgBusFactory',
        '$element',
        'ContactsFactory',
        AdticketElvisContactsDetailsMailingCtrl
    ]);

function AdticketElvisContactsDetailsMailingCtrl(
    $scope,
    $log,
    $translate,
    $timeout,
    $filter,
    ContactMailing,
    $stateParams,
    GocMsgBusFactory,
    $element,
    ContactsFactory
) {
    $log.info('AdticketElvisContactsDetailsMailingCtrl');

    $scope.filters = {
        q: '',
        statuses: [],
        activateDates: []
    };

    $scope.isScrollShown = false;

    $scope.getList = function (filters) {
        return ContactMailing.getList($stateParams.contactId, null, null, filters).then(function (res) {
            return _.values(res.plain());
        });
    };

    $scope.filteredRecords = [];

    $scope.filterRecords = function () {
        if (ContactsFactory.currentContact) {
            $scope.getList($scope.filters).then(function (res) {
                $scope.filteredRecords = res;
                $scope.selectRecord(_.first($scope.filteredRecords));
                $scope.$broadcast('scrollbarRebuild:mailingHistory');
            });
        }
    };

    GocMsgBusFactory.onMsg('contacts.b2c.mailing.filter_applied', function (e, args) {
        $scope.filters = {
            q: args.q,
            types: _.map(args.types, 'id'),
            activateDates: args.activateDates
        };

        $scope.filterRecords();
    });

    $scope.selectedRecord = null;

    $scope.selectRecord = function (record) {
        if ($scope.isSelectedRecord(record)) {
            return;
        }

        $scope.selectedRecord = null;

        if (!record) {
            return;
        }

        $timeout(function () {
            $scope.selectedRecord = record;
            $scope.selectedRecord.link = $scope.selectedRecord.link ||
                Routing.generate('communication_mailing_details', { id: $scope.selectedRecord.mailing.id });
        });
    };

    $scope.rebuildScrollBar = function () {
        $timeout(function () {
            $scope.$broadcast('scrollbarRebuild:mailingHistory');
        });
    };

    $scope.isSelectedRecord = function (record) {
        if (!record || !$scope.selectedRecord) {
            return false;
        }

        return record.mailing.id === $scope.selectedRecord.mailing.id && record === $scope.selectedRecord;
    };

    $scope.checkBottomBorder = function () {
        var recordsList = $element.find('#records-list');
        var header = recordsList.find('.headline');
        var records = recordsList.find('.record');

        var columnHeight = parseFloat(recordsList.css('height'));
        var contentHeight = parseFloat(header.css('height'));

        angular.forEach(records, function (record) {
            var element = angular.element(record);
            contentHeight += parseFloat(element.css('height'));
        });

        return columnHeight - contentHeight <= records.length;
    };

    $scope.getCommonGroups = function (historyRecord, contact) {
        return _.intersection(_.pluck(contact.groups, 'name'), _.pluck(historyRecord.mailing.groups, 'name'));
    };

    $scope.statusNamesMap = {
        pending: 'In der Zielgruppe',
        queued: 'Versendet',
        open: 'Geöffnet',
        click: 'Geklickt',
        bounce: 'Nicht versendet',
        unsubscribe: 'Abgemeldet',
        spam: 'Nicht versendet'
    };
}
