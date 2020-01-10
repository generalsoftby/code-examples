import React, {Component} from 'react';
import './TagSimplePieChart.scss';
import SimplePieChart from '../../../../../../../../../components/Ui/Chart/SimplePieChart';
import {connect} from "react-redux";


class TagSimplePieChart extends Component {

    render() {

        let chartData = [];

        if(this.props.statData) {
            let stats = this.props.statData;
            if(stats.series) {
                stats.series.forEach(function (item, i) {
                    let obj = {
                        'parameter': item,
                        'value': stats.data[i],
                    };
                    chartData.push(obj);
                });
            }

        }


        return (
            <div>
                <SimplePieChart
                    chartData={chartData}
                    chartId="chart-main-dashboard-tag-spc-div"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.tagStatistics.total,
    })
)(TagSimplePieChart);

