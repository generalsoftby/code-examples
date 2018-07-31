'use strict';

angular
    .module('app.contacts')
    .factory('ContactsFieldsFactory', ContactsFieldsFactory);

function ContactsFieldsFactory(ContactsV2Restangular, Helpers) {
    var ContactsFieldsFactory = {};

    ContactsFieldsFactory.getAll = function () {
        return ContactsV2Restangular.one('fieldsettings').get({limit: 0}).then(function (response) {
            var result = response.plain();
            Helpers.snakeToCamelForObject(result);

            return result;
        }, function (reject) {
            return reject;
        });
    };

    ContactsFieldsFactory.getByName = function (name) {
        return ContactsV2Restangular.one('fieldsettings').one('name', name).get();
    };

    ContactsFieldsFactory.add = function (fieldObject) {
        return ContactsV2Restangular.all('fieldsettings').post(fieldObject);
    };

    ContactsFieldsFactory.update = function (fieldObject) {
        var putObject = {};

        putObject.name = fieldObject.name;
        putObject.option = fieldObject.option;

        return ContactsV2Restangular.one('fieldsettings', fieldObject.id).customPUT(putObject);
    };

    ContactsFieldsFactory.delete = function (fieldId) {
        return ContactsV2Restangular.one('fieldsettings', fieldId).remove();
    };

    return ContactsFieldsFactory;
}
