import { UsersListState } from '@/store/modules/users/list/types';

const state: UsersListState = {
    listItems: null,
    totalAmount: 0,
    loading: false,
    filters: {
        page: 1,
        perPage: 10,
    },
};

export default state;
