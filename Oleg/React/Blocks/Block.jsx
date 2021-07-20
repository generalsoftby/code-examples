import React from 'react';
import Attributes from '../Attributes';
import Options from '../Options';
import CalculationVariables from '../CalculationVariables';

/**
 * Connects and shows a block with attributes, calculation variables, options, etc.
 */
export default class Block extends React.Component {
    constructor(props) {
        super(props);

        /** @var {array|object[]} calculationVariables */
        this.calculationVariables = {
            top: [],
            bottom: [],
        };

        /** @var {object|object[]} configOfCalculationVariables */
        this.configOfCalculationVariables = {};

        if (typeof props.contents.calculationVariables === 'object') {
            /** @var {array|Object[]} allowedCalculationVariables */
            const allowedCalculationVariables = this.props.calculationVariables.filter(calculationVariable =>
                props.contents.calculationVariables.hasOwnProperty(calculationVariable.name)
            );

            // The default position is top.
            this.calculationVariables.top = allowedCalculationVariables.filter(element =>
                (typeof element.settings.position !== 'undefined' && element.settings.position === 'top')
                || (typeof props.contents.calculationVariables[element.name].position !== 'undefined'
                && props.contents.calculationVariables[element.name].position === 'top')
            );

            // The other positon is bottom.
            this.calculationVariables.bottom = allowedCalculationVariables.filter(element =>
                !((typeof element.settings.position !== 'undefined' && element.settings.position === 'top')
                || (typeof props.contents.calculationVariables[element.name].position !== 'undefined'
                && props.contents.calculationVariables[element.name].position === 'top'))
            );

            // Prepares configs for components of calculation variables
            /** @var {array|string[]} variableNames */
            const variableNames = allowedCalculationVariables.map(element => element.name);

            this.configOfCalculationVariables = this.pullConfigsOfCalculationVariables(
                props.contents.calculationVariables,
                variableNames
            );
        }

        this.handleChangeOfAttributes = this.handleChangeOfAttributes.bind(this);
        this.handleChangeOfOption = this.handleChangeOfOption.bind(this);
        this.handleChangeOfCalculationVariable = this.handleChangeOfCalculationVariable.bind(this);
        this.handleChangeOfDependentBlocks = this.handleChangeOfDependentBlocks.bind(this);
        this.handleConfigOfBroadcasting = this.handleConfigOfBroadcasting.bind(this);
        this.handleValueOfBroadcasting = this.handleValueOfBroadcasting.bind(this);
    }

    /**
     * Pulls system configs of calculation variables from an object with configs.
     *
     * @param {object|object[]} configs
     * @param {array} variableNames
     */
    pullConfigsOfCalculationVariables(configs, variableNames) {
        const configOfCalculationVariables = {};

        for (const variableName in configs) {
            if (variableNames.includes(variableName) && configs[variableName].configuration) {
                configOfCalculationVariables[variableName] = configs[variableName].configuration;
            }
        }

        return configOfCalculationVariables;
    }

    /**
     * Calls the callback of change of attributes.
     *
     * @param {object} attributes
     */
    handleChangeOfAttributes(attributes) {
        this.props.onAttributesChange(attributes, this.props.name);
    }

    /**
     * Calls the callback of change of an option.
     *
     * @param {number} optionId
     * @param {array|int[]} values An array with new IDs of values
     */
    handleChangeOfOption(optionId, values) {
        this.props.onOptionsChange(optionId, values, this.props.name);
    }

    /**
     * Calls the callback of change of a calculation variable.
     *
     * @param {string} name
     * @param {object} value A new value (state)
     * @param {object|null} error An object with errors
     */
    handleChangeOfCalculationVariable(name, value, error) {
        this.props.onCalculationVariableChange(name, value, this.props.name, error);
    }

    /**
     * Handles change of depednent blocks.
     *
     * @param {string} elementName
     * @param {array|object[]} statesOfBlocks
     */
    handleChangeOfDependentBlocks(elementName, statesOfBlocks) {
        this.props.onDependentBlocksChange(this.props.name, 'calculationVariables', elementName, statesOfBlocks);
    }

