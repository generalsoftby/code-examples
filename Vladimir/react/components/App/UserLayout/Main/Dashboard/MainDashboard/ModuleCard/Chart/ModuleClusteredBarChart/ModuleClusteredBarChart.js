import React, {Component} from 'react';
import './ModuleClusteredBarChart.scss';
import ClusteredBarChart from '../../../../../../../../../components/Ui/Chart/ClusteredBarChart';
import ChildClusteredBarChart from '../../../../../../../../../components/Ui/Chart/ClusteredBarChart/Child';
import {connect} from "react-redux";
import Card from "../../../../../../../../Ui/Card";

class ModuleClusteredBarChart extends Component {

    state = {
        indexVisibleChildChart: 0,
    };

    needColumnClick = (colunmId) => {
        //this.props.isVisibleChildChart = true;
        //this.props.indexVisibleChildChart = colunmId;

        this.setState({
            indexVisibleChildChart: colunmId,
        });
    }


    render() {

        let chartData = [];

        if(this.props.statData && this.props.statData.by_module) {
            let byModule = this.props.statData.by_module;
            if(byModule) {
                byModule.series.forEach(function (item, i) {
                    let counter = 0;
                    byModule.data[i].forEach(function (itemL, iL) {
                        counter = counter + itemL;
                    });

                    let label = item;
                    switch (label) {
                        case 'domains':
                            label = 'Domains';
                            break;
                        case 'markeplaces':
                            label = 'Markeplaces';
                            break;
                        case 'search_engines':
                            label = 'Websites';
                            break;
                        case 'image_recognition':
                            label = 'Image recognition';
                            break;
                        case 'social_media':
                            label = 'Social media';
                            break;
                        default:
                            break;
                    }

                    let obj = {
                        "typeY": label,
                        "value": counter,
                    };
                    chartData.push(obj);
                });
            }
        }

        let chartDataChild = [];
        let chartTitleChild = '';
        if(this.props.statData && this.props.statData.by_module) {
            let byModule = this.props.statData.by_module;
            if(byModule) {
                let element = this.state.indexVisibleChildChart;
                chartTitleChild = byModule.series[element];
                byModule.legend.forEach(function (item, i) {

                    let label = item;
                    switch (label) {
                        case 'second_hand':
                            label = 'Second Hand';
                            break;
                        case 'removed':
                            label = 'Removed';
                            break;
                        case 'no_infringements':
                            label = 'No infringement';
                            break;
                        case 'authorized':
                            label = 'Authorized';
                            break;
                        case 'pending':
                            label = 'Pending';
                            break;
                        case 'detected':
                            label = 'Detected';
                            break;
                        default:
                            break;
                    }

                    let obj = {
                        "type": label,
                        "value": byModule.data[element][i],
                    };
                    chartDataChild.push(obj);
                });
            }
        }

        switch (chartTitleChild) {
            case 'domains':
                chartTitleChild = 'Domains';
                break;
            case 'markeplaces':
                chartTitleChild = 'Markeplaces';
                break;
            case 'search_engines':
                chartTitleChild = 'Websites';
                break;
            case 'image_recognition':
                chartTitleChild = 'Image recognition';
                break;
            case 'social_media':
                chartTitleChild = 'Social media';
                break;
            default:
                break;
        }

        /*
        switch (chartTitleChild) {
            case 'second_hand':
                chartTitleChild = 'Second Hand';
                break;
            case 'removed':
                chartTitleChild = 'Removed';
                break;
            case 'no_infringements':
                chartTitleChild = 'No infringement';
                break;
            case 'authorized':
                chartTitleChild = 'Authorized';
                break;
            case 'pending':
                chartTitleChild = 'Pending';
                break;
            case 'detected':
                chartTitleChild = 'Detected';
                break;
            default:
                break;
        }
        */


        return (
            <div>
                <div data-uk-grid className="uk-margin-remove">
                    <div className="uk-width-1-2">
                        <ClusteredBarChart
                            chartData={chartData}
                            chartId="chart-main-dashboard-module-cbc-div"
                            needColumnClick={this.needColumnClick}
                        />
                    </div>
                    <div className="uk-width-1-2">
                        <Card
                            title={chartTitleChild}
                            body={<div>
                                <ChildClusteredBarChart
                                    chartData={chartDataChild}
                                    chartId="chart-main-dashboard-module-child-cbc-div"
                                    needColumnClick={this.needColumnClick}
                                />
                            </div>}
                        ></Card>
                    </div>
                </div>
                <div class='card-stat-modeles-footer'>
                    <div className="uk-flex uk-flex-center">
                        <div className="item-legend item-detected">Detected</div>
                        <div className="item-legend item-pending">Pending</div>
                        <div className="item-legend item-authorized">Authorized</div>
                        <div className="item-legend item-no-ingfrigements">No ingfrigements</div>
                        <div className="item-legend item-removed">Removed</div>
                        <div className="item-legend item-second-hand">Second Hand</div>
                    </div>
                </div>
            </div>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.mainStatistics.total,
    })
)(ModuleClusteredBarChart);

