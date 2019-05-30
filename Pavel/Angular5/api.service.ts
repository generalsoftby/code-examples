import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs/Observable';

import { ApiResponse, ElasticSearchResponse, Query } from '../types';
import { SearchRequest }                             from '../request/search.request';

/**
 * Service to interact with the remote storage.
 */
@Injectable()
export class ApiService {

    private readonly RESULTS_LIMIT = 10000;

    /**
     * @param http
     */
    constructor(private readonly http: HttpClient) {
    }

    /**
     * Gets all items from an index
     *
     * @param index
     * @param request
     */
    get<T>(index: string, request: SearchRequest): Observable<ApiResponse<T>> {
        return this.http
            .post<ElasticSearchResponse<T>>(`http://localhost/audit/${index}/_search`, request.getRequestBody(), {params: request.httpParams})
            .map<ElasticSearchResponse<T>, ApiResponse<T>>((response: ElasticSearchResponse<T>) => {
                const total = Math.min(response.hits.total, this.RESULTS_LIMIT);

                return {
                    total,
                    totalPages: Math.ceil(total / request.pagination.perPage),
                    page: request.pagination.page,

                    data: response.hits.hits.map((record: any) => record._source)
                };
            })
        ;
    }

    /**
     * Gets suggestion from the server.
     *
     * @param index
     * @param query
     * @param params
     */
    getSuggestions(index: string, query: Query, params?: { [key: string]: string }): Observable<any> {
        return this.http
            .post(`http://193.70.3.219:9200/audit/${index}/_search`, query, {params})
        ;
    }
}
