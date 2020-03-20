import { GetterTree } from 'vuex';
import { UsersListState } from '@/store/modules/users/list/types';
import { RootState } from '@/store/types';

const getters: GetterTree<UsersListState, RootState> = {
    filters(state) {
        return state.filters;
    },
    listItems(state) {
        return state.listItems;
    },
    loading(state) {
        return state.loading;
    },
    totalAmount(state) {
        return state.totalAmount;
    },
};

export default getters;
