'use strict';

angular
    .module('app.contacts')
    .controller('ContactsDetailsMailingCtrl', ContactsDetailsMailingCtrl);

function ContactsDetailsMailingCtrl($scope, $log, $translate, $timeout, $filter, ContactMailing, $stateParams, GocMsgBusFactory, $element) {
    $log.info('ContactsDetailsMailingCtrl');
    $scope.filters = null;

    $scope.records = [];

    $scope.isScrollShown = false;

    $scope.filteredRecords = [];

    $scope.getList = function (filters) {
        ContactMailing.getList($stateParams.contactId, null, null, filters).then(function (res) {
            $scope.records = _.values(res.plain());
            $scope.filterRecords();
        })
    };



    $scope.filterRecords = function () {
        $scope.filteredRecords = $filter('mailing')($scope.records, $scope.filters);

        if ($scope.filteredRecords.length) {
            $scope.selectRecord($scope.filteredRecords[0]);

            return;
        }

        $scope.selectedRecord = null;
        $scope.$broadcast('scrollbarRebuild:mailingHistory');
    };

    GocMsgBusFactory.onMsg('contacts.b2c.mailing.filter_applied', function (e, args) {
        $scope.filters = {
            name: args.q,
            statuses: _.map(args.types, 'id'),
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

        $timeout(function () {
            $scope.selectedRecord = record;
            $scope.selectedRecord.link = $scope.selectedRecord.link || Routing.generate('communication_mailing_details', {id: $scope.selectedRecord.mailing.id});
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
        var leftColumn = $element.find('.left.floatleft');
        var header = leftColumn.find('.headline');
        var records = leftColumn.find('.record');

        var columnHeight = parseFloat(leftColumn.css('height'));
        var contentHeight = parseFloat(header.css('height'));

        angular.forEach(records, function (record) {
            var element = angular.element(record);
            contentHeight += parseFloat(element.css('height'));
        });

        return columnHeight - contentHeight <= records.length;
    };

    $scope.mapStatusNames = {
        pending: 'In der Zielgruppe',
        queued: 'Versendet',
        open: 'Geöffnet',
        click: 'Geklickt',
        bounce: 'Nicht versendet',
        unsubscribe: 'Abgemeldet',
        spam: 'Nicht versendet'
    };

    $timeout(function () {
        $scope.getList({});
    }, 0);
}
