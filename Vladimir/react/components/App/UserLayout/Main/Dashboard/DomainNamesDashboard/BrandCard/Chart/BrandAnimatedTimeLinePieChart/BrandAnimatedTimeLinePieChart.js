import React, {Component} from 'react';
import './BrandAnimatedTimeLinePieChart.scss';
import AnimatedTimeLinePieChart from '../../../../../../../../../components/Ui/Chart/AnimatedTimeLinePieChart';
import {connect} from "react-redux";

class BrandAnimatedTimeLinePieChart extends Component {

    render() {

        let chartData = [];
        if(this.props.statData && this.props.statData.dataset && this.props.statData.dataset.total) {
            let filter = this.props.filter;
            let part = filter ? filter.value : 'total';
            let stats = this.props.statData.dataset[part];
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
                    chartId="chart-main-dashboard-brand-satlpc-div"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.brandStatistics.domains,
    })
)(BrandAnimatedTimeLinePieChart);

