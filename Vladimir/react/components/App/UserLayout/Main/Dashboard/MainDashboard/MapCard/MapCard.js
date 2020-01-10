import React, {Component} from 'react';
import './MapCard.scss';
import Card from "../../../../../../Ui/Card";
import MapDrillDownToCountries from "./Chart/MapDrillDownToCountries";

class MapCard extends Component {

    render() {
        return (
            <Card
                title='Map of incidents'
                body={<div><MapDrillDownToCountries /></div>}
                isHiddenBody={true}
            ></Card>
        );
    }
}

export default MapCard;

