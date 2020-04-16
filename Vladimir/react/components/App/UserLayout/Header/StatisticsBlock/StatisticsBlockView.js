import React, {Component} from 'react';
import './StatisticsBlock.scss';

const statCaptions = {
    'issues_count': 'Total issues',
    'notices_sent': 'Total notices sent',
    'risk_high': 'High risk',
    'risk_medium': 'Medium risk',
    'risk_low': 'Low risk',
    'total_removed': 'Total removed',
};

class StatisticsBlockView extends Component {

    _renderStatItem = (item, key) => {
        return (
            <li key={key}>
                <span className="stat-label">{ item.caption }</span>
                <span className="stat-value">{ item.counter }</span>
            </li>
        );
    }

    render() {

        return (
            <div>
                  {this.props.itemStatistics && this.props.itemStatistics.length > 0 &&
                    <ul className="uk-flex header-statistic">
                        {this.props.itemStatistics.map((item, key) =>
                            this._renderStatItem(item, key)
                        )}
                    </ul>
                }
            </div>

        );
    }
}

export default StatisticsBlockView;

