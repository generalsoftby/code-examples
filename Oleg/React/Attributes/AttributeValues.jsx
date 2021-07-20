import React from 'react';
import Select from 'react-select';

/**
 * Shows attribute values of a calculator.
 */
export default class AttributeValues extends React.Component {
    constructor (props) {
        super(props);

        this.prepareOptions();
    }

    /**
     * Prepares options for the 'select' component
     */
    prepareOptions() {
        this.options = this.props.values.map(attributeValue => ({
            value: attributeValue.id,
            label: attributeValue.value
        }));
    }

    /**
     * Finds and returns an option in the array with options by the given value.
     *
     * @param {number} value An ID of a value.
     * @return {object|null}
     */
    getOptionByValue(value) {
        let option = null;

        if (typeof value !== 'undefined') {
            option = this.options.find(element => element.value === value);
        }

        return option;
    }

    /**
     * Finds and returns an option by the given value.
     *
     * @param {number} value An ID of a value.
     * @param {array|object[]} visibleOptions
     * @return {object|null}
     */
    findOption(value, visibleOptions) {
        let option = null;

        if (typeof value !== 'undefined') {
            option = visibleOptions.find(element => element.value === value);
        }

        return option;
    }

    /**
     * Filters options and returns available options.
     *
     * @return {array|undefined}
     */
    filterOptions() {
        return this.options && this.options.filter(option =>
            this.props.visibilityOfValues.hasOwnProperty(option.value) &&
            this.props.visibilityOfValues[option.value]
        );
    }

    /**
     * Disables options and returns active and inactive options.
     *
     * @return {array|undefined}
     */
    disableOptions() {
        return this.options && this.options.map(option => {
            if (this.props.visibilityOfValues.hasOwnProperty(option.value) &&
            this.props.visibilityOfValues[option.value]) {
                return option;
            }

            return {
                ...option,
                isDisabled: true,
            };
        });
    }

    render() {
        /** @var {array|object[]} visibleOptions */
        const visibleOptions = this.props.typeOfHidding === 'filter' ? this.filterOptions() : this.disableOptions();

        return (
            <Select
                id={this.props.id}
                options={visibleOptions}
                isDisabled={this.props.isDisabled}
                value={this.findOption(this.props.selectedValue, visibleOptions)}
                placeholder="Выберите атрибут"
                isClearable={this.props.isClearable}
                onChange={this.props.onValueChange} />
        );
    }
}

AttributeValues.defaultProps = {
    /**
     * The type of hidding options:
     * - filter: hides inactive values;
     * - disable: disables inactive values but they are visible.
     */
    typeOfHidding: 'filter',
    isDisabled: false,
};
