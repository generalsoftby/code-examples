import React, {Component} from 'react';
import './TopSellerCard.scss';
import Card from "../../../../../../Ui/Card";
import TopSellerChart from "./Chart/TopSellerChart";

class TopSellerCard extends Component {

    render() {
        return (
            <Card
                title='Top-10 Sellers'
                body={<div><TopSellerChart /></div>}
            ></Card>
        );
    }
}

export default TopSellerCard;

