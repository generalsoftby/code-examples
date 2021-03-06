import React, {Component} from 'react';
import './TopSellerChart.scss';
import SolidGauge from '../../../../../../../../../components/Ui/Chart/SolidGauge';
import {connect} from "react-redux";


class TopSellerChart extends Component {

    render() {

        let chartData = [];

        if(this.props.statData) {
            let stats = this.props.statData;
            if(stats.series) {
                let fullValue = 0;
                stats.series.forEach(function (item, i) {
                    fullValue = fullValue + stats.data[i];
                })

                stats.series.forEach(function (item, i) {
                    let obj = {
                        'category': item,
                        'value': fullValue > 0 ? (stats.data[i] / fullValue) * 100 : 0,
                        'full': 100,
                    };
                    chartData.push(obj);
                });
                chartData.sort(function(a, b){return a.value - b.value});
            }

        }

        return (
            <div>

            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.tagStatistics.markeplaces,
    })
)(TopSellerChart);

