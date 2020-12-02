import React from 'react';
import Attribute from './Attribute';
import { getObjectWithAttributesWithDefaultValues } from "../../services/attributes";

/**
 * Shows attributes of a group of the calculator.
 */
export default class Index extends React.Component {
    constructor(props) {
        super(props);

        this.handleChangeOfAttribute = this.handleChangeOfAttribute.bind(this);
    }

    /**
     * Defines default values and sets their to the callback.
     */
    componentDidMount() {
        this.props.onAttributesChange(
            getObjectWithAttributesWithDefaultValues(this.props.attributes)
        );
    }

    /**
     * Calls the callback of changing attribute values.
     * It uses an attribute ID and a new selected value.
     * Passes to the callback an object with the new value and other selected values.
     *
     * @param {number} attributeId An attribute ID.
     * @param {number} value A new value of a selected element.
     */
    handleChangeOfAttribute(attributeId, value) {
        /** @var {object} selectedAttributes An object with selected attributes */
        const { selectedAttributes } = this.props;

        selectedAttributes[attributeId] = value;

        this.props.onAttributesChange(selectedAttributes);
    }

    /**
     * Returns a message by the given error type.
     *
     * @param {string} type
     * @param {object} params
     * @return {string}
     */
    getErrorMessage(type, params) {
        switch (type) {
            case 'ATTRIBUTES_ARE_NOT_SELECTED':
                return 'Указаные не все атрибуты: ' + params.numberOfSelectedAttributes + ' из ' + params.length + ' .';

            default:
                return 'Обнаружена ошибка в атрибутах.';
        }
    }

    render () {
        if (! this.props.attributes.length) {
            return null;
        }

        return (
            <div>
                {this.props.attributes.map((attribute, index) => {
                    const visibility = this.props.visibility.hasOwnProperty(attribute.id)
                        ? this.props.visibility[attribute.id].visible
                        : false
                    ;
                    const isDisabled = !visibility;

                    if (isDisabled && this.props.typeOfHidding !== 'disable') {
                        return null;
                    }

                    // All attributes are clearable except the first attribute
                    const clearable = index !== 0;

                    return <Attribute
                        key={attribute.id}
                        title={attribute.title}
                        id={attribute.id}
                        isClearable={clearable}
                        typeOfHidding={this.props.typeOfHidding}
                        isDisabled={isDisabled}
                        values={attribute.attribute_values}
                        visibilityOfValues={this.props.visibility[attribute.id].values}
                        selectedValue={this.props.selectedAttributes[attribute.id]}
                        onAttributeChange={this.handleChangeOfAttribute} />;
                })}

                {this.props.errors.length > 0 &&
                    <div className="mb-3 row justify-content-end">
                        <div className="align-self-end col-xl-8 col-sm-10 col-xs-10">
                            {this.props.errors.map((error, index) => (
                                <div
                                    className={(index !== 0 ? "mt-2 " : "") + "text-danger"}
                                    key={error.type}>
                                    {this.getErrorMessage(error.type, error.params)}
                                </div>
                            ))}
                        </div>
                    </div>
                }
            </div>
        );
    }
}

Index.displayName = 'Attributes';

Index.defaultProps = {
    attributes: [],
    typeOfHidding: 'filter',
};
