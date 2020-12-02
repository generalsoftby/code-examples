import React from 'react';
import PropTypes from 'prop-types';
import { isEqual } from "lodash";
import CheckboxesWithSides from './CheckboxesWithSides';
import CalculatorSelect from '../Common/CalculatorSelect';
import CheckboxesWithAngles from './CheckboxesWithAngles';

const TYPE_IS_UNDEFINED = 'TYPE_IS_UNDEFINED';
const STEP_IS_UNDEFINED = 'STEP_IS_UNDEFINED';
const VALUE_LESS_THAN_ONE_ERROR = 'VALUE_LESS_THAN_ONE_ERROR';
const SIDES_ARE_NOT_SELECTED_ERROR = 'SIDES_ARE_NOT_SELECTED_ERROR';
const ANGLES_ARE_NOT_SELECTED_ERROR = 'ANGLES_ARE_NOT_SELECTED_ERROR';

const SIDE_TYPE = 'side';
const ANGLE_TYPE = 'angle';

const TYPES = [
    { value: SIDE_TYPE, label: 'По периметру' },
    { value: ANGLE_TYPE, label: 'В углы' },
];

export default class Eyelets extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            active: false,
            type: null,
            step: 1,
            angles: {
                leftTop: false,
                leftBottom: false,
                rightTop: false,
                rightBottom: false,
            },
            sides: {
                left: false,
                right: false,
                top: false,
                bottom: false,
            },
        };

        this.handleChangeActive = this.handleChangeActive.bind(this);
        this.handleTypeChange = this.handleTypeChange.bind(this);
        this.handleStepChange = this.handleStepChange.bind(this);
        this.handleSideChange = this.handleSideChange.bind(this);
        this.handleAngleChange = this.handleAngleChange.bind(this);
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
        } else if (this.props.value.type !== nextProps.value.type) {
            return true;
        } else if (this.props.value.step !== nextProps.value.step) {
            return true;
        } else if (!isEqual(this.props.value.sides, nextProps.value.sides)) {
            return true;
        } else if (!isEqual(this.props.value.angles, nextProps.value.angles)) {
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
     * Handles a change of the type.
     */
    handleTypeChange(option) {
        this.setState({
            type: option ? option.value : null,
        }, this.changeValue);
    }

    /**
     * Handles a change of the step.
     *
     * @param {object} e
     */
    handleStepChange(e) {
        const value = Number.parseInt(e.target.value);
        console.log('step', value);

        this.setState({
            step: !Number.isNaN(value) ? value : null,
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
     * Handles a change of angles.
     *
     * @param {object} angles
     */
    handleAngleChange(angles) {
        console.log('a', angles);
        this.setState({
            angles,
        }, this.changeValue);
    }

    /**
     * Normalizes values, checks values and calls the callback.
     */
    changeValue() {
        this.props.onChange(
            this.props.name,
            this.state,
            this.validate(
                this.state.active,
                this.state.type,
                this.state.step,
                this.state.sides,
                this.state.angles
            )
        );
    }

    /**
     * Validates the given values.
     * Returns errors of the validation.
     *
     * @param {boolean} active
     * @param {?string} type A position of eyelets.
     * @param {?number} step
     * @param {object} sides
     * @param {object} angles
     * @return {object[]}
     */
    validate(active, type, step, sides, angles) {
        const errors = [];

        if (!active) {
            return errors;
        }

        switch (type) {
            case SIDE_TYPE:
                errors.push(...this.validateSides(step, sides));
                break;

            case ANGLE_TYPE:
                errors.push(...this.validateAngles(angles));
                break;

            default:
                errors.push({
                    type: TYPE_IS_UNDEFINED,
                });
        }

        return errors;
    }

    /**
     * Validates the given step and sides.
     *
     * @param {?number} step
     * @param {object} sides
     * @return {object[]}
     */
    validateSides(step, sides) {
        const errors = [];

        if (step === null) {
            errors.push({
                type: STEP_IS_UNDEFINED,
            });
        } else if (step < 1) {
            errors.push({
                type: VALUE_LESS_THAN_ONE_ERROR,
            });
        }

        if (!(sides.left || sides.right || sides.top || sides.bottom)) {
            errors.push({
                type: SIDES_ARE_NOT_SELECTED_ERROR,
            });
        }

        return errors;
    }

    /**
     * Validates the given angles.
     *
     * @param {object} angles
     * @return {object[]}
     */
    validateAngles(angles) {
        return (!(angles.leftTop || angles.rightTop || angles.leftBottom || angles.rightBottom))
            ? [{type: ANGLES_ARE_NOT_SELECTED_ERROR}]
            : []
        ;
    }

    /**
     * Returns a message by the given type.
     *
     * @param {string} type
     * @return {string}
     */
    getMessageByType(type) {
        switch (type) {
            case TYPE_IS_UNDEFINED:
                return 'Способ установки не задан.';
            case STEP_IS_UNDEFINED:
                return 'Шаг установки не задан.';
            case VALUE_LESS_THAN_ONE_ERROR:
                return 'Шаг установки меньше 1.';
            case SIDES_ARE_NOT_SELECTED_ERROR:
                return 'Ни одна сторона не выбрана.';
            case ANGLES_ARE_NOT_SELECTED_ERROR:
                return 'Ни один угол не выбран.';
        }

        return 'Неопределенная ошибка.';
    }

    /**
     * Renders components of the eyelets.
     */
    renderComponentsOfTypes() {
        const optionOfType = this.props.value.type
            ? TYPES.find(option => option.value === this.props.value.type)
            : null
        ;

        return (
            <React.Fragment>
                <div className="mt-3">
                    <CalculatorSelect
                        placeholder="Тип установки"
                        options={TYPES}
                        name={"calculationVariable[" + this.props.name + "][type]"}
                        value={optionOfType}
                        isClearable
                        onChange={this.handleTypeChange} />
                </div>

                {this.renderComponentOfType(this.props.value.type)}
            </React.Fragment>
        );
    }

    /**
     * Renders a component by the given type.
     *
     * @param {?string} type
     */
    renderComponentOfType(type) {
        switch (type) {
            case SIDE_TYPE:
                return this.renderPerimeterType();
            case ANGLE_TYPE:
                return this.renderAngleType();
            default:
                return null;
        }
    }

    /**
     * Returns the component of the perimeter type.
     */
    renderPerimeterType() {
        const stepId = this.props.id + "Step";

        return (
            <React.Fragment>
                <div className="mt-3 form-group">
                    <label htmlFor={stepId} className="col-form-label mb-2">
                        Шаг установки
                    </label>
                    <div className="form-inline">
                        <input
                            type="number"
                            className={"form-control col-xl-4"}
                            placeholder="Шаг установки, мм"
                            id={stepId}
                            name={"calculationVariable[" + this.props.name + "][step]"}
                            value={this.props.value.step}
                            onChange={this.handleStepChange} />
                        <span className="ml-2">мм</span>
                    </div>

                </div>
                <div className="mt-3 w-50">
                    <CheckboxesWithSides
                        id={this.props.id}
                        name={this.props.name}
                        sides={this.props.value.sides}
                        onChange={this.handleSideChange} />
                </div>
            </React.Fragment>
        );
    }

    /**
     * Returns the component of the angle type.
     */
    renderAngleType() {
        return (
            <div className="mt-2 w-75">
                <CheckboxesWithAngles
                    id={this.props.id}
                    name={this.props.name}
                    angles={this.props.value.angles}
                    onChange={this.handleAngleChange} />
            </div>
        );
    }

    render() {
        const activeId = this.props.id + "Active";

        return (
            <div className="mb-4">
                <div className="row">
                    <div className="col-xl-4 col-sm-12 col-xs-12">
                        <label className="col-form-label">
                            Люверсы
                        </label>
                    </div>

                    <div className="col-xl-8 col-sm-12 col-xs-12">
                        <div className={"custom-control custom-checkbox"}>
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={activeId}
                                name={"calculationVariable[" + this.props.name + "][active]"}
                                onChange={this.handleChangeActive}
                                checked={this.props.value.active} />
                            <label className="custom-control-label" htmlFor={activeId} />
                            <label htmlFor={activeId} className="mb-1">
                                Используется
                            </label>
                        </div>

                        {this.props.value.active && this.renderComponentsOfTypes()}

                        {this.props.value.active && this.props.errors.length > 0 &&
                            <div className="mt-2 text-danger">
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
            </div>
        );
    }
}

Eyelets.propTypes = {
    value: PropTypes.object,
    errors: PropTypes.array,
    onChange: PropTypes.func,
};

Eyelets.defaultProps = {
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
