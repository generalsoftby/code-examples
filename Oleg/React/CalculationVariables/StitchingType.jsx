import React from 'react';
import CalculatorSelect from '../Common/CalculatorSelect';
import { isEqual } from 'lodash';

export default class StitchingType extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            value: null,
        };

        this.handleWayChange = this.handleWayChange.bind(this);
        this.changeValue = this.changeValue.bind(this);
    }

    /**
     * Initializes data of the component.
     */
    componentDidMount() {
        this.initOptionsForSelect();

        // Sends initialized values of the component to the callback
        this.changeValue();
    }

    /**
     * Initializes options and formats for Select.
     */
    initOptionsForSelect() {
        this.options = Object.values(this.props.ways).map((way, index) => ({
            value: index,
            label: way.name
        }));
    }

    /**
     * Optimizes rendering components. Compares the value and errors.
     *
     * @param {object} nextProps
     * @return {boolean}
     */
    shouldComponentUpdate(nextProps) {
        return !isEqual(this.props.value, nextProps.value);
    }

    /**
     * Returns an object by the given value.
     *
     * @param {object} value
     * @return {?object}
     */
    getOptionByValue(value) {
        return value ? this.options.find(option => option.label === value.name) : null;
    }

    /**
     * Returns a way by the given value.
     *
     * @param {object} value
     * @return {?object}
     */
    getWayByValue(value) {
        return this.props.ways.find((way, index) => way.name === value.name && index === value.index);
    }

    /**
     * Handles changing of the list with ways.
     *
     * @param {object} event
     */
    handleWayChange(event) {
        this.setState({
            value: {
                index: event.value,
                name: event.label,
            },
        }, this.changeValue);
    }

    /**
     * Calls the callback.
     */
    changeValue() {
        /** @var {object} config Config of NumberOfPagesInBlock (NumberPfProducts) */
        let config = {};

        this.props.onChange(this.props.name, this.state.value, this.validate(this.state.value));

        if (this.state.value) {
            const way = this.getWayByValue(this.state.value);

            if (way) {
                this.props.onDependencesChange([
                    {name: 'cover', state: way.cover.active},
                    {name: 'substrate', state: way.substrate.active},
                    {name: 'block', state: way.block.active},
                ]);

                // If the block is active then broadcast the new config
                // for NumberOfPagesInBlock of all blocks.
                if (way.block.active) {
                    config = {
                        min: Number.parseInt(way.block.min_number_of_pages),
                        max: way.block.max_number_of_pages ? Number.parseInt(way.block.max_number_of_pages) : null,
                        step: Number.parseInt(way.block.frequency_of_pages),
                    };
                }
            }
        } else {
            this.props.onDependencesChange([
                {name: 'cover', state: false},
                {name: 'substrate', state: false},
                {name: 'block', state: false},
            ]);
        }

        // Broadcasts the config to all blocks for 'number_of_pages_in_block'.
        this.props.onConfigBroadcast([{
            name: 'number_of_pages_in_block',
            componentType: 'calculationVariables',
            config,
        }]);
    }

    /**
     * Validates the given values.
     *
     * @param {object} value
     * @return {object[]}
     */
    validate(value) {
        if (!value) {
            return [{
                type: 'VALUE_IS_NOT_SELECTED',
            }];
        }

        return [];
    }

    render() {
        return (
            <div className="mb-4">
                <div className="row">
                    <div className="col-xl-4 col-sm-12 col-xs-12">
                        <label className="col-form-label">Способ брошюровки / переплёта</label>
                    </div>
                    <div className="col-xl-8 col-sm-12 col-xs-12">
                        <CalculatorSelect
                            placeholder="Выберите способ"
                            options={this.options}
                            name={"calculationVariable[" + this.props.name + "][way]"}
                            value={this.getOptionByValue(this.props.value)}
                            onChange={this.handleWayChange} />

                        {this.props.errors.length > 0 &&
                            <div className="mt-2 text-danger">
                                Значение не выбрано.
                            </div>
                        }
                    </div>
                    <div className="col-md-2"/>
                </div>
            </div>
        );
    }
}

StitchingType.defaultProps = {
    config: {},
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
