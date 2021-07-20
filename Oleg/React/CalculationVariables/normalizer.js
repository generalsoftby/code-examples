/**
 * Normalizes data of calculation variables by the given type.
 * Returns useful data for the server.
 *
 * @param {string} type
 * @param {object} data
 * @return {object}
 */
export function normalize(type, data) {
    switch (type) {
        case 'print_formats':
            return normalizePrintFormats(data);
        case 'number_of_products':
        case 'number_of_pages_in_block':
            return normalizeNumberOfProducts(data);
    }

    return data;
}

/**
 * Converts the server format of data of component to the frontend format of data.
 *
 * @param {string} type
 * @param {object} data
 */
export function denormalize(type, data) {
    switch (type) {
        case 'print_formats':
            return denormalizePrintFormats(data);
        case 'number_of_products':
        case 'number_of_pages_in_block':
            return denormalizeNumberOfProducts(data);
    }

    return data;
}

/**
 * Normalizes data of PrintFormats.
 *
 * @param {object} data
 * @return {object}
 */
function normalizePrintFormats(data) {
    // Костыль для извлечения данных при отсутствии значения для старого
    // режима калькулятора.
    let selectedFormat = {
        value: '',
        name: 'custom'
    };

    if (data.selectedFormat) {
        selectedFormat = data.selectedFormat;
    }

    return {
        height: data.height,
        width: data.width,
        format: {
            index: selectedFormat.value,
            name: selectedFormat.label,
        },
    };
}

/**
 * Converts server data of the component to frontend data.
 *
 * @param {object} data
 * @return {object}
 */
function denormalizePrintFormats(data) {
    return {
        height: data.height,
        width: data.width,
        selectedFormat: {
            value: data.format.index,
            label: data.format.name,
        },
    };
}

/**
 * Normalizes data of NumberOfProducts.
 *
 * @param {number} data
 * @returns {object}
 */
function normalizeNumberOfProducts(data) {
    return {
        value: data,
    };
}

/**
 * Converts server data of the component to frontend data.
 *
 * @param {object} data
 * @return {number}
 */
function denormalizeNumberOfProducts(data) {
    return data.value;
}
