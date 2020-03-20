import Vue from 'vue';
import Component from 'vue-class-component';

import { ListApiResponse } from '@/types/api';

@Component
export default class PaginatedList<T = any> extends Vue {
    page = 1;

    perPage = 10;

    items: T[] = [];

    total = 0;

    loading = false;

    changePage(page: number): void {
        this.page = page;
        this.getItems();
    }

    changePerPage(perPage: number): void {
        this.perPage = perPage;
        this.page = 1;
        this.getItems();
    }

    async fetchItems(): Promise<ListApiResponse<T>> {
        return {
            page: 1,
            pageSize: 10,
            results: [],
            totalResults: 0,
            totalPages: 0,
        };
    }

    processApiResponse(res: ListApiResponse<T>): ListApiResponse<T> {
        this.page = res.page;
        this.perPage = res.pageSize;
        this.total = res.totalResults;
        this.items = res.results;

        return res;
    }

    async getItems(): Promise<ListApiResponse<T>> {
        this.loading = true;
        let res = await this.fetchItems();
        res = this.processApiResponse(res);
        this.loading = false;

        return res;
    }
}
