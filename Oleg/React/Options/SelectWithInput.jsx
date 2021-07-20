import React from 'react';
import ReactSelect from 'react-select';
import getMessageByType from './messages';
import { VALUES_WAS_NOT_ENTERED, INCORRECT_RANGE_VALUE } from '../../services/options/validation';

/**
 * Shows an option with its values and an input field.
 */
export default class SelectWithInput extends React.Component {
    constructor(props) {
        super(props);

        const selectedValues = {
            // Keeps values of the select
            values: [],
            // Keeps a value of the input
            value: '',
        };

        // Initializes user data of the component. They are practically undefined
        // and used default values.
        if (typeof this.props.selectedValues !== 'undefined') {
            if (Array.isArray(this.props.selectedValues)) {
                selectedValues.values =  this.props.selectedValues;
            } else if (typeof this.props.selectedValues.values !== 'undefined') {
                selectedValues.values = this.props.selectedValues.values;
            }
        }

        this.state = {
            ...selectedValues,
            valuesWasInitialized: false,
        };

        this.getVisibleOptions = this.getVisibleOptions.bind(this);
        this.isVisible = this.isVisible.bind(this);
        this.handleChangeValue = this.handleChangeValue.bind(this);
    }

    /**
     * Prepares options for the 'select' component.
     */
    componentDidMount() {
        this.options = this.props.values.map(optionValue => ({
            value: optionValue.id,
            label: optionValue.title
        }));

        this.rangeLabel = this.props.config.label ? this.props.config.label : 'Значение';
        this.rangeText = 'от ';
        this.rangeText += this.props.config.min ? this.props.config.min : 1;

        if (this.props.config.max !== null) {
            this.rangeText += ' до ' + this.props.config.max;
        }
    }

    /**
     * Sets default values to the state after the first render.
     *
     * @param {object} prevProps
     * @param {object} prevState
     */
    componentDidUpdate(prevProps, prevState) {
        if (!prevState.valuesWasInitialized) {
            this.setState({
                ...this.props.selectedValues,
                valuesWasInitialized: true,
            }, this.callCallback);
        }
    }

    /**
     * Returns visible options (options for react-select).
     *
     * @return {array|object[]}
     */
    getVisibleOptions() {
        if (! Array.isArray(this.options)) {
            return [];
        }

        return this.options.filter(option => this.isVisible(option.value));
    }

    /**
     * Checks whether an value by the given ID is visible.
     *
     * @param {number} id An ID of option.
     * @return {boolean}
     */
    isVisible(id) {
        return this.props.visibility.values[id].visible === true;
    }

    /**
     * Gets options of react-select from the given options by the given selected IDs of values.
     *
     * @param {array|object[]} sourceOptions An array with options of react-select.
     * @param {array|number[]} values An array with IDs of values.
     * @returns {array|object[]}
     */
    getOptionsBySelectedValues(sourceOptions, values) {
        if (Array.isArray(values) && typeof sourceOptions !== 'undefined') {
            return sourceOptions.filter(element =>
                values.indexOf(element.value) !== -1
            );
        }

        return [];
    }

    /**
     * Changes a component state by a new value.
     *
     * @param {object|array} anyValue A new value
     */
    handleChangeValue(anyValue) {
        // WARNING: Sets props, because there needs to save the current values
        // of other fields: the Input or the Select.
        if (Array.isArray(anyValue)) {
            // For the select that have many values
            this.setState({
                values: anyValue.map(option => option.value),
                value: this.props.selectedValues.value,
            }, this.callCallback);
        } else if (typeof anyValue === 'object') {
            if (typeof anyValue.currentTarget === 'object') {
                // For the input of range
                const value = anyValue.currentTarget.value.trim()
                    ? Number.parseInt(anyValue.currentTarget.value)
                    : null
                ;

                this.setState({
                    value,
                    values: this.props.selectedValues.values,
                }, this.callCallback);
            } else {
                // For the select that have one value
                this.setState({
                    values: [anyValue.value],
                    value: this.props.selectedValues.value,
                }, this.callCallback);
            }
        }
    }

    /**
     * Calls the callback and passes IDs of selected values and an entered value.
     */
    callCallback() {
        this.props.onChange({
            values: this.state.values,
            value: this.state.value,
        });
    }

    render() {
        if (! this.props.visibility.visible) {
            return null;
        }

        const options = this.getVisibleOptions();
        /** @var {array|object[]} values An array with selected options by IDs of values */
        const values = this.getOptionsBySelectedValues(options, this.props.selectedValues.values);
        const message = this.props.validation.error
            ? getMessageByType(this.props.validation.type, this.props.validation.params)
            : null
        ;

        let errorOfSelect = false;
        let errorOfInput = false;

        if (message) {
            if (
                this.props.validation.type === VALUES_WAS_NOT_ENTERED
                || this.props.validation.type === INCORRECT_RANGE_VALUE
            ) {
                errorOfInput = true;
            } else {
                errorOfSelect = true;
            }
        }

        return (
            <React.Fragment>
                <div className="form-group row align-items-center">
                    <div className="field__label col-xl-4 col-sm-12 col-xs-12">
                        <label htmlFor={this.props.id} className="col-form-label">
                            {this.props.label}
                            {this.props.required && <span className="text-danger pl-1">*</span>}
                        </label>
                    </div>
                    <div className="field__select col-xl-8 col-sm-10 col-xs-10">
                        <ReactSelect
                            id={this.props.id}
                            options={options}
                            className={errorOfSelect ? 'has-error' : ''}
                            isMulti={this.props.multiple}
                            value={values}
                            placeholder="Выберите значение опции"
                            noOptionsMessage={() => "Нет опций"}
                            onChange={this.handleChangeValue} />
                        {errorOfSelect && message && <div className="mt-2 text-danger">{message}</div>}
                    </div>
                </div>
                <div className="form-group row align-items-center">
                    <div className="field__label col-xl-4 col-sm-12 col-xs-12">
                        <label htmlFor={this.props.id + 'Range'} className="col-form-label">
                            {this.rangeLabel}
                        </label>
                    </div>
                    <div className="field__select col-xl-8 col-sm-10 col-xs-10">
                        <div className="mt-3">
                            <input
                                id={this.props.id + 'Range'}
                                type="number"
                                min={this.props.config.min}
                                max={this.props.config.max}
                                value={this.props.selectedValues.value}
                                className={'form-control col-xl-6 ' + (errorOfInput ? 'has-error' : '')}
                                placeholder={"Введите значение " + this.rangeText}
                                onChange={this.handleChangeValue} />
                        </div>
                        {errorOfInput && message && <div className="mt-2 text-danger">{message}</div>}
                    </div>
                </div>
            </React.Fragment>
        );
    }
}

SelectWithInput.defaultProps = {
    selectedValues: {
        values: [],
        value: '',
    },
};
