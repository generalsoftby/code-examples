import React, {Component} from 'react';
import './Header.scss';
import logo from './logo.svg';
import RightBlock from './RightBlock';
import StatisticsBlock from './StatisticsBlock';

class Header extends Component {

    render() {
        return (
            <header className="gr-header">
                <div data-uk-grid>
                    <div className="logo-container">
                        <img className="logo-img" src={logo} alt="logo" />
                    </div>
                    <div className="uk-width-expand gr-header-content">
                        <div data-uk-grid>
                            <div className="uk-width-expand">
                                <StatisticsBlock />
                            </div>
                            <div className="uk-width-auto header-right-block">
                                <RightBlock />
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        );

    }
}

export default Header;

