import React from 'react';
import AttributeValues from './AttributeValues';

/**
 * Shows an attribute of the calculator.
 */
export default class Attribute extends React.Component {
    constructor(props) {
        super(props);

        this.handleChangeOfAttributeValue = this.handleChangeOfAttributeValue.bind(this);
    }

    /**
     * Calls the callback of changing attribute values.
     *
     * @param {object|null} option An object of selected option. Contains a value and a label.
     */
    handleChangeOfAttributeValue(option) {
        if (option) {
            this.props.onAttributeChange(this.props.id, option.value);
        } else {
            this.props.onAttributeChange(this.props.id, null);
        }
    }

    render() {
        if (! this.props.values.length) {
            return null;
        }

        const attributeId = 'attribute' + this.props.id;

        return (
            <div className="form-group row align-items-center">
                <div className="field__label col-xl-4 col-sm-12 col-xs-12">
                    <label className="col-form-label" htmlFor={attributeId}>
                        {this.props.title}
                    </label>
                </div>
                <div className="field__select col-xl-8 col-sm-10 col-xs-10">
                    <AttributeValues
                        id={attributeId}
                        isClearable={this.props.isClearable}
                        isDisabled={this.props.isDisabled}
                        typeOfHidding={this.props.typeOfHidding}
                        values={this.props.values}
                        visibilityOfValues={this.props.visibilityOfValues}
                        selectedValue={this.props.selectedValue}
                        onValueChange={this.handleChangeOfAttributeValue} />
                </div>
            </div>
        );
    }
}

Attribute.defaultProps = {
    /**
     * The callback of changing attribute values.
     * Sets an attribute ID and a new value.
     *
     * @param {number} attributeId
     * @param {number} value
     */
    onAttributeChange: (attributeId, value) => {},
};
