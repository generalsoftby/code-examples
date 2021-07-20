import React from 'react';
import PropTypes from 'prop-types';
import { isEqual } from "lodash";
import CheckboxesWithSides from './CheckboxesWithSides';

const SIDES_ARE_NOT_SELECTED_ERROR = 'SIDES_ARE_NOT_SELECTED_ERROR';

export default class Sides extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            active: false,
            sides: {
                left: false,
                right: false,
                top: false,
                bottom: false,
            },
        };

        this.handleChangeActive = this.handleChangeActive.bind(this);
        this.handleSideChange = this.handleSideChange.bind(this);
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
        } else if (!isEqual(this.props.value.sides, nextProps.value.sides)) {
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
     * Handles a change of sides.
     *
     * @param {object} sides
     */
    handleSideChange(sides) {
        this.setState({
            sides,
        }, this.changeValue);
    }

    /**
     * Normalizes values, checks values and calls the callback.
     */
    changeValue() {
        this.props.onChange(
            this.props.name,
            this.state,
            this.validate(this.state.active, this.state.sides)
        );
    }

    /**
     * Validates the given values.
     * Returns errors of the validation.
     *
     * @param {boolean} active
     * @param {object} sides
     * @return {object[]}
     */
    validate(active, sides) {
        const errors = [];

        if (!active) {
            return errors;
        }

        if (!(sides.left || sides.right || sides.top || sides.bottom)) {
            errors.push({
                type: SIDES_ARE_NOT_SELECTED_ERROR,
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
            case SIDES_ARE_NOT_SELECTED_ERROR:
                return 'Ни одна сторона не выбрана.';
        }

        return 'Неопределенная ошибка.';
    }

    render() {
        return (
            <div className="mb-4">
                <div className="row">
                    <div className="col-xl-4 col-sm-12 col-xs-12">
                        <label className="col-form-label">{this.props.label}</label>
                    </div>

                    <div className="col-xl-8 col-sm-12 col-xs-12">
                        <div className={"custom-control custom-checkbox"}>
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={this.props.id}
                                name={"calculationVariable[" + this.props.name + "][active]"}
                                onChange={this.handleChangeActive}
                                checked={this.props.value.active} />
                            <label className="custom-control-label" htmlFor={this.props.id} />
                            <label htmlFor={this.props.id} className="mb-1">
                                Используется
                            </label>
                        </div>

                        {this.props.value.active &&
                            <div className="mt-2 w-75">
                                <CheckboxesWithSides
                                    id={this.props.id}
                                    name={this.props.name}
                                    sides={this.props.value.sides}
                                    onChange={this.handleSideChange} />
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

Sides.propTypes = {
    value: PropTypes.object,
    errors: PropTypes.array,
    onChange: PropTypes.func,
};

Sides.defaultProps = {
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
