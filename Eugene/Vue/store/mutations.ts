import { MutationTree } from 'vuex';
import { User, UsersFilters } from '@/types';
import { UsersListState } from '@/store/modules/users/list/types';

const mutations: MutationTree<UsersListState> = {
    setListItems(state, items: User[]) {
        state.listItems = items;
    },
    setTotalAmount(state, amount: number) {
        state.totalAmount = amount;
    },
    setFilters(state, filters: UsersFilters) {
        state.filters = Object.assign({}, state.filters, filters);
    },
    setLoading(state, loading: boolean) {
        state.loading = loading;
    },
    updateUser(state, user: User) {
        const items = state.listItems;

        if (items && items.length) {
            state.listItems = items.map((item: User) => {
                if (item.id === user.id) {
                    return user;
                }
                return item;
            });
        }
    },
};

export default mutations;
