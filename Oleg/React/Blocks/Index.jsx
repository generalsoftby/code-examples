import React from 'react';
import PropTypes from 'prop-types';
import Block from './Block';

/**
 * Shows blocks with attributes, calculation variables and options.
 * Contains common handlers.
 */
export default class Index extends React.Component {
    constructor(props) {
        super(props);

        this.handleChangeOfDependentBlocks = this.handleChangeOfDependentBlocks.bind(this);
        this.changeVisibilityOfBlocks = this.changeVisibilityOfBlocks.bind(this);
        this.isChangingVisibilityAllowed = this.isChangingVisibilityAllowed.bind(this);
    }

    /**
     * Returns a config from the given array by the given block name.
     *
     * @param  {array|object[]} configs An array with configs of blocks.
     * @param  {string} blockName
     * @return {object|null}
     */
    getConfigOfBlock(configs, blockName) {
        for (let index = 0; index < configs.length; index++) {
            if (configs[index].name === blockName) {
                return configs[index];
            }
        }

        return null;
    }

    /**
     * Checks whether changing visibility of blocks is allowed.
     *
     * @param {string} targetBlockName
     * @param {string} sourceBlockName
     * @param {string} componentType
     * @param {string} elementName
     */
    isChangingVisibilityAllowed(targetBlockName, sourceBlockName, componentType, elementName) {
        /** @var {object|null} configOfBlock */
        const configOfBlock = this.getConfigOfBlock(this.props.configuration, targetBlockName);

        if (! configOfBlock || ! configOfBlock.hasOwnProperty('visualizeBy')) {
            return false;
        }

        /** @var {array|object[]} permissions */
        const permissions = configOfBlock.visualizeBy.filter(rule =>
            rule.blockName === sourceBlockName
            && rule.componentType === componentType
            && rule.elementName === elementName
        );

        // If permissions exist then changing visibility is allowed
        return permissions.length > 0;
    }

    /**
     * Changes visiblility of dependent blocks.
     *
     * @param {string} blockName
     * @param {string} componentType A component type: calculationVariable, attribute, option.
     * @param {string} elementName
     * @param {array|object[]} statesOfBlocks An array with new states of blocks.
     */
    handleChangeOfDependentBlocks(blockName, componentType, elementName, statesOfBlocks) {
        this.changeVisibilityOfBlocks(
            blockName,
            componentType,
            elementName,
            statesOfBlocks
        );
    }

    /**
     * Changes visiblility of blocks.
     *
     * @param {string} blockName
     * @param {string} componentType A component type: calculationVariable, attribute, option.
     * @param {string} elementName
     * @param {array|object[]} statesOfBlocks An array with new states of blocks.
     */
    changeVisibilityOfBlocks(blockName, componentType, elementName, statesOfBlocks) {
        const visibilityOfBlocks = {};

        statesOfBlocks.forEach(element => {
            if (this.isChangingVisibilityAllowed(element.name, blockName, componentType, elementName)) {
                visibilityOfBlocks[element.name] = element.state;
            }
        });

        this.props.onVisibilityOfBlocksChange(visibilityOfBlocks);
    }

    render() {
        return (
            <React.Fragment>
                {this.props.configuration.map((block) => {
                    const broadcastingConfig = this.props.broadcastingConfig.hasOwnProperty(block.name)
                        ? this.props.broadcastingConfig[block.name]
                        : undefined
                    ;

                    const broadcastingValues = this.props.broadcastingValues.hasOwnProperty(block.name)
                        ? this.props.broadcastingValues[block.name]
                        : undefined
                    ;

                    return <Block
                        key={block.name}
                        name={block.name}
                        broadcastingConfig={broadcastingConfig}
                        contents={block.contents}
                        groupsWithAttributes={this.props.groupsWithAttributes}
                        attributesOfBlock={this.props.blockAttributes[block.name]}
                        optionsOfBlock={this.props.blockOptions[block.name]}
                        calculationVariables={this.props.calculationVariables}
                        visible={this.props.visibilityOfBlocks[block.name]}
                        selectedAttributes={this.props.userInput.componentBlocks[block.name].selectedAttributes}
                        selectedOptions={this.props.userInput.componentBlocks[block.name].selectedOptions}
                        statesOfCalculationVariables={
                            this.props.userInput.componentBlocks[block.name].calculationVariables
                        }
                        broadcastingValues={broadcastingValues}
                        onAttributesChange={this.props.onAttributesChange}
                        onOptionsChange={this.props.onOptionsChange}
                        onCalculationVariableChange={this.props.onCalculationVariableChange}
                        onConfigBroadcast={this.props.onConfigBroadcast}
                        onValueBroadcast={this.props.onValueBroadcast}
                        onDependentBlocksChange={this.handleChangeOfDependentBlocks} />;
                })}
            </React.Fragment>
        );
    }
}

Index.displayName = 'Blocks';

Index.propTypes = {
    visibilityOfBlocks: PropTypes.object,
    blockAttributes: PropTypes.object,
    blockOptions: PropTypes.object,
    configuration: PropTypes.array,
    groupsWithAttributes: PropTypes.object,
    calculationVariables: PropTypes.array,
    userInput: PropTypes.object,
    onVisibilityOfBlocksChange: PropTypes.func,
    onAttributesChange: PropTypes.func,
    onCalculationVariableChange: PropTypes.func,
    onOptionsChange: PropTypes.func,
};

Index.defaultProps = {
    visibilityOfBlocks: {},
    groupsWithAttributes: {},
    blockAttributes: {},
    blockOptions: {},
    calculationVariables: [],
    onAttributeChange: (attribute, blockName) => {},
    onVisibilityOfBlocksChange: (visibilityOfBlocks) => {},
};
