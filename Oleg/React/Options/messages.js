import {
    OPTION_WAS_NOT_SELECTED,
    WRONG_NUMBER_OF_VALUES,
    VALUES_WAS_NOT_ENTERED,
    INCORRECT_RANGE_VALUE
} from '../../services/options/validation';

/**
 * Returns a message by the given type of the error.
 *
 * @param {string} type
 * @param {object} params
 * @return {string}
 */
export default function getMessageByType(type, params) {
    let message;

    switch (type) {
        case OPTION_WAS_NOT_SELECTED:
            return 'Опция обязательна для заполнения.';
        case WRONG_NUMBER_OF_VALUES:
            return 'Неверное количество выбранных значений. Максимальное количество выбранных значений: ' + params.max + '.';
        case VALUES_WAS_NOT_ENTERED:
            message = 'Не введено значение';

            return params.label ? message + ' "' + params.label + '".' : message + '.';
        case INCORRECT_RANGE_VALUE:
            message = 'Значение не входит в заданный диапазон: от ' + params.min;

            return params.max
                ? message + ' до ' + params.max + '.'
                : message + '.'
            ;
    }

    return 'Ошибка при валидации данных.';
}
