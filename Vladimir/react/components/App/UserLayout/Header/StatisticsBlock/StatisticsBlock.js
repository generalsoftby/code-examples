import React, {Component} from 'react';
import { Switch, Route } from 'react-router-dom';
import StatisticsBlockDefault from './Page/StatisticsBlockDefault';
import StatisticsBlockMarketplacesDashboard from './Page/StatisticsBlockMarketplacesDashboard';
import StatisticsBlockDomainNameDashboard from './Page/StatisticsBlockDomainNameDashboard';
import StatisticsBlockSearchEnginesDashboard from './Page/StatisticsBlockSearchEnginesDashboard';
import StatisticsBlockImageRecognitionDashboard from './Page/StatisticsBlockImageRecognitionDashboard';
import StatisticsBlockSocialMediaDashboard from './Page/StatisticsBlockSocialMediaDashboard';

class StatisticsBlock extends Component {

    render() {
        return (
            <div>
                <Switch>
                    <Route exact path='/' component={ StatisticsBlockDefault } />
                    <Route exact path='/market-places/dashboard/' component={ StatisticsBlockMarketplacesDashboard } />
                    <Route exact path='/domains/dashboard/' component={ StatisticsBlockDomainNameDashboard } />
                    <Route exact path='/websites/dashboard/' component={ StatisticsBlockSearchEnginesDashboard } />
                    <Route exact path='/image-recognition/dashboard/' component={ StatisticsBlockImageRecognitionDashboard } />
                    <Route exact path='/social-media/dashboard/' component={ StatisticsBlockSocialMediaDashboard } />
                    <Route component={ StatisticsBlockDefault }/>
                </Switch>
            </div>
        );
    }
}

export default StatisticsBlock;

