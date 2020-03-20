import { Module } from 'vuex';

import actions from './actions';
import getters from './getters';
import mutations from './mutations';
import state from './state';
import { RootState } from '@/store/types';
import { UsersListState } from '@/store/modules/users/list/types';

const list: Module<UsersListState, RootState> = {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};

export default list;
