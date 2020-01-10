import React, {Component} from 'react';
import './MainDashboard.scss';
import DateRangeSwitcher from '../../../../../../components/Ui/Form/DateRangeSwitcher';
import IssuesStatisticsCard from "./IssuesStatisticsCard";
import MapCard from "./MapCard";
import EfficiencyCard from "./EfficiencyCard";
import TagCard from "./TagCard";
import BrandCard from "./BrandCard";
import ProductCard from "./ProductCard";
import ModuleCard from "./ModuleCard";

class MainDashboard extends Component {
    render() {
        return (
            <div>
                <h1 className="gr-h1">Main dashboard</h1>
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
                        <ModuleCard />
                    </section>
                </div>
            </div>
        );
    }
}

export default MainDashboard;

