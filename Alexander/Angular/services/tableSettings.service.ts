import { Injectable } from '@angular/core';

import { Pagination, Sorting } from '../types';
import { TranslateService }    from '@ngx-translate/core';

@Injectable()
export class TableSettingsService {

    columns: any[];

    pagination: Pagination = {
        page: 1,
        perPage: 15
    };
    maxSize = 5;
    numPages = 1;
    length = 0;

    config: any = {
        paging: true,
        sorting: {columns: this.columns},
        className: ['table-striped', 'table-bordered']
    };

    defaultSorting: Sorting = {
        field: 'timestamp',
        type: 'desc'
    };

    /**
     * @param {TableSettingsService} translate
     */
    constructor(private readonly translate: TranslateService) {
        this.columns = [
            {title: 'Timestamp', name: 'timestamp', sort: true},
            {title: 'Source', name: 'source', sort: true},
            {title: 'Destination', name: 'destination', sort: true},
            {title: 'Action', name: 'action', sort: true},
            {title: 'Subject', name: 'subject', sort: false},
            {title: 'Subject type', name: 'subjectType', sort: true},
            {title: 'Outcome', name: 'outcome', sort: true},
            {title: 'Details', name: 'details', sort: false}
        ];
    }
}
