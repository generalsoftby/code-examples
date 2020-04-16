import React, {Component} from 'react';
import Header from './Header';
import LeftSidebar from './LeftSidebar';
import Main from './Main';
import { userSettings } from "../../../actions/settings/userSettings";
import { updateStatistics } from "../../../actions/statistics/updateStatistics";
import './UserLayout.scss';
import { connect } from "react-redux";

class UserLayout extends Component {

    componentDidMount() {
        this.props.dispatch(userSettings());
        this.props.dispatch(updateStatistics());
    }

    render() {
        return (
            <div className="user-layout">
                <Header></Header>
                <section className="gr-section-content">
                    <div data-uk-grid>
                        <div className="gr-left-sidebar">
                            <LeftSidebar></LeftSidebar>
                        </div>
                        <div className="uk-width-expand gr-content">
                            <Main></Main>
                        </div>
                    </div>
                </section>
            </div>
        );
    }
}

export default connect(
    null
)(UserLayout);
