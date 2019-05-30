'use strict';

angular
    .module('app.contacts')
    .factory('ContactsTableConfigFactory', ContactsTableConfigFactory);

function ContactsTableConfigFactory(ContactsFieldsValuesFactory, $log, GocMsgBusFactory, $rootScope, Helpers) {
    var ContactsTableConfigFactory = function(scope, config) {
        scope.widthRange = [];
        scope.columnsValues = ContactsFieldsValuesFactory.getFields();
        scope.columnsValues.unshift(null);

        var initial = true;

        scope.controlsConfig = {
            live: true,
            fixed: []
        };

        scope.width = [];

        scope.columnsConfig = {
            width: config.width,
            fields: config.fields
        };

        angular.forEach(scope.columnsConfig.fields, function (field) {
            field.title = Helpers.translate('contacts.fields_list.' + field.name);
        });

        function generateRange(count) {
            scope.widthRange = [];

            for (var i = 5; i <= 100 - (5 * count); ++i) {
                scope.widthRange.push(i + '%');
            }
        }

        generateRange(scope.columnsConfig.fields.length);

        scope.isFieldSelected = function(i) {
            return scope.columnsConfig.fields[i] && scope.columnsConfig.fields[i].type;
        };

        scope.fixPercent = function(i, second) {
            var summ = 0;
            var summFixed = 0;
            var reservedSumm = 0;
            var n = 0;

            var diff = parseInt(scope.width[i]) > parseInt(scope.columnsConfig.width[i]) ?
                5 : 0;

            for (var j = 0; j < 6; ++j) {
                if (scope.isFieldSelected(j) && !scope.controlsConfig.fixed[j]) {
                    var value = parseInt(scope.width[j]);
                    if (isNaN(value)) {
                        return;
                    }
                    summ += value;
                    if ((i !== j && parseInt(scope.width[j]) > diff) || initial) {
                        ++n;
                        reservedSumm += parseInt(scope.width[j]);
                    }
                }

                if (scope.controlsConfig.fixed[j]) {
                    summFixed += parseInt(scope.width[j]);
                }
            }

            if (!initial && ((summ - (100 - summFixed)) > reservedSumm && diff === 5)) {
                scope.width[i] = scope.columnsConfig.width[i];
                return;
            }

            var sub = Math.ceil((summ - (100 - summFixed)) / n);

            for (var j = 0; j < 6; ++j) {
                if (!scope.isFieldSelected(j)) {
                    continue;
                }
                if ((i !== j && j !== second && !scope.controlsConfig.fixed[j] && parseInt(scope.width[j]) > diff) || initial) {
                    scope.width[j] = Math.max(1, parseInt(scope.width[j]) - sub) + '%';
                }

                scope.columnsConfig.width[j] = scope.width[j];
            }
        };

        for (var i = 0; i < 6; ++i) {
            if (!scope.columnsConfig.width[i]) {
                scope.columnsConfig.width[i] = '0%';
            }
            scope.width[i] = scope.columnsConfig.width[i];
            scope.controlsConfig.fixed[i] = false;
        }

        scope.fixPercent(-1);
        initial = false;

        var fieldsInit = false;
        scope.$watchCollection('columnsConfig.fields', function(newValue, oldValue){
            if (!fieldsInit) {
                fieldsInit = true;
                return;
            }

            var isNew = false, n = 0, isOld = false;
            for (var i = 0; i < 6; ++i) {
                if (newValue[i] && !oldValue[i]) {
                    isNew = i;
                }
                if (!newValue[i] && oldValue[i]) {
                    isOld = i;
                }
                if (newValue[i]) {
                    ++n;
                }
            }

            generateRange(n);

            if (isNew !== false) {
                scope.columnsConfig.width[isNew] = scope.width[isNew] = Math.round(100 / n) + '%';
                scope.fixPercent(isNew);
            }

            if (isOld !== false) {
                scope.columnsConfig.width[isOld] = scope.width[isOld] = 0 + '%';
                scope.fixPercent(isOld);
            }
        });

        var liveInit = true;
        scope.$watch('controlsConfig.live', function(){
            if (scope.controlsConfig.live) {
                GocMsgBusFactory.emitMsg('dynamic-table-update', scope.columnsConfig);
            } else {
                GocMsgBusFactory.emitMsg('dynamic-table-update', angular.copy(scope.columnsConfig));
            }
            if (liveInit) {
                liveInit = false;
                return;
            }
            GocMsgBusFactory.emitMsg('contacts.live_toggle', {flag: !scope.controlsConfig.live});
        });


        scope.$watch('controlsConfig.strict', function(){
            if (scope.controlsConfig.strict) {
                scope.fixPercent(-1);
            }
        });

        scope.$watchCollection('columnsConfig.fields', function() {
            GocMsgBusFactory.emitMsg('contacts.columnsConfig.fields.changed');
        });

        var dragOffset = 0;

        GocMsgBusFactory.onMsg('dynamic-table-drag', function(e, args){
            var tableWidth = $rootScope.tableWidth;
            var diff = args.offset / tableWidth * 100;

            dragOffset += diff;

            if (Math.abs(dragOffset) < 1) {
                return;
            }

            dragOffset = Math.sign(dragOffset) * Math.round(Math.abs(dragOffset));

            var i = args.index;
            var currentWidth = parseInt(scope.width[i]);

            var widthI = currentWidth - dragOffset;
            var delta = widthI - currentWidth;
            var widthJ = parseInt(scope.width[i - 1]) - delta;

            if ((dragOffset < 0 && (widthJ < 5 || widthI > 94)) ||
                (dragOffset > 0 && (widthI < 5 || widthJ > 94))) {

                return;
            }

            scope.$apply(function(){

                scope.width[i] =  widthI + '%';
                scope.width[i - 1] = widthJ + '%';

                scope.fixPercent(i, i - 1);
            });
            dragOffset = 0;

        });

    };

    return ContactsTableConfigFactory;


}
