import React, {Component} from 'react';
import { Link, useRouteMatch } from 'react-router-dom';
import './LeftSidebar.scss';
import {connect} from "react-redux";

class LeftSidebar extends Component {

    _renderMenuItem = (item, key) => {
        if(item.children && item.children.length) {
            return (
                <li className="uk-parent" key={key}>
                    <a href="/">{ item.display }</a>
                    <ul className="uk-nav-sub">
                        {item.children.map((child, childKey) =>
                            <LeftSidebarMenuLink key={childKey} to={child.url} label={child.display} />
                        )}
                    </ul>
                </li>
            );
        }
        else{
            return <LeftSidebarMenuLink key={key} to={ item.url } label={ item.display } />;
        }
    }

    render() {
        return (
            <div className="container-left-sidebar">
                <ul className="uk-nav-default list-parent" data-uk-nav="multiple: true">
                    <LeftSidebarMenuLink  key={1000}  to="/" label="Main dashbord" activeOnlyWhenExact={true} />
                    {this.props.menu.map((item, key) =>
                        this._renderMenuItem(item, key)
                    )}

                    {/*
                    <LeftSidebarMenuLink to="/dashboard/domain-names" label="Domain names" />
                    <LeftSidebarMenuLink to="/dashboard/marketplaces" label="Marketplaces" />
                    <LeftSidebarMenuLink to="/dashboard/websites" label="Websites" />
                    <LeftSidebarMenuLink to="/dashboard/image-recognition" label="Image recognition" />
                    <LeftSidebarMenuLink to="/dashboard/social-media" label="Social media" />

                    <li><a href="/">Clustering</a></li>
                    <li><a href="/">Email</a></li>
                    <li className="uk-parent">
                        <a href="/">Setup</a>
                        <ul className="uk-nav-sub">
                            <li><a href="/">Brands</a></li>
                            <li><a href="/">Themes</a></li>
                            <li><a href="/">Stems</a></li>
                            <li><a href="/">Import queues</a></li>
                        </ul>
                    </li>
                    <li className="uk-parent">
                        <a href="/">Administration</a>
                        <ul className="uk-nav-sub">
                            <li><a href="/">Client</a></li>
                            <li><a href="/">Client User</a></li>
                            <li><a href="/">Client Log</a></li>
                            <li><a href="/">Analytic User</a></li>
                            <li><a href="/">Analytic Log</a></li>
                            <li><a href="/">Analytic statistics</a></li>
                        </ul>
                    </li>
                    <li><a href="/">Log out</a></li>
                    */}
                </ul>
            </div>
        );

    }
}

let LeftSidebarMenuLink = ({ label, to, activeOnlyWhenExact }) => {
    let match = useRouteMatch({
        path: to,
        exact: activeOnlyWhenExact
    });

    return (
        <li>
            <Link className={match ? "active" : ""} to={to}>{label}</Link>
        </li>
    );
}

export default connect(
    state => ({
        menu: state.userSettings.leftSidebarMenu,
    })
)(LeftSidebar);


