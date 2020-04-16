import React, {Component} from 'react';
import './DateRangeSwitcher.scss';
import {connect} from "react-redux";
import { clientSettingsMainFilterDateRange } from "../../../../actions/clientSettings/mainFilterDateRange";
import { RangeDatePicker } from '@y0c/react-datepicker';
import '@y0c/react-datepicker/assets/styles/calendar.scss';

class DateRangeSwitcher extends Component {

    onClickLastWeekToggle = e => {
        this.props.dispatch(clientSettingsMainFilterDateRange({
            start: null,
            stop: null,
            last: '7d',
        }));
    }

    onClickLastMonthToggle = e => {
        this.props.dispatch(clientSettingsMainFilterDateRange({
            start: null,
            stop: null,
            last: '1m',
        }));
    }

    onClickLastYearToggle = e => {
        this.props.dispatch(clientSettingsMainFilterDateRange({
            start: null,
            stop: null,
            last: '1y',
        }));
    }

    onChangeDate = (start, end) => {
        if ( start && end ) {
            let srtStart = start.getFullYear() + '-' + ("0" + (start.getMonth() + 1)).slice(-2) + "-" + ("0"+(start.getDate())).slice(-2);
            let srtEnd = end.getFullYear() + '-' + ("0" + (end.getMonth() + 1)).slice(-2) + "-" + ("0"+(end.getDate())).slice(-2);
            this.props.dispatch(clientSettingsMainFilterDateRange({
                start: srtStart,
                stop: srtEnd,
                last: null,
            }));
        }
    }

    render() {

        var attrDateRange = {};
       // if (this.props.mainFilterDateRange.stop)
        //    attrDateRange.value = new Date(this.props.mainFilterDateRange.start);
       // if (this.props.mainFilterDateRange.stop)
       //     attrDateRange.initialEndDate = new Date(this.props.mainFilterDateRange.stop);
            //console.log(attrDateRange);

        return (
            <div className="container-filter-date">
                <div data-uk-grid>
                    <div className="uk-width-auto uk-flex">
                        <ul className="gr-daterange-switcher">
                            <li>
                                <button
                                    className={'item-switch' + (this.props.mainFilterDateRange.last === '7d' ? ' active' : '' )}
                                    onClick={this.onClickLastWeekToggle}
                                >Last week</button>
                            </li>
                            <li>
                                <button
                                    className={'item-switch' + (this.props.mainFilterDateRange.last === '1m' ? ' active' : '' )}
                                    onClick={this.onClickLastMonthToggle}
                                >Last month</button>
                            </li>
                            <li>
                                <button
                                    className={'item-switch' + (this.props.mainFilterDateRange.last === '1y' ? ' active' : '' )}
                                    onClick={this.onClickLastYearToggle}
                                >Last Year</button>
                            </li>
                        </ul>
                        <div className="gr-daterange-container">
                            <RangeDatePicker
                                showMonthCnt={3}
                                dateFormat={'DD MMM YYYY'}
                                onChange={this.onChangeDate}
                                {...attrDateRange}
                            />
                        </div>
                    </div>
                </div>
            </div>
        );

    }
}

export default connect(
    state => ({
        mainFilterDateRange: state.clientSettings.mainFilterDateRange,
    })
)(DateRangeSwitcher);



