import React from 'react';
import PropTypes from 'prop-types';
import { isEqual } from 'lodash';

const VALUE_IS_NAN_ERROR = 'VALUE_IS_NAN_ERROR';
const VALUE_LESS_THAN_MIN_ERROR = 'VALUE_LESS_THAN_MIN_ERROR';
const VALUE_GREATER_THAN_MAX_ERROR = 'VALUE_GREATER_THAN_MAX_ERROR';

export default class NumberOfProducts extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            value: this.props.rules.default,
        };

        this.handleChange = this.handleChange.bind(this);
        this.changeValue = this.changeValue.bind(this);
    }

    /**
     * Initializes data of the component.
     */
    componentDidMount() {
        this.initLabelAndPlaceholder();
        this.initRules();

        // Sends initialized values of the component to the callback
        this.changeValue();
    }

    /**
     * Optimizes rendering components. Compares the value and errors.
     *
     * @param {object} nextProps
     * @return {boolean}
     */
    shouldComponentUpdate(nextProps) {
        if (this.props.value !== nextProps.value) {
            return true;
        } else if (!isEqual(this.props.errors, nextProps.errors)) {
            return true;
        }

        return false;
    }

    /**
     * Initializes the label and the placeholder.
     */
    initLabelAndPlaceholder() {
        this.label = typeof this.props.config.label !== 'undefined'
            ? this.props.config.label
            : this.props.label
        ;
        this.placeholder = this.props.placeholder;
    }

    /**
     * Initializes rules to validate.
     */
    initRules() {
        switch (this.props.type) {
            case 'standard':
                this.minValue = this.props.rules.min;
                this.maxValue = this.props.rules.max ? this.props.rules.max : Infinity;
                this.step = this.props.rules.step;
                break;

            default:
                break;
        }
    }

    /**
     * Handles a change of the input.
     *
     * @param {object} event
     */
    handleChange(event) {
        this.setState({
            value: Number.parseInt(event.target.value),
        }, this.changeValue);
    }

    /**
     * Normalizes values, checks values and calls the callback.
     */
    changeValue() {
        this.props.onChange(
            this.props.name,
            this.state.value,
            this.validate(this.state.value)
        );
    }

    /**
     * Validates the given value.
     * Returns errors of the validation.
     *
     * @param {?number} value
     * @return {object[]}
     */
    validate(value) {
        const errors = [];

        if (Number.isNaN(value) || value === null) {
            errors.push({
                type: VALUE_IS_NAN_ERROR,
            });
        } else if (this.minValue > value) {
            errors.push({
                type: VALUE_LESS_THAN_MIN_ERROR,
            });
        } else if (this.maxValue < value) {
            errors.push({
                type: VALUE_GREATER_THAN_MAX_ERROR,
            });
        }

        return errors;
    }

    /**
     * Returns a message by the given type.
     *
     * @param {string} type
     * @return {string}
     */
    getMessageByType(type) {
        switch (type) {
            case VALUE_IS_NAN_ERROR:
                return 'Значение не задано.';
            case VALUE_LESS_THAN_MIN_ERROR:
                return 'Значение меньше минимального значения: ' + this.minValue + '.';
            case VALUE_GREATER_THAN_MAX_ERROR:
                return 'Значение больше максимального значения: ' + this.maxValue + '.';
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
                        <input
                            type="number"
                            className={"form-control col-xl-4" + (this.props.errors.length ? " has-format-error" : "")}
                            placeholder={this.placeholder}
                            name={"calculationVariable[" + this.props.name + "][value]"}
                            value={this.props.value}
                            min={this.minValue}
                            max={this.maxValue}
                            step={this.step}
                            onChange={this.handleChange} />

                        {this.props.errors.length > 0 &&
                            <div className="mt-2 text-danger">
                                {this.getMessageByType(this.props.errors[0].type)}
                            </div>
                        }
                    </div>
                </div>
            </div>
        );
    }
}

NumberOfProducts.propTypes = {
    config: PropTypes.object,
    value: PropTypes.oneOfType([
        PropTypes.number,
        PropTypes.oneOf([''])
    ]),
    errors: PropTypes.array,
    onChange: PropTypes.func,
};

NumberOfProducts.defaultProps = {
    config: {},
    value: '',
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
