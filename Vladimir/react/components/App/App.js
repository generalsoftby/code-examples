import React, {Component} from 'react';
import { connect } from 'react-redux';
import UserLayout from './UserLayout';
import Login from './Login';
import { updateToken } from "../../actions/token/updateToken";
import './App.scss';

//import Header from 'components/App/Header/Header';
//import Icons from 'uikit/dist/js/uikit-icons';
//UIkit.use(Icons);

//import '../../node_modules/uikit/dist/css/uikit.min.css';
//import '../../node_modules/uikit/dist/js/uikit.min.js';
//import '../../node_modules/uikit/dist/js/uikit-icons.min.js';

//UIkit.use(Icons);

//uikit-core.min
//import reducer from "../../reducers";
//import { createStore } from "redux";

//const initialState = { tech: "React 16.5 " };
//const store = createStore(reducer, initialState);



class App extends Component {

    componentDidMount() {
        this.props.dispatch(updateToken());
    }

    render() {
        const authUserHash = this.props.currentUser.authUserHash;

        return (
            <div className="AppGorodissky">
                { authUserHash ? <UserLayout /> : <Login /> }
            </div>
        )
    }
}

export default connect(
    state => ({
        currentUser: state.currentUser
    }),
    dispatch => ({
        onChangeToken: (token) => {
            dispatch({ type: 'CHANGE_TOKEN', payload: token })
        },
        dispatch
    })
)(App);

