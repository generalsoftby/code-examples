import React, {Component} from 'react';
import './TagAnimatedTimeLinePieChart.scss';
import AnimatedTimeLinePieChart from '../../../../../../../../../components/Ui/Chart/AnimatedTimeLinePieChart';
import {connect} from "react-redux";

class TagAnimatedTimeLinePieChart extends Component {

    render() {

        let chartData = [];
        if(this.props.statData) {
            let stats = this.props.statData;
            if(stats.series) {
                stats.series.forEach(function (item, i) {
                    let obj = {
                        'sector': item,
                        'size': stats.data[i],
                    };
                    chartData.push(obj);
                });
            }
        }

        return (
            <div>
                <AnimatedTimeLinePieChart
                    chartData={chartData}
                    chartId="chart-main-dashboard-tag-satlpc-div"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.tagStatistics.search_engines,
    })
)(TagAnimatedTimeLinePieChart);

