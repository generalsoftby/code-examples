angular
    .module('app.contacts')
    .directive('textBox', textBox);

function textBox(InitialFieldService) {
    return {
        restrict: 'E',
        templateUrl: 'contacts.textBox.html',
        scope: {
            settings: '=',
            value: '='
        },
        link: function (scope) {
            var numberMask = '\\d';
            var textMask = 'a-zA-Zа-яА-ЯöäüÖÄÜßÀàÈèÌìÒòÙùÁáÉéÍíÓóÚúÝýÂâÊêÎîÔôûÑñÕõÃãÑñÕõÃãËëÏïŸÿ\\b\\t\\s';
            var specMask = '';
            var resultMask = '';

            scope.placeholder = scope.settings.placeholder || '';

            if (scope.settings.allowSpecSymbols) {
                if (scope.settings.allCheckedSpecSymbols) {
                    var specSymbols = InitialFieldService.getDefaultSetting('numberinput', 'allowedSpecSymbols');
                    angular.forEach(specSymbols, function (symbol) {
                        specMask += '\\' + symbol;
                    });
                } else {
                    angular.forEach(scope.settings.allowedSpecSymbols, function (symbol) {
                        specMask += '\\' + symbol;
                    });
                }
            }

            switch (scope.settings.subtype) {
                case 'symbol':
                    resultMask = '^[' + textMask + specMask + ']+$';
                    break;
                case 'number':
                    resultMask = '^[' + numberMask + specMask + ']+$';
                    break;
                case 'all':
                    resultMask = '^[' + textMask + numberMask + specMask + ']+$';
                    break;
            }

            resultMask = new RegExp(resultMask);

            scope.setValue = function (text) {
                if ((angular.isDefined(text) && resultMask.test(text)) || text === '') {
                    scope.value = text;
                }

                return scope.value;
            };
        }
    };
}
