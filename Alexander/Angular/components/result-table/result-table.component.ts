import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { BsModalService, PageChangedEvent }        from 'ngx-bootstrap';

import { ApiResponse, Event, Pagination, Sorting } from '../../types';
import { TableSettingsService }                    from '../../services/tableSettings.service';
import SortingState                                from '../../../shared/utils/sorting.state';
import { DetailsModalWindowComponent }             from '../../../shared/components/details-modal-window/details-modal-window.component';

@Component({
    selector: 'result-table',
    templateUrl: './result-table.component.html',
    styleUrls: ['./result-table.component.scss']
})
export class ResultTableComponent implements OnInit {
    @Output() paginate = new EventEmitter<Pagination>();
    @Output() sort = new EventEmitter<Sorting>();

    pagination: Pagination = {
        perPage: 15,
        page: 1
    };

    data: ApiResponse<Event> = {
        data: null,
        total: 0,
        totalPages: 0,
        page: 1
    };

    rows: Event[] = [];

    columns: any[];

    maxSize: number;
    numPages: number;
    length: number;

    config: any;
    sortingState: SortingState;

    /**
     * @param tableSettings
     * @param bsModalService
     */
    constructor(private readonly tableSettings: TableSettingsService, private readonly bsModalService: BsModalService) {
        this.sortingState = new SortingState('timestamp', 'desc');
    }

    /**
     * Converts the data in a row to the table format
     *
     * @param event Event
     *
     * @return any
     */
    private static convertRow(event: Event): any {
        return {
            raw: event,
            ...event,
            timestamp: new Date(event.timestamp),
            subjects: event.subjects ? event.subjects : []
        };
    }

    /**
     *
     */
    ngOnInit(): void {
        const tableSettings = this.tableSettings;

        this.columns = tableSettings.columns;
        this.maxSize = tableSettings.maxSize;
        this.numPages = tableSettings.numPages;
        this.length = tableSettings.length;
        this.config = tableSettings.config;
    }

    /**
     * Set data to display in the table
     *
     * @param {ApiResponse<Event>} value
     */
    setData(value: ApiResponse<Event>): void {
        this.pagination.page = value.page;

        this.data = value;
        this.rows = value.data.map<Event>(ResultTableComponent.convertRow);
    }

    /**
     * Called upon pagination event
     *
     * @param {PageChangedEvent} event
     */
    changePage(event: PageChangedEvent): void {
        if (event.page === this.data.page) {
            return;
        }

        this.paginate.emit({
            page: event.page,
            perPage: event.itemsPerPage
        });
    }

    /**
     * Called upon sorting event
     *
     * @param column
     */
    changeSorting(column: any): void {
        if (!column.sort || this.data.total === 0) {
            return;
        }

        this.sort.emit(this.sortingState.change(column.name));
    }

    /**
     * Opens the details modal window
     *
     * @param row
     */
    public showDetails(row: any): void {
        this.bsModalService.show(DetailsModalWindowComponent, {
            initialState: {
                event: row.raw
            }
        });
    }
}
