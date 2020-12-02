import React from 'react';
import PropTypes from 'prop-types';
import CalculatorSelect from "../Common/CalculatorSelect";
import SizeOfPrintFormat, { UNKNOWN_ERROR, MAX_ERROR, MIN_ERROR, NAN_ERROR } from "./SizeOfPrintFormat";
import { isEqual } from 'lodash';

const VALUES_ARE_NAN_ERROR = 'VALUES_ARE_NAN_ERROR';
const HEIGHT_IS_NAN_ERROR = 'HEIGHT_IS_NAN_ERROR';
const HEIGHT_LESS_THAN_MIN_ERROR = 'HEIGHT_LESS_THAN_MIN_ERROR';
const HEIGHT_GREATER_THAN_MAX_ERROR = 'HEIGHT_GREATER_THAN_MAX_ERROR';
const WIDTH_IS_NAN_ERROR = 'WIDTH_IS_NAN_ERROR';
const WIDTH_GREATER_THAN_MAX_ERROR = 'WIDTH_GREATER_THAN_MAX_ERROR';
const WIDTH_LESS_THAN_MIN_ERROR = 'WIDTH_LESS_THAN_MIN_ERROR';

const HEIGHT_SOURCE = 'HEIGHT_SOURCE';
const WIDTH_SOURCE = 'WIDTH_SOURCE';
const BOTH_SOURCE = 'BOTH_SOURCE';

