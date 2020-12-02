import React from 'react';
import PropTypes from 'prop-types';
import CalculationVariable from './CalculationVariable';

/**
 * Shows calculation variables.
 */
class Index extends React.Component {
    constructor(props) {
        super(props);

        /** @var {object} configs Contains variables names in the form of keys and their system configs */
        this.configs = {};

        this.props.calculationVariables.forEach((variable) => {
            this.configs[variable.name] = this.props.configs.hasOwnProperty(variable.name)
                ? this.props.configs[variable.name]
                : undefined
            ;
        });
    }

    render() {
        return (
            <React.Fragment>
                {this.props.calculationVariables.map((variable) => {
                    let value, errors;

                    if (typeof this.props.states[variable.name] !== 'undefined') {
                        value = this.props.states[variable.name].value;
                        errors = this.props.states[variable.name].errors;
                    }

                    const broadcastingValue = this.props.broadcastingValues.hasOwnProperty(variable.name)
                        ? this.props.broadcastingValues[variable.name]
                        : null
                    ;

                    /** @var {object|null} broadcastingConfig */
                    const broadcastingConfig = this.props.broadcastingConfig.hasOwnProperty(variable.name)
                        ? this.props.broadcastingConfig[variable.name]
                        : {}
                    ;

                    return <CalculationVariable
                        key={variable.name}
                        position={this.props.position}
                        type={variable.type}
                        name={variable.name}
                        settings={variable.settings}
                        config={this.configs[variable.name]}
                        broadcastingConfig={broadcastingConfig}
                        value={value}
                        broadcastingValue={broadcastingValue}
                        errors={errors}
                        onChange={this.props.onCalculationVariableChange}
                        onDependencesChange={this.props.onDependencesChange}
                        onConfigBroadcast={this.props.onConfigBroadcast}
                        onValueBroadcast={this.props.onValueBroadcast} />;
                })}
            </React.Fragment>
        );
    }
}

Index.propTypes = {
    calculationVariables: PropTypes.array,
    position: PropTypes.string,
    states: PropTypes.object,
};

Index.defaultProps = {
    calculationVariables: [],
    configs: {},
    broadcastingConfig: {},
    /**
     * States of calculation variables. Contains values and errors.
     */
    states: {},
    broadcastingValues: {},
    position: 'top',
    /**
     * A callback to change a value.
     *
     * @param {string} name A name of the calculation variable
     * @param {object} value A new value
     * @param {array|object[]} errors An object with errors
     */
    onCalculationVariableChange: (name, value, errors) => {},
    /**
     * A function to change values of other components.
     */
    onDependencesChange: (name, statesOfBlocks) => {},
};

export default Index;
