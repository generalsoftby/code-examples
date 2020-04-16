import React, {Component} from 'react';
import './EfficiencyAnimatedTimeLinePieChart.scss';
import AnimatedTimeLinePieChart from '../../../../../../../../../components/Ui/Chart/AnimatedTimeLinePieChart';
import {connect} from "react-redux";

class EfficiencyAnimatedTimeLinePieChart extends Component {

    render() {

        let chartData = [];
        if(this.props.statData && this.props.statData.efficiency) {
            let efficiency = this.props.statData.efficiency;
            if(efficiency) {
                efficiency.legend.forEach(function (item, i) {
                    let itemStr = item;
                    switch (item) {
                        case 'second_hand':
                            itemStr = 'Second Hand';
                            break;
                        case 'removed':
                            itemStr = 'Removed';
                            break;
                        case 'no_infringements':
                            itemStr = 'No infringement';
                            break;
                        case 'authorized':
                            itemStr = 'Authorized';
                            break;
                        case 'pending':
                            itemStr = 'Pending';
                            break;
                        case 'detected':
                            itemStr = 'Detected';
                            break;
                        default:
                            break;
                    }
                    let obj = {
                        'sector': itemStr,
                        'size': efficiency.data[i],
                    };
                    chartData.push(obj);
                });
            }
        }

        return (
            <div>
                <AnimatedTimeLinePieChart
                    chartData={chartData}
                    chartId="chart-main-dashboard-efficiency-satlpc-div"
                    colorScheme="VALUES"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.mainStatistics.search_engines,
    })
)(EfficiencyAnimatedTimeLinePieChart);

