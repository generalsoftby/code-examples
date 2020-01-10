import React, {Component} from 'react';
import './IssuesStatisticsStackedArea.scss';
import StackedArea from '../../../../../../../../../components/Ui/Chart/StackedArea';
import {connect} from "react-redux";

class IssuesStatisticsStackedArea extends Component {

    render() {

        let chartData = [];
        if(this.props.statData && this.props.statData.issues_dynamic) {
            let issuesDynamic = this.props.statData.issues_dynamic;
            if(issuesDynamic) {
                issuesDynamic.series.forEach(function (item, i) {
                    let obj = {};
                    obj.point = item + ' (' + i + ')';
                    issuesDynamic.legend.forEach(function (itemL, iL) {
                        obj[itemL] = issuesDynamic.data[i] && issuesDynamic.data[i][iL] ? issuesDynamic.data[i][iL] : 0;
                    });
                    chartData.push(obj);
                });
            }
        }

        return (
            <div>
                <StackedArea
                    chartData={chartData}
                    chartId="chart-main-dashboard-issues-statistic-div"
                />
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.mainStatistics.image_recognition,
    })
)(IssuesStatisticsStackedArea);

