import { async, ComponentFixture, inject, TestBed } from '@angular/core/testing';
import { BsModalService }                           from 'ngx-bootstrap';

import { ResultTableComponent } from './result-table.component';
import { AppModule }            from '../../../app.module';
import { TableSettingsService } from '../../services/tableSettings.service';

describe('ResultTableComponent', () => {
    let component: ResultTableComponent;
    let fixture: ComponentFixture<ResultTableComponent>;
    let tableSettings: TableSettingsService;
    let instance: ResultTableComponent;

    const events = require('../../../../assets/events.json');

    beforeEach(async(() => {
        TestBed.configureTestingModule({
            declarations: [],
            imports: [AppModule],
            providers: [TableSettingsService]
        })
            .compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ResultTableComponent);
        tableSettings = TestBed.get(TableSettingsService);
        component = fixture.componentInstance;
        component.pagination = tableSettings.pagination;
        fixture.detectChanges();
        instance = fixture.componentInstance;
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('should have columns', () => {
        expect(component.columns).toBeDefined();
    });

    it('should have pagination settings', () => {
        expect(component.pagination).toBeDefined();
    });

    it('should have table settings', () => {
        expect(component.maxSize).toBeDefined();
        expect(component.numPages).toBeDefined();
        expect(component.length).toBeDefined();
        expect(component.config).toBeDefined();
    });

    it('Columns should be shown', () => {
        let compiled = fixture.debugElement.nativeElement;
        let textContent = compiled.querySelector('.results-table').textContent;

        expect(textContent).toContain('Timestamp');
        expect(textContent).toContain('Action');
        expect(textContent).toContain('Outcome');
        expect(textContent).toContain('Source');
        expect(textContent).toContain('Destination');
        expect(textContent).toContain('Subject');
        expect(textContent).toContain('Subject type');
        expect(textContent).toContain('Details');
    });

    it('should emit all events', () => {
        const paginateEvent = {
            page: 11,
            perPage: 10
        };
        const sortEvent = {
            field: 'test',
            type: 'asc'
        };

        instance.paginate.subscribe(event => {
            expect(event).toEqual(paginateEvent);
        });

        instance.sort.subscribe(event => {
            expect(event).toEqual(sortEvent);
        });

        instance.sort.emit(sortEvent);
        instance.paginate.emit(paginateEvent);
    });

    it('should set data', () => {
        const response = {
            total: 100,
            totalPages: 10,
            page: 2,
            data: events.hits.hits
        };

        instance.setData(response);

        expect(instance.pagination.page).toEqual(response.page);
        expect(instance.data).toEqual(response);
    });

    it('should not emit event in case total = 0', () => {
        const response = {
                total: 0,
                totalPages: 0,
                page: 0,
                data: events.hits.hits
            },
            column = {
                name: 'action',
                sort: true
            }
        ;

        spyOn(instance.sort, 'emit');

        instance.setData(response);
        instance.changeSorting(column);

        expect(instance.sort.emit).toHaveBeenCalledTimes(0);
    });

    it('should emit sorting event in case total is not 0', () => {
        const response = {
                total: 10,
                totalPages: 1,
                page: 1,
                data: events.hits.hits
            },
            column = {
                name: 'action',
                sort: true
            }
        ;

        spyOn(instance.sort, 'emit');

        instance.setData(response);
        instance.changeSorting(column);
    });

    it('should not call pagination for the same page', () => {
        spyOn(instance.paginate, 'emit');

        instance.changePage({
            page: 1,
            itemsPerPage: 15,
        });

        expect(instance.paginate.emit).toHaveBeenCalledTimes(0);
    });

    it('should not call pagination for another page', () => {
        spyOn(instance.paginate, 'emit');

        instance.changePage({
            page: 2,
            itemsPerPage: 15,
        });

        expect(instance.paginate.emit).toHaveBeenCalledTimes(1);
    });

    it('should show details window', inject([BsModalService], (modal: BsModalService) => {
        spyOn(modal, 'show');

        instance.showDetails({raw: {}});

        expect(modal.show).toHaveBeenCalled();
    }));
});
