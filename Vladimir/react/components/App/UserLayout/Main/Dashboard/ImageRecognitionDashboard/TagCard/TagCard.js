import React, {Component} from 'react';
import './TagCard.scss';
import TagSolidGauge from "./Chart/TagSolidGauge";
import TagSimplePieChart from "./Chart/TagSimplePieChart";
import TagAnimatedTimeLinePieChart from "./Chart/TagAnimatedTimeLinePieChart";
import Card from "../../../../../../Ui/Card";

class TagCard extends Component {

    state = {
        isVisibleSimplePie: true,
        isVisibleTimeLine: false,
        isVisibleSolidGauge: false,
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
                title='Statistics by tag'
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
                                <TagSimplePieChart />
                            </div>
                        }
                        { this.state.isVisibleTimeLine &&
                            <div>
                                <TagAnimatedTimeLinePieChart />
                            </div>
                        }
                        { this.state.isVisibleSolidGauge &&
                            <div>
                                <TagSolidGauge />
                            </div>
                        }
                    </div>}
            ></Card>
        );
    }
}

export default TagCard;

