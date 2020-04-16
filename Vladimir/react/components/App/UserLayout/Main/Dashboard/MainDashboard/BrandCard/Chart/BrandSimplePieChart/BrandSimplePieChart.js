import React, {Component} from 'react';
import './BrandSimplePieChart.scss';
import SimplePieChart from '../../../../../../../../../components/Ui/Chart/SimplePieChart';
import {connect} from "react-redux";


class BrandSimplePieChart extends Component {

    render() {

        let chartData = [];

        if(this.props.statData && this.props.statData.dataset && this.props.statData.dataset.total) {
            let stats = this.props.statData.dataset.total;
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
                    chartId="chart-main-dashboard-brand-spc-div"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.brandStatistics.total,
    })
)(BrandSimplePieChart);

