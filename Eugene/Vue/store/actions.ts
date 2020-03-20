import { ActionTree } from 'vuex';
import { adminHttp } from '@/http';
import { User, UsersFilters } from '@/types';
import { UsersListState } from '@/store/modules/users/list/types';
import { RootState } from '@/store/types';
import { UsersApiResponse } from '@/types/api';
import { AxiosPromise } from 'axios';

const actions: ActionTree<UsersListState, RootState> = {
    async changeFilters({ commit, dispatch }, filters: UsersFilters): Promise<UsersApiResponse> {
        commit('setLoading', true);
        commit('setFilters', filters);

        const items = await dispatch('getItems');
        commit('setLoading', false);

        return items;
    },

    async getItems({ state, commit }): Promise<UsersApiResponse> {
        const { data } = await adminHttp.getUserList(state.filters);

        commit('setListItems', data.results);
        commit('setTotalAmount', data.totalResults);

        return data;
    },

    removeItem(module, user: User): AxiosPromise<void> {
        return adminHttp.removeUser(user.id);
    },
};

export default actions;
