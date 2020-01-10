import React, {Component} from 'react';
import { Switch, Route } from 'react-router-dom';
import MainDashboard from './Dashboard/MainDashboard';
import MarketplacesDashboard from './Dashboard/MarketplacesDashboard';
import DomainNamesDashboard from './Dashboard/DomainNamesDashboard';
import WebsitesDashboard from './Dashboard/WebsitesDashboard';
import ImageRecognitionDashboard from './Dashboard/ImageRecognitionDashboard';
import SocialMediaDashboard from './Dashboard/SocialMediaDashboard';
import './Main.scss';

class Main extends Component {
    render() {
        return (
            <main>
                <Switch>
                    <Route exact path='/' component={ MainDashboard } />
                    <Route exact path='/market-places/dashboard/' component={ MarketplacesDashboard } />
                    <Route exact path='/domains/dashboard/' component={ DomainNamesDashboard } />
                    <Route exact path='/websites/dashboard/' component={ WebsitesDashboard } />
                    <Route exact path='/image-recognition/dashboard/' component={ ImageRecognitionDashboard } />
                    <Route exact path='/social-media/dashboard/' component={ SocialMediaDashboard } />
                </Switch>
            </main>
        );
    }
}

export default Main;