    /**
     * Handles configs of broadcasting.
     *
     * @param {string} elementName
     * @param {array|object[]} configs
     */
    handleConfigOfBroadcasting(elementName, configs) {
        this.props.onConfigBroadcast(this.props.name, 'calculationVariables', elementName, configs);
    }

    /**
     * Handles a value of broadcasting.
     *
     * @param {string} elementName
     * @param {any} value
     */
    handleValueOfBroadcasting(elementName, value) {
        this.props.onValueBroadcast(this.props.name, 'calculationVariables', elementName, value);
    }

    render() {
        if (! this.props.visible) {
            return null;
        }

        /** @var {string|undefined} nameOfAttributeGroup */
        const nameOfAttributeGroup = this.props.contents.nameOfAttributeGroup;

        const broadcastingConfigOfCalculationVariables = this.props.broadcastingConfig.hasOwnProperty('calculationVariables')
            ? this.props.broadcastingConfig.calculationVariables
            : undefined;

        const broadcastingValuesOfCalculationVariables = this.props.broadcastingValues.hasOwnProperty('calculationVariables')
            ? this.props.broadcastingValues.calculationVariables
            : undefined;

        return (
            <div className="mb-3">
                {/* {this.props.name} - имя группы <br/> */}
                {
                    nameOfAttributeGroup &&
                    <Attributes
                        key={this.props.name}
                        group={nameOfAttributeGroup}
                        attributes={this.props.groupsWithAttributes[nameOfAttributeGroup]}
                        visibility={this.props.attributesOfBlock.visibility}
                        errors={this.props.attributesOfBlock.errors}
                        nameOfAttributeGroup={this.props.attributesOfBlock.nameOfAttributeGroup}
                        selectedAttributes={this.props.selectedAttributes}
                        onAttributesChange={this.handleChangeOfAttributes} />
                }
                {
                    this.calculationVariables.top.length > 0 &&
                    <CalculationVariables
                        group="top"
                        calculationVariables={this.calculationVariables.top}
                        configs={this.configOfCalculationVariables}
                        broadcastingConfig={broadcastingConfigOfCalculationVariables}
                        states={this.props.statesOfCalculationVariables}
                        broadcastingValues={broadcastingValuesOfCalculationVariables}
                        onCalculationVariableChange={this.handleChangeOfCalculationVariable}
                        onConfigBroadcast={this.handleConfigOfBroadcasting}
                        onValueBroadcast={this.handleValueOfBroadcasting}
                        onDependencesChange={this.handleChangeOfDependentBlocks} />
                }

                <Options
                    prefixIdName={'optionOfBlock_' + this.props.name + '_'}
                    options={this.props.optionsOfBlock.options}
                    groups={this.props.optionsOfBlock.groups}
                    visibility={this.props.optionsOfBlock.visibility}
                    selectedOptions={this.props.selectedOptions}
                    statesOfValidation={this.props.optionsOfBlock.statesOfValidation}
                    onOptionsChange={this.handleChangeOfOption} />

                {
                    this.calculationVariables.bottom.length > 0 &&
                    <CalculationVariables
                        group="bottom"
                        calculationVariables={this.calculationVariables.bottom}
                        configs={this.configOfCalculationVariables}
                        broadcastingConfig={broadcastingConfigOfCalculationVariables}
                        states={this.props.statesOfCalculationVariables}
                        broadcastingValues={broadcastingValuesOfCalculationVariables}
                        onCalculationVariableChange={this.handleChangeOfCalculationVariable}
                        onConfigBroadcast={this.handleConfigOfBroadcasting}
                        onValueBroadcast={this.handleValueOfBroadcasting}
                        onDependencesChange={this.handleChangeOfDependentBlocks} />
                }
            </div>
        );
    }
}

Block.defaultProps = {
    visualizeBy: [],
    optionsOfBlock: {
        options: [],
        groups: []
    },
    broadcastingConfig: {},
    broadcastingValues: {},
    onAttributesChange: (attributes, blockName) => {},
    onBlockUpdate: (block, type, data) => {},
    onDependentBlocksChange: (blockName, componentType, elementName, statesOfBlocks) => {},
};
