import React from 'react';
import PrintFormats from './PrintFormats';
import NumberOfProducts from './NumberOfProducts';
import StitchingType from './StitchingType';
import Gluing from './Gluing';
import Sides from './Sides';
import Eyelets from './Eyelets';

/**
 * Connects and shows a calculation variable.
 */
export default class CalculationVariable extends React.Component {
    constructor (props) {
        super(props);

        this.handleChangeDependences = this.handleChangeDependences.bind(this);
        this.handleConfigOfBroadcasting = this.handleConfigOfBroadcasting.bind(this);
        this.handleValueOfBroadcasting = this.handleValueOfBroadcasting.bind(this);
    }

    /**
     * Initializes a component position.
     */
    componentDidMount() {
        /** @var {string} componentPosition A positon of the component on the form: top or bottom. **/
        this.componentPosition = typeof this.props.settings.position === 'undefined'
            ? 'top'
            : this.props.settings.position
        ;
    }

    /**
     * Calls the callback of the change of dependences.
     *
     * @param {string} name
     * @param {object[]} statesOfBlocks
     */
    handleChangeDependences(statesOfBlocks) {
        this.props.onDependencesChange(this.props.name, statesOfBlocks);
    }

    /**
     * Calls the callback to broadcast the given config.
     *
     * @param {object[]} config
     */
    handleConfigOfBroadcasting(config) {
        this.props.onConfigBroadcast(this.props.name, config);
    }

    /**
     * Calls the callback to broadcast the given value.
     *
     * @param {object[]} value
     */
    handleValueOfBroadcasting(value) {
        this.props.onValueBroadcast(this.props.name, value);
    }

    render() {
        if (this.props.position !== this.componentPosition) {
            return null;
        }

        // NOTE: Add here your new components of calculation variables
        // Use the 'changeDependences'=this.props.changeDependences to provide
        // a possibility to change visibility of blocks.
        switch (this.props.type) {
            case 'print_formats':
                return (
                    <PrintFormats
                        config={this.props.config}
                        formats={this.props.settings.formats}
                        rules={this.props.settings.rules}
                        value={this.props.value}
                        broadcastingValue={this.props.broadcastingValue}
                        errors={this.props.errors}
                        name={this.props.name}
                        onChange={this.props.onChange}
                        onValueBroadcast={this.handleValueOfBroadcasting}
                        group={this.props.position} />
                );
            case 'number_of_products':
                return (
                    <NumberOfProducts
                        config={this.props.config}
                        type={this.props.settings.type}
                        label={this.props.settings.label}
                        unit={this.props.settings.unit}
                        placeholder={this.props.settings.placeholder}
                        rules={this.props.settings.rules[this.props.settings.type]}
                        value={this.props.value}
                        errors={this.props.errors}
                        name={this.props.name}
                        onChange={this.props.onChange}
                        group={this.props.position} />
                );
            case 'stitching_type':
                return (
                    <StitchingType
                        config={this.props.config}
                        ways={this.props.settings}
                        value={this.props.value}
                        errors={this.props.errors}
                        name={this.props.name}
                        onChange={this.props.onChange}
                        onDependencesChange={this.handleChangeDependences}
                        onConfigBroadcast={this.handleConfigOfBroadcasting}
                        group={this.props.position} />
                );
            case 'gluing':
                return (
                    <Gluing
                        config={this.props.config}
                        value={this.props.value}
                        errors={this.props.errors}
                        name={this.props.name}
                        onChange={this.props.onChange}
                        group={this.props.position} />
                );
            case 'sides':
                return (
                    <Sides
                        config={this.props.config}
                        label={this.props.settings.label}
                        value={this.props.value}
                        errors={this.props.errors}
                        name={this.props.name}
                        id={'sides' + this.props.name}
                        onChange={this.props.onChange}
                        group={this.props.position} />
                );
            case 'eyelets':
                return (
                    <Eyelets
                        config={this.props.config}
                        value={this.props.value}
                        errors={this.props.errors}
                        name={this.props.name}
                        id={this.props.name}
                        onChange={this.props.onChange}
                        group={this.props.position} />
                );
            default:
                console.warn('Неподдерживаемая переменная расчета: [name: ' + this.props.name + ', position: ' + this.componentPosition + ']');
                return null;
        }
    }
}

CalculationVariable.defaultProps = {
    config: {},
    errors: [],
    broadcastingValue: null,
    broadcastingConfig: {},
    /**
     * A callback to assign a new value.
     *
     * @param {string} name
     * @param {object} value A new value of the component
     * @param {bool} error A state of error
     */
    onChange: (name, value, error) => {},
    onDependencesChange: (name, statesOfBlocks) => {},
    onConfigBroadcast: (name, config) => {},
    onValueBroadcast: (name, values) => {},
};
