import { combineReducers } from 'redux';
import token from './token';
import currentUser from './auth';
import userSettings from './userSettings';
import staticstics from './staticstics';
import clientSettings from './client/clientSettings';

export default combineReducers({
    token,
    currentUser,
    userSettings,
    staticstics,
    clientSettings,
})