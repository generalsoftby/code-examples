import React from 'react';

/**
 * Shows 4 sides: left, right, top, bottom.
 */
export default class CheckboxesWithSides extends React.Component {
    constructor (props) {
        super(props);

        this.state = {
            sides: {
                left: false,
                right: false,
                top: false,
                bottom: false,
            },
        };

        this.handleChange = this.handleChange.bind(this);
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
     * Handles a change of a side.
     *
     * @param {Event} e
     */
    handleChange(e) {
        this.setState({
            sides: {
                ...this.state.sides,
                [e.target.value]: !this.state.sides[e.target.value]
            },
        }, this.changeValue);
    }

    /**
     * Calls the callback and sends selected sides.
     */
    changeValue() {
        this.props.onChange(this.state.sides);
    }

    render() {
        const leftId = 'leftSide' + this.props.id;
        const rightId = 'rightSide' + this.props.id;
        const topId = 'topSide' + this.props.id;
        const bottomId = 'bottomSide' + this.props.id;

        return <React.Fragment>
            <label className="col-form-label">Стороны установки</label>

            <div className="mt-2">
                <div className="text-center">
                    <div className="custom-control custom-checkbox">
                        <input
                            type="checkbox"
                            className="custom-control-input"
                            id={topId}
                            name={"calculationVariable[" + this.props.name + "][sides][top]"}
                            onChange={this.handleChange}
                            value="top"
                            checked={this.props.sides.top} />
                        <label className="custom-control-label" htmlFor={topId} />
                        <label htmlFor={topId} className="mb-1 ml-1">
                            Верх
                        </label>
                    </div>
                </div>

                <div className="row justify-content-between mt-1">
                    <div className="col">
                        <div className="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={leftId}
                                name={"calculationVariable[" + this.props.name + "][sides][left]"}
                                onChange={this.handleChange}
                                value="left"
                                checked={this.props.sides.left} />
                            <label className="custom-control-label" htmlFor={leftId} />
                            <label htmlFor={leftId} className="mb-1 ml-1">
                                Лево
                            </label>
                        </div>
                    </div>
                    <div className="col text-right">
                        <div className="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={rightId}
                                name={"calculationVariable[" + this.props.name + "][sides][right]"}
                                onChange={this.handleChange}
                                value="right"
                                checked={this.props.sides.right} />
                            <label className="custom-control-label" htmlFor={rightId} />
                            <label htmlFor={rightId} className="mb-1 ml-1">
                                Право
                            </label>
                        </div>
                    </div>
                </div>

                <div className="text-center">
                    <div className="custom-control custom-checkbox">
                        <input
                            type="checkbox"
                            className="custom-control-input"
                            id={bottomId}
                            name={"calculationVariable[" + this.props.name + "][sides][bottom"}
                            onChange={this.handleChange}
                            value="bottom"
                            checked={this.props.sides.bottom} />
                        <label className="custom-control-label" htmlFor={bottomId} />
                        <label htmlFor={bottomId} className="mb-1 ml-1">
                            Низ
                        </label>
                    </div>
                </div>
            </div>
        </React.Fragment>;
    }
}

CheckboxesWithSides.defaultProps = {
    sides: {
        left: false,
        right: false,
        top: false,
        bottom: false,
    },
    onChange: (sides) => {},
};
