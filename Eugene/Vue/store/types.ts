import { User, UsersFilters } from '@/types';

export interface UsersListState {
    listItems: null | User[];
    totalAmount: number;
    loading: boolean;
    filters: UsersFilters;
}
