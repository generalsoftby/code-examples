import React, {Component} from 'react';
import './ProductCard.scss';
import Card from "../../../../../../Ui/Card";

class ProductCard extends Component {

    render() {
        return (
            <Card
                title='Statistics by product'
                body={<div></div>}
            ></Card>
        );
    }
}

export default ProductCard;

