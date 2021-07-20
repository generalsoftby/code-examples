import React from 'react';
import Option from "./Option";

/**
 * Shows options of a block. The list of options depends on attribute values.
 */
const Index = (props) => {
    if (! props.options.length) {
        return null;
    }

    return (
        <React.Fragment>
            {props.options.map(option =>
                <Option
                    key={option.id}
                    id={props.prefixIdName + option.id}
                    optionId={option.id}
                    visibility={props.visibility[option.id]}
                    type={option.component_type}
                    config={option.component_config}
                    label={option.title}
                    required={option.required}
                    multiple={option.multiple > 1}
                    values={option.values}
                    selectedValues={props.selectedOptions[option.id]}
                    validation={props.statesOfValidation[option.id]}
                    onChange={props.onOptionsChange} />
            )}
        </React.Fragment>
    );
};

Index.defaultProps = {
    options: [],
    groups: [],
    selectedOptions: [],
    /**
     * The callback of changing options.
     *
     * @param {number} optionId
     * @param {array|int[]|object} values IDs of new values or an object.
     */
    onOptionsChange: (optionId, values) => {},
};

export default Index;
