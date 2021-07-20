import React from 'react';
import PropTypes from 'prop-types';
import { isEqual } from "lodash";

const VALUE_IS_NAN_ERROR = 'VALUE_IS_NAN_ERROR';
const VALUE_LESS_THAN_ONE_ERROR = 'VALUE_LESS_THAN_ONE_ERROR';

export default class Gluing extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            active: false,
            length: 1,
        };

        this.handleChangeActive = this.handleChangeActive.bind(this);
        this.handleChangeLength = this.handleChangeLength.bind(this);
        this.changeValue = this.changeValue.bind(this);
    }

    /**
     * Initializes data of the component. Sends initialized values
     * of the component to the callback.
     */
    componentDidMount() {
        this.changeValue();
    }

    /**
     * Optimizes rendering components. Compares the value and errors.
     *
     * @param {object} nextProps
     * @return {boolean}
     */
    shouldComponentUpdate(nextProps) {
        if (this.props.value.active !== nextProps.value.active) {
            return true;
        } else if (this.props.value.length !== nextProps.value.length) {
            return true;
        } else if (!isEqual(this.props.errors, nextProps.errors)) {
            return true;
        }

        return false;
    }

    /**
     * Handles a change of the activity.
     */
    handleChangeActive() {
        this.setState({
            active: !this.state.active,
        }, this.changeValue);
    }

    /**
     * Handles a change of the input with a length of gluing.
     *
     * @param {object} event
     */
    handleChangeLength(event) {
        this.setState({
            length: Number.parseInt(event.target.value),
        }, this.changeValue);
    }

    /**
     * Normalizes values, checks values and calls the callback.
     */
    changeValue() {
        this.props.onChange(
            this.props.name,
            this.state,
            this.validate(this.state.active, this.state.length)
        );
    }

    /**
     * Validates the given values.
     * Returns errors of the validation.
     *
     * @param {boolean} active
     * @param {?number} length
     * @return {object[]}
     */
    validate(active, length) {
        const errors = [];

        if (!active) {
            return errors;
        }

        if (Number.isNaN(length) || length === null) {
            errors.push({
                type: VALUE_IS_NAN_ERROR,
            });
        } else if (1 > length) {
            errors.push({
                type: VALUE_LESS_THAN_ONE_ERROR,
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
            case VALUE_LESS_THAN_ONE_ERROR:
                return 'Значение меньше 1.';
        }

        return 'Неопределенная ошибка.';
    }

    render() {
        const id = "gluing" + this.props.name;

        return (
            <div className="mb-4">
                <div className="row">
                    <div className="col-xl-4 col-sm-12 col-xs-12">
                        <label className="col-form-label">Склейка частей</label>
                    </div>

                    <div className="col-xl-8 col-sm-12 col-xs-12">
                        <div className={"custom-control custom-checkbox"}>
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={id}
                                name={"calculationVariable[" + this.props.name + "][active]"}
                                onChange={this.handleChangeActive}
                                checked={this.props.value.active} />
                            <label className="custom-control-label" htmlFor={id} />
                            <label htmlFor={id} className="mb-1">
                                Используется
                            </label>
                        </div>

                        {this.props.value.active &&
                            <div className="mt-2">
                                <input
                                    type="number"
                                    className={"form-control col-xl-4" + (this.props.errors.length ? " has-format-error" : "")}
                                    placeholder="Длина склейки"
                                    name={"calculationVariable[" + this.props.name + "][length]"}
                                    value={this.props.value.length}
                                    onChange={this.handleChangeLength} />
                                {this.props.errors.length > 0 &&
                                    <div className="mt-2 text-danger">
                                        {this.getMessageByType(this.props.errors[0].type)}
                                    </div>
                                }
                            </div>
                        }
                    </div>
                </div>
            </div>
        );
    }
}

Gluing.propTypes = {
    active: PropTypes.bool,
    value: PropTypes.object,
    errors: PropTypes.array,
    onChange: PropTypes.func,
};

Gluing.defaultProps = {
    config: {},
    value: {},
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
