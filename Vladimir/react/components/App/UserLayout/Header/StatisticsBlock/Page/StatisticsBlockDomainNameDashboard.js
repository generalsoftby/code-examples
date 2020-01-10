import React, {Component} from 'react';
import StatisticsBlockView from '../StatisticsBlockView';
import {connect} from "react-redux";

const statCaptions = {
    'issues_count': 'Total issues',
    'notices_sent': 'Total notices sent',
    'risk_high': 'High risk',
    'risk_medium': 'Medium risk',
    'risk_low': 'Low risk',
    'total_removed': 'Total removed',
};

class StatisticsBlockDomainNameDashboard extends Component {

    statTotalData = () => {
        let data = [];
        if(this.props.statTotalData && this.props.statTotalData.top_panel) {
            let objList = this.props.statTotalData.top_panel;
            for (let objKey in objList) {
                data.push({
                    'caption': statCaptions[objKey] ? statCaptions[objKey] : objList[objKey],
                    'counter': objList[objKey],
                });
            }
        }
        return data;
    }



    render() {

        let itemStatistics = this.statTotalData();
        return (
            <StatisticsBlockView itemStatistics={itemStatistics} />
        );
    }
}

export default connect(
    state => ({
        statTotalData: state.staticstics.mainStatistics.domains,
    })
)(StatisticsBlockDomainNameDashboard);

