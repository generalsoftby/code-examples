import React, {Component} from 'react';
import './IssuesStatisticsCard.scss';
import Card from "../../../../../../Ui/Card";
import IssuesStatisticsStackedArea from "./Chart/IssuesStatisticsStackedArea";

class IssuesStatisticsCard extends Component {

    render() {
        return (
            <Card
                title='Issues statistics'
                body={<div><IssuesStatisticsStackedArea /></div>}
            ></Card>
        );
    }
}

export default IssuesStatisticsCard;

