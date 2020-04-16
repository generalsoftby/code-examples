import React, {Component} from 'react';
import './MapDrillDownToCountries.scss';
import DrillDownToCountries from '../../../../../../../../../components/Ui/Chart/DrillDownToCountries';
import {connect} from "react-redux";

class MapDrillDownToCountries extends Component {

    render() {

        let chartData = [];

        return (
            <div>
                <DrillDownToCountries
                    chartData={chartData}
                    chartId="chart-main-dashboard-map-statistic-div"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.mainStatistics.total,
    })
)(MapDrillDownToCountries);

