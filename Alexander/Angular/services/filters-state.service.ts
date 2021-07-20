import { Injectable } from '@angular/core';

import { SearchRequest } from '../request/search.request';

/**
 * Save current state of the filters to pass between components.
 */
@Injectable()
export class FiltersStateService {

    /**
     * Currently stored request
     */
    private request: SearchRequest;

    /**
     * Gets currently set request
     *
     * @return {SearchRequest} Currently set request
     */
    get(): SearchRequest {
        return this.request;
    }

    /**
     * Sets the request
     *
     * @param request
     */
    set(request: SearchRequest): void {
        this.request = request;
    }

    /**
     * Gets request and removes it from the storage
     *
     * @return {SearchRequest} Current request
     */
    pull(): SearchRequest {
        const request = this.request;
        this.request = undefined;

        return request;
    }

}
