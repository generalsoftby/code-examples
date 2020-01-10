import React, {Component} from 'react';
import './Card.scss';

class Card extends Component {

    state = {
        isBodyVisible: this.props.isHiddenBody ? false : true,
    };

    onClickIconToggle = e => {
        this.setState({ isBodyVisible: !this.state.isBodyVisible });
    }

    render() {
        return (
            <div className="gr-card uk-width-1-1">
                <div className="gr-card-header">
                    <span
                        onClick={ this.onClickIconToggle }
                        className={ 'gs-toggle-icon' + (this.state.isBodyVisible ? ' gs-toggle-icon-visible' : ' gs-toggle-icon-invisible') }
                    ></span>
                    <div data-uk-grid>
                        <div className="uk-width-expand gr-card-caption">
                            { this.props.title }
                        </div>
                        <div className="uk-width-auto"></div>
                    </div>
                </div>
                <div
                     className={ 'gr-card-body ' + (this.state.isBodyVisible ? ' ' : ' uk-hidden') }
                >
                    { this.props.body }
                </div>
            </div>
        );

    }
}

export default Card;


