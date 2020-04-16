import React, {Component} from 'react';
import './MarketplacesDashboard.scss';
import DateRangeSwitcher from "../../../../../Ui/Form/DateRangeSwitcher";
import IssuesStatisticsCard from "./IssuesStatisticsCard";
import MapCard from "../MainDashboard/MapCard";
import EfficiencyCard from "./EfficiencyCard";
import BrandCard from "./BrandCard";
import TagCard from "./TagCard";
import ProductCard from "../MainDashboard/ProductCard";
import TopSellerCard from "./TopSellerCard";

class MarketplacesDashboard extends Component {
    render() {
        return (
            <div>
                <h1 className="gr-h1">Marketplaces dashboard</h1>
                <div className="container-dashboard-filter-date">
                    <DateRangeSwitcher />
                </div>
                <div className="container-dashboard-content">
                    <section>
                        <IssuesStatisticsCard />
                    </section>
                    <section>
                        <MapCard />
                    </section>
                    <section>
                        <div data-uk-grid className="uk-margin-remove">
                            <div className="card-mr uk-width-1-2">
                                <EfficiencyCard />
                            </div>
                            <div className="card-ml uk-width-1-2">
                                <BrandCard />
                            </div>
                        </div>
                    </section>
                    <section>
                        <div data-uk-grid className="uk-margin-remove">
                            <div className="card-mr uk-width-1-2">
                                <TagCard />
                            </div>
                            <div className="card-ml uk-width-1-2">
                                <ProductCard />
                            </div>
                        </div>
                    </section>
                    <section>
                        <div data-uk-grid className="uk-margin-remove">
                            <div className="card-mr uk-width-1-2">
                                <TopSellerCard />
                            </div>
                            <div className="card-ml uk-width-1-2">
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        );
    }
}

export default MarketplacesDashboard;

