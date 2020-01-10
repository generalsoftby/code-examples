import React, {Component} from 'react';
import './EfficiencySolidGauge.scss';
import SolidGauge from '../../../../../../../../../components/Ui/Chart/SolidGauge';
import {connect} from "react-redux";


class EfficiencySolidGauge extends Component {

    render() {

        let chartData = [];

        if(this.props.statData && this.props.statData.efficiency) {
            let efficiency = this.props.statData.efficiency;
            if(efficiency) {
                let fullValue = 0;
                efficiency.legend.forEach(function (item, i) {
                    fullValue = fullValue + efficiency.data[i];
                })

                efficiency.legend.forEach(function (item, i) {
                    let obj = {
                        'category': item,
                        'value': fullValue > 0 ? (efficiency.data[i] / fullValue) * 100 : 0,
                        'full': 100,
                    };
                    chartData.push(obj);
                });
                //chartData = chartData.reverse();
                chartData.sort(function(a, b){return a.value - b.value});
            }
        }

        return (
            <div>
                <SolidGauge
                    chartData={chartData}
                    chartId="chart-main-dashboard-efficiency-sg-div"
                    colorScheme="VALUES"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.mainStatistics.domains,
    })
)(EfficiencySolidGauge);

