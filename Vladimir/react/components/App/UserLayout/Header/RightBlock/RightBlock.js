import React, {Component} from 'react';
import './RightBlock.scss';
import {connect} from "react-redux";
import SimpleSelect from '../../../../../components/Ui/Form/Select/SimpleSelect';
import { logoutUser } from "../../../../../actions/auth/logoutUser";
import { userSettingsTopicBrandsValue } from "../../../../../actions/settings/userSettings";
import logo from "../logo.svg";
import StatisticsBlock from "../StatisticsBlock";

class RightBlock extends Component {

    handleChangeBrand(value){
        this.props.dispatch(userSettingsTopicBrandsValue(value));
    }

    btnClickLogout = e => {
        e.preventDefault();
        this.props.dispatch(logoutUser());
    }

    render() {
        return (
            <div>
                <div data-uk-grid className="block-top">
                    <div className="block-top-item uk-width-1-4">
                        <div className="breifcase-container">
                            <span className="label-container">Breifcase (20)</span>
                        </div>
                    </div>
                    <div className="block-top-item uk-width-1-4">
                        <div className="client-name-container">
                            <span className="label-container">{this.props.topicClients.value.label}</span>
                        </div>
                        {/*
                        <SimpleSelect
                            options={this.props.topicClients.options}
                            defaultValue={{label: null, value: null}}
                            value={this.props.topicClients.value}
                            isSearchable={true}
                            isDisabled={true}
                            heightSelect={32}
                        />
                        */}
                    </div>
                    <div className="block-top-item uk-width-1-2">
                        <div className="uk-flex uk-flex-between">
                            <span>
                                <span className="analitic-container">
                                    <span className="label-container">{ this.props.currentUser.userName }</span>
                                </span>
                            </span>
                                <span>
                                <span className="logout-container">
                                    <a className="label-container" onClick={ this.btnClickLogout }>Log out</a>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div data-uk-grid className="block-bottom">
                    <div className="block-bottom-item uk-width-1-4">
                        <SimpleSelect
                            options={this.props.topicBrands.options}
                            defaultValue={{value: null, label: null}}
                            value={this.props.topicBrands.value}
                            isSearchable={true}
                            onChange={value => this.handleChangeBrand(value)}
                        />
                    </div>
                    <div className="block-bottom-item uk-width-1-4">
                        <SimpleSelect
                            options={[
                                { value: 0, label: 'Products' }
                            ]}
                            value={{ label: 'Products', value: 0 }}
                            isSearchable={true}
                            isDisabled={true}
                        />
                    </div>
                    <div className="block-bottom-item uk-width-1-4">
                        <SimpleSelect
                            options={[
                                { value: 0, label: 'Tag' }
                            ]}
                            value={{ label: 'Tag', value: 0 }}
                            isSearchable={true}
                            isDisabled={true}
                        />
                    </div>
                    <div className="block-bottom-item uk-width-1-4">
                        <SimpleSelect
                            options={[
                                { value: 0, label: 'All countries' }
                            ]}
                            value={{ label: 'All countries', value: 0 }}
                            isSearchable={true}
                            isDisabled={true}
                        />
                    </div>
                </div>

            </div>
        );

    }
}

export default connect(
    state => ({
        currentUser: state.currentUser,
        topicClients: state.userSettings.topicClients,
        topicBrands: state.userSettings.topicBrands,
    })
)(RightBlock);


