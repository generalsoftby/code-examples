import React from 'react';

export const NAN_ERROR = 'NAN_ERROR';
export const UNKNOWN_ERROR = 'UNKNOWN_ERROR';
export const MIN_ERROR = 'MIN_ERROR';
export const MAX_ERROR = 'MAX_ERROR';

const MIN_SOURCE = 'MIN_SOURCE';
const MAX_SOURCE = 'MAX_SOURCE';

export default class SizeOfPrintFormat extends React.Component {
    /**
     * Checks whether the error belongs to the given source.
     *
     * @param {string} source
     * @return {boolean}
     */
    hasError(source) {
        const anySource = [NAN_ERROR, UNKNOWN_ERROR];

        if (!this.props.error) {
            return false;
        }

        if (
            anySource.includes(this.props.error)
            || (source === MIN_SOURCE && this.props.error === MIN_ERROR)
            || (source === MAX_SOURCE && this.props.error === MAX_ERROR)
        ) {
            return true;
        }

        return false;
    }

    render() {
        let minValueLabel, maxValueLabel;

        if (typeof this.props.min !== 'undefined' && Number.isFinite(this.props.min) && this.props.visibleLimitation) {
            minValueLabel = (
                <label className={this.hasError(MIN_SOURCE) ? "text-danger" : "text-secondary"}>
                    Min: {this.props.min} {this.props.unit}
                </label>
            );
        }

        if (this.props.max && Number.isFinite(this.props.max) && this.props.visibleLimitation) {
            maxValueLabel = (
                <label className={"ml-3 " + (this.hasError(MAX_SOURCE) ? "text-danger" : "text-secondary")}>
                    Max: {this.props.max} {this.props.unit}
                </label>
            );
        }

        return (
            <div className={this.props.className}>
                <div className="row pr-3 align-items-center">
                    <label className="col-md-5 col-form-label text-right" htmlFor={this.props.id}>
                        {this.props.label}
                    </label>
                    <input type="number"
                        name={this.props.name}
                        id={this.props.id}
                        value={this.props.value}
                        onChange={this.props.onChange}
                        min={this.props.min}
                        max={this.props.max}
                        readOnly={this.props.readOnly}
                        className={"form-control col-md-5" + (this.props.error ? " has-format-error" : "")} />
                    <span className="col-md-2">
                        {this.props.unit}
                    </span>
                </div>
                { (minValueLabel || maxValueLabel) &&
                    <div className="mt-2 text-right">
                        {minValueLabel} {maxValueLabel}
                    </div>
                }
            </div>
        );
    }
}