export default class PrintFormats extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            width: '',
            height: '',
            selectedFormat: null,
            wasManualChanged: false,
        };

        this.handleSelectSizeChange = this.handleSelectSizeChange.bind(this);
        this.handleInputSizeChange = this.handleInputSizeChange.bind(this);
        this.changeValue = this.changeValue.bind(this);
    }

    /**
     * Initializes data of the component.
     */
    componentDidMount() {
        this.initOptionsForSelect();
        this.initLabel();
        this.initRules();

        // Sends initialized values of the component to the callback
        this.changeValue();
    }

    /**
     * Initializes options and formats for Select.
     */
    initOptionsForSelect() {
        this.options = this.initOptionsOfPrintFormats(
            this.props.formats, this.props.rules.fixed_print_formats
        );
        this.formats = Object.values(this.props.formats);
    }

    /**
     * Initializes the label.
     */
    initLabel() {
        this.label = typeof this.props.config.label !== 'undefined'
            ? this.props.config.label
            : 'Формат печати'
        ;
    }

    /**
     * Initializes rules to validate.
     */
    initRules() {
        if (typeof this.props.rules.height !== 'undefined') {
            this.minHeight = this.props.rules.height.min ? Number.parseInt(this.props.rules.height.min) : 1;
            this.maxHeight = this.props.rules.height.max ? Number.parseInt(this.props.rules.height.max) : Infinity;
            this.visibleLimitationOfHeight = this.props.rules.height.visible_limitation;
        } else {
            this.minHeight = 1;
            this.maxHeight = Infinity;
            this.visibleLimitationOfHeight = false;
        }

        if (typeof this.props.rules.width !== 'undefined') {
            this.minWidth = this.props.rules.width.min ? Number.parseInt(this.props.rules.width.min) : 1;
            this.maxWidth = this.props.rules.width.max ? Number.parseInt(this.props.rules.width.max) : Infinity;
            this.visibleLimitationOfWidth = this.props.rules.width.visible_limitation;
        } else {
            this.minWidth = 1;
            this.maxWidth = Infinity;
            this.visibleLimitationOfWidth = false;
        }

        this.updateUntil = this.props.config.updateUntil;
    }

    /**
     * Optimizes rendering components. Compares the value and errors.
     *
     * @param {object} nextProps
     * @return {boolean}
     */
    shouldComponentUpdate(nextProps) {
        if (this.props.value.width !== nextProps.value.width || this.props.value.height !== nextProps.value.height) {
            return true;
        } else if (!isEqual(this.props.errors, nextProps.errors)) {
            return true;
        } else if (
            this.updateUntil === 'first_manual_change' &&
            !this.state.wasManualChanged &&
            !isEqual(this.props.broadcastingValue, nextProps.broadcastingValue)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Updates values by broadcasting values when it uses 'first_manual_change'.
     *
     * @param {object} prevProps
     */
    componentDidUpdate(prevProps) {
        if (
            this.updateUntil === 'first_manual_change' &&
            !this.state.wasManualChanged &&
            (
                !isEqual(this.props.broadcastingValue, prevProps.broadcastingValue) ||
                !isEqual(this.props.broadcastingValue, {
                    height: this.state.height,
                    width: this.state.height,
                    selectedFormat: this.state.selectedFormat,
                }))
        ) {
            this.setState(this.props.broadcastingValue, this.changeValue);
        }
    }

    /**
     * Defines options of allowed print formats.
     * Returns an array with options in the form of an object.
     *
     * @param  {object} formats Print formats
     * @param  {boolean} fixed  Fixed formats
     * @return {object[]}
     */
    initOptionsOfPrintFormats(formats, fixed) {
        // Options for the select
        const options = Object.values(formats).map((format, index) => ({
            value: index + 1, // NOTE: Добавляем 1, потому что нумерация объектов начинается с 1, а массив с 0
            label: format.name
        }));

        // Adds the option for an unstandard size
        if (!fixed) {
            options.unshift(this.getCustomFormat());
        }

        return options;
    }

    /**
     * Returns the option of a custom format.
     *
     * @return {object}
     */
    getCustomFormat() {
        return {
            value: 'custom',
            label: 'Нестандартный размер'
        };
    }

    /**
     * Changes sizes width and height usign a selected size.
     * Runs after changing the select with sizes.
     *
     * @param {object} event An event of ReactSelect
     */
    handleSelectSizeChange(event) {
        const selectedFormat = this.options.find(option => option.value === event.value);

        if (typeof selectedFormat === 'undefined' || selectedFormat.value === 'custom') {
            this.setState({
                selectedFormat,
                wasManualChanged: true
            }, this.changeValue);
            this.props.onValueChange(this.props.name, {selectedFormat});
        } else if (typeof this.formats.find(format => format.name === event.label) !== 'undefined') {
            const format = this.formats.find(format => format.name === event.label);

            this.setState({
                selectedFormat,
                width: format.width,
                height: format.height,
                wasManualChanged: true,
            }, this.changeValue);
        }
    }

    /**
     * Handles a change of input sizes.
     *
     * @param {object} event
     */
    handleInputSizeChange(event) {
        const value = Number.parseInt(event.target.value);

        // Only digits and space
        if (Number.isNaN(value) && event.target.value.trim() !== '') {
            return;
        }

        const property = event.target.name === ("calculationVariables[" + this.props.name + "][width]")
            ? 'width'
            : 'height'
        ;
        const otherProperty = property === 'width' ? 'height' : 'width';

        const newState = {
            selectedFormat: this.getCustomFormat(),
            [property]: value,
            [otherProperty]: this.props.value[otherProperty],
            wasManualChanged: true,
        };

        this.setState(newState, this.changeValue);
    }

    /**
     * Normalizes values, checks values and calls the callback.
     */
    changeValue() {
        const values = {
            height: this.state.height,
            width: this.state.width,
            selectedFormat: this.state.selectedFormat,
        };

        this.props.onChange(this.props.name, values, this.validate(values));
        this.props.onValueBroadcast([{
            // Sends values to all 'print_formats'.
            // The value will be set only for allowed components.
            type: 'print_formats',
            componentType: 'calculationVariables',
            value: values
        }]);
    }

    /**
     * Validates the given values.
     * Returns errors of the validation.
     *
     * @param {object} values A current state
     * @return {object[]}
     */
    validate(values) {
        if (
            (Number.isNaN(values.width) || typeof values.width === 'string' && values.width.trim() === '') &&
            (Number.isNaN(values.height) || typeof values.height === 'string' && values.height.trim() === '')
        ) {
            return [({
                type: VALUES_ARE_NAN_ERROR,
                source: BOTH_SOURCE,
            })];
        }

        const errors = [];

        if (Number.isNaN(values.width)) {
            errors.push({
                type: WIDTH_IS_NAN_ERROR,
                source: WIDTH_SOURCE,
            });
        } else if (this.minWidth > values.width) {
            errors.push({
                type: WIDTH_LESS_THAN_MIN_ERROR,
                source: WIDTH_SOURCE,
            });
        } else if (this.maxWidth < values.width) {
            errors.push({
                type: WIDTH_GREATER_THAN_MAX_ERROR,
                source: WIDTH_SOURCE,
            });
        }

        if (Number.isNaN(values.height)) {
            errors.push({
                type: HEIGHT_IS_NAN_ERROR,
                source: HEIGHT_SOURCE,
            });
        } else if (this.minHeight > values.height) {
            errors.push({
                type: HEIGHT_LESS_THAN_MIN_ERROR,
                source: HEIGHT_SOURCE,
            });
        } else if (this.maxHeight < values.height) {
            errors.push({
                type: HEIGHT_GREATER_THAN_MAX_ERROR,
                source: HEIGHT_SOURCE,
            });
        }

        return errors;
    }

    /**
     * Returns errors by the given source.
     *
     * @param {string} source
     * @return {object[]}
     */
    getErrorsBySource(source) {
        return this.props.errors
            .filter(error => error.source === source || error.source === BOTH_SOURCE)
            .map(error => error.type);
    }

    /**
     * Returns an error type by the given source.
     *
     * @param {string} source
     * @return {?string}
     */
    getErrorType(source) {
        const errors = this.getErrorsBySource(source);

        if (!errors.length) {
            return null;
        }

        if (
            errors.includes(VALUES_ARE_NAN_ERROR) ||
            errors.includes(HEIGHT_IS_NAN_ERROR) ||
            errors.includes(WIDTH_IS_NAN_ERROR)
        ) {
            return NAN_ERROR;
        }

        if (errors.includes(HEIGHT_LESS_THAN_MIN_ERROR) || errors.includes(WIDTH_LESS_THAN_MIN_ERROR)) {
            return MIN_ERROR;
        }

        if (errors.includes(HEIGHT_GREATER_THAN_MAX_ERROR) || errors.includes(WIDTH_GREATER_THAN_MAX_ERROR)) {
            return MAX_ERROR;
        }

        return UNKNOWN_ERROR;
    }

    /**
     * Returns a message by the given type.
     *
     * @param {string} type
     * @return {string}
     */
    getMessageByType(type) {
        switch (type) {
            case VALUES_ARE_NAN_ERROR:
                return 'Значения не заданы.';
            case HEIGHT_IS_NAN_ERROR:
                return 'Не задана высота.';
            case WIDTH_IS_NAN_ERROR:
                return 'Не задана ширина.';
            case HEIGHT_LESS_THAN_MIN_ERROR:
                return 'Высота меньше чем минимальное значение.';
            case WIDTH_LESS_THAN_MIN_ERROR:
                return 'Ширина меньше чем минимальное значение.';
            case HEIGHT_GREATER_THAN_MAX_ERROR:
                return 'Высота больше чем максимальное значение.';
            case WIDTH_GREATER_THAN_MAX_ERROR:
                return 'Ширина больше чем максимальное значение.';
        }

        return 'Неопределенная ошибка.';
    }

    render() {
        return (
            <div className="mb-4">
                <div className="row">
                    <div className="col-xl-4 col-sm-12 col-xs-12">
                        <label className="col-form-label">{this.label}</label>
                    </div>
                    <div className="col-xl-8 col-sm-12 col-xs-12">
                        <CalculatorSelect
                            placeholder="Выберите формат"
                            options={this.options}
                            name={"calculationVariable[" + this.props.name + "][format]"}
                            value={this.props.value.selectedFormat}
                            onChange={this.handleSelectSizeChange} />
                    </div>
                    <div className="col-md-2"/>
                </div>
                <div className="row mt-4">
                    <div className="col-xl-8 ml-xl-auto d-flex col-sm-12 col-xs-12">
                        <SizeOfPrintFormat
                            label="Ширина"
                            name={"calculationVariables[" + this.props.name + "][width]"}
                            id={"calculationVariables[" + this.props.name + "][width]"}
                            unit="мм"
                            value={this.props.value.width}
                            error={this.getErrorType(WIDTH_SOURCE)}
                            min={this.minWidth}
                            max={this.maxWidth}
                            readOnly={this.props.rules.fixed_print_formats}
                            visibleLimitation={this.visibleLimitationOfWidth}
                            className="col-md-6"
                            onChange={this.handleInputSizeChange} />
                        <SizeOfPrintFormat
                            label="Длина"
                            name={"calculationVariables[" + this.props.name + "][height]"}
                            id={"calculationVariables[" + this.props.name + "][height]"}
                            value={this.props.value.height}
                            error={this.getErrorType(HEIGHT_SOURCE)}
                            unit="мм"
                            min={this.minHeight}
                            max={this.maxHeight}
                            readOnly={this.props.rules.fixed_print_formats}
                            visibleLimitation={this.visibleLimitationOfHeight}
                            className="col-md-6"
                            onChange={this.handleInputSizeChange} />
                    </div>
                    {this.props.errors.length > 0 &&
                        <div className="col-xl-8 ml-xl-auto d-flex col-sm-12 col-xs-12 mt-2 text-danger">
                            <p>Обнаружены ошибки:</p>
                            <ul className="mb-0">
                                {this.props.errors.map(error => (
                                    <li key={error.type} className="mt-1">
                                        {this.getMessageByType(error.type)}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    }
                </div>
            </div>
        );
    }
}

PrintFormats.propTypes = {
    config: PropTypes.object,
    value: PropTypes.object,
    errors: PropTypes.array,
    onChange: PropTypes.func,
};

PrintFormats.defaultProps = {
    config: {},
    value: {
        width: '',
        height: '',
        selectedFormat: null,
    },
    errors: [],
    /**
     * A callback to assign a new value.
     *
     * @param {string} name
     * @param {object} value A new value of the component
     * @param {array} errors
     */
    onChange: (name, value, errors) => {},
};
