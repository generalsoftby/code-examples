import React from 'react';
import Select from "./Select";
import RadioCheckbox from "./RadioCheckbox";
import SelectWithInput from './SelectWithInput';
import { isEqual } from "lodash";

/**
 * Connects a type of option with its component.
 */
export default class Option extends React.Component {
    constructor(props) {
        super(props);

        this.handleChange = this.handleChange.bind(this);
    }

    /**
     * Calls the callback and passes IDs of selected values or an object with values.
     *
     * @param {array|number[]|object} selectedValues
     */
    handleChange(selectedValues) {
        this.props.onChange(this.props.optionId, selectedValues);
    }

    /**
     * Optimizes rendering components. Compares states of visibility,
     * validation and selected values.
     *
     * @param {object} nextProps
     * @return {boolean}
     */
    shouldComponentUpdate(nextProps) {
        if (! isEqual(this.props.visibility, nextProps.visibility)) {
            return true;
        }

        if (! this.arrayEquals(this.props.selectedValues, nextProps.selectedValues)) {
            return true;
        }

        if (! isEqual(this.props.validation, nextProps.validation)) {
            return true;
        }

        return false;
    }

    /**
     * Compares two arrays. Returns true if they equals.
     *
     * @param {array} a
     * @param {array} b
     * @return {boolean}
     */
    arrayEquals(a, b) {
        return Array.isArray(a)
            && Array.isArray(b)
            && a.length === b.length
            && a.every((val, index) => val === b[index])
        ;
    }

    render() {
        // NOTE: Adds here your new type of an option
        switch (this.props.type) {
            case 'radio_checkbox':
                return <RadioCheckbox {...this.props}
                    onChange={this.handleChange} />
                ;
            case 'select_with_text':
                return <SelectWithInput {...this.props}
                    onChange={this.handleChange} />
                ;
            case 'select':
            default:
                return <Select {...this.props}
                    onChange={this.handleChange} />
                ;
        }
    }
}

Option.defaultProps = {
    /**
     * The callback of changing values.
     *
     * @param {number} optionId An option ID
     * @param {array|number[]|object} values New values
     */
    onChange: (optionId, values) => {},
};
