import React, {Component} from 'react';
import './BrandCard.scss';
import BrandSimplePieChart from "./Chart/BrandSimplePieChart";
import BrandAnimatedTimeLinePieChart from "./Chart/BrandAnimatedTimeLinePieChart";
import BrandSolidGauge from "./Chart/BrandSolidGauge";
import Card from "../../../../../../Ui/Card";
import SimpleSelect from "../../../../../../Ui/Form/Select";
import {connect} from "react-redux";

class BrandCard extends Component {

    state = {
        isVisibleSimplePie: false,
        isVisibleTimeLine: true,
        isVisibleSolidGauge: false,
        valueFilter: {
            value: 'total',
            label: 'Total',
        },
    };

    onClickIconSimplePie = e => {
        this.setState({
            isVisibleSimplePie: true,
            isVisibleTimeLine: false,
            isVisibleSolidGauge: false,
        });
    }

    onClickIconTimeLine = e => {
        this.setState({
            isVisibleSimplePie: false,
            isVisibleTimeLine: true,
            isVisibleSolidGauge: false,
        });
    }

    onClickIconSolidGauge = e => {
        this.setState({
            isVisibleSimplePie: false,
            isVisibleTimeLine: false,
            isVisibleSolidGauge: true,
        });
    }

    onChangeValueFilter = e => {
        this.setState({
            isVisibleSimplePie: true,
            isVisibleTimeLine: false,
            isVisibleSolidGauge: false,
        });
    }

    handleChangeFilter(value){
        this.setState({ valueFilter: value });
    }

    render() {

        let optionsFilterDiagramm = [];

        if(this.props.statData && this.props.statData.choices) {
            let filterOptions = this.props.statData.choices;
            filterOptions.forEach ((item, key) => {
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
                optionsFilterDiagramm.push({
                    value: item,
                    label: label,
                });
            });
        }

        return (
            <Card
                title='Statistics by brand'
                body={<div>
                        <div className='gr-card-panel'>
                            <div className='uk-flex'>
                                <div className="card-select-diagram">
                                    <SimpleSelect
                                        options={optionsFilterDiagramm}
                                        defaultValue={{label: null, value: null}}
                                        value={this.state.valueFilter}
                                        isSearchable={true}
                                        heightSelect={38}
                                        onChange={value => this.handleChangeFilter(value)}
                                    />
                                </div>
                                <div className='card-switch-diagram'>
                                    <span
                                        className={'item-switch-diagram spc ' + (this.state.isVisibleSimplePie ? 'active' : '')}
                                        onClick={this.onClickIconSimplePie}
                                    ></span>
                                    <span
                                        className={'item-switch-diagram atlpc ' + (this.state.isVisibleTimeLine ? 'active' : '')}
                                        onClick={this.onClickIconTimeLine}
                                    ></span>
                                    <span
                                        className={'item-switch-diagram sg ' + (this.state.isVisibleSolidGauge ? 'active' : '')}
                                        onClick={this.onClickIconSolidGauge}
                                    ></span>
                                </div>
                            </div>
                        </div>
                        { this.state.isVisibleSimplePie &&
                            <div>
                                <BrandSimplePieChart
                                    filter={this.state.valueFilter}
                                ></BrandSimplePieChart>
                            </div>
                        }
                        { this.state.isVisibleTimeLine &&
                            <div>
                                <BrandAnimatedTimeLinePieChart
                                    filter={this.state.valueFilter}
                                ></BrandAnimatedTimeLinePieChart>
                            </div>
                        }
                        { this.state.isVisibleSolidGauge &&
                            <div>
                                <BrandSolidGauge
                                    filter={this.state.valueFilter}
                                ></BrandSolidGauge>
                            </div>
                        }
                    </div>}
            ></Card>
        );
    }
}

export default connect(
    state => ({
        statData: state.staticstics.brandStatistics.total,
    })
)(BrandCard);
