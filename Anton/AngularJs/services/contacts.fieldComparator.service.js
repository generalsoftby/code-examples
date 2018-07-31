'use strict';

angular
    .module('app.contacts')
    .service('FieldsComparator', FieldsComparator);

function FieldsComparator(InitialFieldService) {
    var convertToComparable = function (a) {
        switch (typeof a) {
            case 'number':
            case 'string':
                return String(a);
            case 'object':
                return _.isNull(a) ? false : a;
            case 'undefined':
                return false;
            default:
                return a;
        }
    };

    this.compareSettings = function (firstFieldSettings, secondFieldSettings) {
        if (firstFieldSettings.type !== secondFieldSettings.type) {
            return false;
        }

        var equal = true;
        var settingsList = InitialFieldService.getSettingsList(firstFieldSettings.type);
        var specialComparableSettings = [];

        angular.forEach(settingsList, function (key) {
            if (!equal) {
                return;
            }

            if (_.isObject(firstFieldSettings[key]) || _.isObject(secondFieldSettings[key])) {
                specialComparableSettings.push(key);

                return;
            }

            equal = convertToComparable(firstFieldSettings[key]) === convertToComparable(secondFieldSettings[key]);
        });

        angular.forEach(specialComparableSettings, function (key) {
            if (!equal) {
                return;
            }

            switch (firstFieldSettings.type) {
                case 'numberinput':
                    if (_.isArray(firstFieldSettings[key]) || _.isArray(secondFieldSettings[key])) {
                        equal = _.isEqual(_.sortBy(firstFieldSettings[key]), _.sortBy(secondFieldSettings[key]));
                    } else {
                        equal = _.isEqual(firstFieldSettings[key], secondFieldSettings[key]);
                    }

                    break;
                default:
                    equal = _.isEqual(firstFieldSettings[key], secondFieldSettings[key]);
                    break;
            }
        });

        return equal;
    };

    this.compareFields = function (firstField, secondField) {
        return convertToComparable(firstField.name) === convertToComparable(secondField.name) && this.compareSettings(firstField.option, secondField.option);
    }
}
