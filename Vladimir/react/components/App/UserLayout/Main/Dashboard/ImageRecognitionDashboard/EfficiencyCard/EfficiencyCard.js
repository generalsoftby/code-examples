import React, {Component} from 'react';
import './EfficiencyCard.scss';
import EfficiencyAnimatedTimeLinePieChart from './Chart/EfficiencyAnimatedTimeLinePieChart';
import EfficiencySimplePieChart from './Chart/EfficiencySimplePieChart';
import EfficiencySolidGauge from './Chart/EfficiencySolidGauge';
import Card from "../../../../../../Ui/Card";

class EfficiencyCard extends Component {

    state = {
        isVisibleSimplePie: false,
        isVisibleTimeLine: false,
        isVisibleSolidGauge: true,
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

    render() {
        return (
            <Card
                title='Efficiency'
                body={<div>
                        <div className='gr-card-panel'>
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
                        { this.state.isVisibleSimplePie &&
                            <div>
                                <EfficiencySimplePieChart />
                            </div>
                        }
                        { this.state.isVisibleTimeLine &&
                            <div>
                                <EfficiencyAnimatedTimeLinePieChart />
                            </div>
                        }
                        { this.state.isVisibleSolidGauge &&
                            <div>
                                <EfficiencySolidGauge />
                            </div>
                        }
                    </div>}
            ></Card>
        );
    }
}

export default EfficiencyCard;

