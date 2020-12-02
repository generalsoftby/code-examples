import React from 'react';

/**
 * Shows 4 angles: left top, right top, left bottom, right bottom.
 */
export default class CheckboxesWithAngles extends React.Component {
    constructor (props) {
        super(props);

        this.state = {
            angles: {
                leftTop: false,
                leftBottom: false,
                rightTop: false,
                rightBottom: false,
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
     * Handles a change of a angle.
     *
     * @param {Event} e
     */
    handleChange(e) {
        this.setState({
            angles: {
                ...this.state.angles,
                [e.target.value]: !this.state.angles[e.target.value]
            },
        }, this.changeValue);
    }

    /**
     * Calls the callback and sends selected angles.
     */
    changeValue() {
        this.props.onChange(this.state.angles);
    }

    render() {
        const leftTopId = 'leftTopAngle' + this.props.id;
        const rightTopId = 'rightTopAngle' + this.props.id;
        const leftBottomId = 'leftBottomAngle' + this.props.id;
        const rightBottomId = 'rightBottomId' + this.props.id;

        return <React.Fragment>
            <label className="col-form-label">Углы установки</label>

            <div className="mt-2">
                <div className="form-row justify-content-between">
                    <div className="col">
                        <div className="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={leftTopId}
                                name={"calculationVariable[" + this.props.name + "][angles][left_top]"}
                                onChange={this.handleChange}
                                value="leftTop"
                                checked={this.props.angles.leftTop} />
                            <label className="custom-control-label" htmlFor={leftTopId} />
                            <label className="ml-1 mb-1" htmlFor={leftTopId}>
                                Левый верхний
                            </label>
                        </div>
                    </div>
                    <div className="col">
                        <div className="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={rightTopId}
                                name={"calculationVariable[" + this.props.name + "][angles][right_top]"}
                                onChange={this.handleChange}
                                value="rightTop"
                                checked={this.props.angles.rightTop} />
                            <label className="custom-control-label" htmlFor={rightTopId} />
                            <label className="ml-1 mb-1" htmlFor={rightTopId}>
                                Правый верхний
                            </label>
                        </div>
                    </div>
                </div>

                <div className="form-row justify-content-between mt-2">
                    <div className="col">
                        <div className="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={leftBottomId}
                                name={"calculationVariable[" + this.props.name + "][angles][left_bottom]"}
                                onChange={this.handleChange}
                                value="leftBottom"
                                checked={this.props.angles.leftBottom} />
                            <label className="custom-control-label" htmlFor={leftBottomId} />
                            <label className="ml-1 mb-1" htmlFor={leftBottomId}>
                                Левый нижний
                            </label>
                        </div>
                    </div>
                    <div className="col">
                        <div className="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                className="custom-control-input"
                                id={rightBottomId}
                                name={"calculationVariable[" + this.props.name + "][angles][right_bottom]"}
                                onChange={this.handleChange}
                                value="rightBottom"
                                checked={this.props.angles.rightBottom} />
                            <label className="custom-control-label" htmlFor={rightBottomId} />
                            <label className="ml-1 mb-1" htmlFor={rightBottomId}>
                                Правый нижний
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </React.Fragment>;
    }
}

CheckboxesWithAngles.defaultProps = {
    angles: {
        leftTop: false,
        leftBottom: false,
        rightTop: false,
        rightBottom: false,
    },
    onChange: (angles) => {},
};
