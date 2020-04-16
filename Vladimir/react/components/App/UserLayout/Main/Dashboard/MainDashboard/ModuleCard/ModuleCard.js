import React, {Component} from 'react';
import './ModuleCard.scss';
import Card from "../../../../../../Ui/Card";
import ModuleClusteredBarChart from "./Chart/ModuleClusteredBarChart";

class ModuleCard extends Component {

    render() {
        return (
            <Card
                title='Statistics by modules'
                body={<div><ModuleClusteredBarChart /></div>}
            ></Card>
        );
    }
}

export default ModuleCard;

