import React from 'react';
import ReactSelect from 'react-select';
import getMessageByType from './messages';

/**
 * Shows an option with its values.
 */
export default class Select extends React.Component {
    constructor(props) {
        super(props);

        this.getVisibleOptions = this.getVisibleOptions.bind(this);
        this.isVisible = this.isVisible.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    /**
     * Prepares options for the 'select' component.
     */
    componentDidMount() {
        this.options = this.props.values.map(optionValue => ({
            value: optionValue.id,
            label: optionValue.title
        }));
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
        let options = [];

        if (Array.isArray(values) && typeof sourceOptions !== 'undefined') {
            options = sourceOptions.filter(element =>
                values.indexOf(element.value) !== -1
            );
        }

        return options;
    }

    /**
     * Handles changing of option values and pulls IDs of these values.
     * Calls the callback and passes IDs of selected values.
     *
     * @param {array|object} newValues
     */
    handleChange(newValues) {
        let values = [];

        if (Array.isArray(newValues)) {
            values = newValues.map(option => option.value);
        } else if (newValues !== null && typeof newValues === 'object') {
            values.push(newValues.value);
        }

        this.props.onChange(values);
    }

    render() {
        if (! this.props.visibility.visible) {
            return null;
        }

        const options = this.getVisibleOptions();
        const values = this.getOptionsBySelectedValues(options, this.props.selectedValues);
        const message = this.props.validation.error
            ? getMessageByType(this.props.validation.type, this.props.validation.params)
            : null
        ;

        return (
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
                        className={this.props.validation.error ? 'has-error' : ''}
                        isMulti={this.props.multiple}
                        isClearable={!this.props.required}
                        value={values}
                        placeholder="Выберите значение опции"
                        noOptionsMessage={{inputValue: "Нет опций"}}
                        onChange={this.handleChange} />
                    {message && <div className="mt-2 text-danger">{message}</div>}
                </div>
            </div>
        );
    }
}
