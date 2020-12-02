import React from 'react';
import getMessageByType from './messages';

/**
 * Shows an option with its values in the form of radio buttons or checkboxes.
 */
export default class RadioCheckbox extends React.Component {
    constructor(props) {
        super(props);

        this.getVisibleValues = this.getVisibleValues.bind(this);
        this.isVisible = this.isVisible.bind(this);
        this.renderComponent = this.renderComponent.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    /**
     * Prepares values for the component.
     */
    componentDidMount() {
        this.values = this.props.values;
        this.componentType = this.props.multiple ? 'checkbox' : 'radio';
    }

    /**
     * Returns visible values.
     *
     * @return {array|object[]}
     */
    getVisibleValues() {
        if (! Array.isArray(this.values)) {
            return [];
        }

        return this.values.filter(value => this.isVisible(value.id));
    }

    /**
     * Checks whether an value by the given ID is visible.
     *
     * @param {number} id An ID of option.
     * @return {boolean}
     */
    isVisible(id) {
        return this.props.visibility.values[id].visible === true;
    }

    /**
     * Render the component by the given type and returns its.
     *
     * @param {string} type A component type
     * @param {array|object[]} values Values of the option
     * @param {array|number[]} selectedValues IDs of selected values
     * @param {string} prefixId A prefix for HTML ID
     * @return {object}
     */
    renderComponentByType(type, values, selectedValues, prefixId = '') {
        return (
            <React.Fragment>
                {values.map((value, index) => {
                    const htmlId = prefixId + '_' + value.id;
                    const htmlName = prefixId + '_' + type;
                    const classes = 'ml-0' + (index === 0 ? '' : ' mt-2');
                    const state = this.isChecked(value.id, selectedValues);

                    return (
                        <div className={classes} key={value.id}>
                            {this.renderComponent(type, htmlId, htmlName, value.id, state, value.title)}
                        </div>
                    );
                })}
            </React.Fragment>
        );
    }

    /**
     * Checks whether the given value ID is selected.
     *
     * @param {number} valueId
     * @param {array|number[]} selectedValues
     * @return {boolean}
     */
    isChecked(valueId, selectedValues) {
        return selectedValues.indexOf(valueId) !== -1;
    }

    /**
     * Renders the component with the given type and the other values.
     * Returns rendered component.
     *
     * @param {string} type A component type
     * @param {string} id An element ID
     * @param {string} name  An element name
     * @param {number} value An element value
     * @param {boolean} state An element state
     * @param {string} label An element label
     * @return {object}
     */
    renderComponent(type, id, name, value, state, label) {
        const classType = 'custom-' + type;
        // If it allows to use only single value then to change value is disallowed.
        const readOnly = this.props.required && this.values.length === 1 && this.props.selectedValues.length === 1;
        const onChange = readOnly ? null : this.handleChange;

        // WARNING: Используется следующий вид компонента, а не стандартный
        // bootstrap, потому что нарушено стандартное поведение стилей.
        // Структура взята из других custom-checkbox.
        return (
            <div className={"custom-control " + classType}>
                <input
                    type={type}
                    className="custom-control-input"
                    id={id}
                    name={name}
                    onChange={onChange}
                    value={value}
                    checked={state} />
                <label className="custom-control-label" htmlFor={id} />
                <label htmlFor={id} className="mb-1">
                    {label}
                </label>
            </div>
        );
    }

    /**
     * Handles changing of elements.
     * Calls the callback and passes new selected values.
     *
     * @param {object} value An object of clicked element of React.
     */
    handleChange(value) {
        let selectedValues = [...this.props.selectedValues];
        const valueId = Number.parseInt(value.target.value);

        if (this.componentType === 'checkbox') {
            // Adds or deletes selected value
            if (value.target.checked) {
                selectedValues.push(valueId);
            } else {
                const indexForDeleting = selectedValues.indexOf(valueId);
                selectedValues.splice(indexForDeleting, 1);
            }
        } else {
            // Only one value can be selected: radio
            selectedValues = [valueId];
        }

        this.props.onChange(selectedValues);
    }

    render() {
        if (! this.props.visibility.visible) {
            return null;
        }

        const visibleValues = this.getVisibleValues();
        const component = this.renderComponentByType(
            this.componentType,
            visibleValues,
            this.props.selectedValues,
            this.props.id
        );
        const message = this.props.validation.error
            ? getMessageByType(this.props.validation.type, this.props.validation.params)
            : null
        ;

        return (
            <div className="form-group row align-items-center">
                <div className="field__label col-xl-4 col-sm-12 col-xs-12">
                    <label htmlFor={this.props.id} className="col-form-label">
                        {this.props.label}
                        {this.props.required && <span className="text-danger pl-1">*</span>}
                    </label>
                </div>
                <div className="field__select col-xl-8 col-sm-10 col-xs-10">
                    <div>
                        {component}
                    </div>
                    {message && <div className="mt-2 text-danger">{message}</div>}
                </div>
            </div>
        );
    }
}

RadioCheckbox.defaultProps = {
    selectedValues: [],
};
