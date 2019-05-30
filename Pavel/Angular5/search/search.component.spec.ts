import { async, ComponentFixture, inject, TestBed } from '@angular/core/testing';
import { By }                                       from '@angular/platform-browser';
import { BsModalService }                           from 'ngx-bootstrap';
import { Observable }                               from 'rxjs';
import { HttpClient }                               from '@angular/common/http';

import { SearchComponent }             from './search.component';
import { AppModule }                   from '../../../app.module';
import { FiltersStateService }         from '../../services/filters-state.service';
import { Action, Pagination, Sorting } from '../../types';
import { SearchRequest }               from '../../request/search.request';
import { ApiService }                  from '../../services/api.service';

describe('SearchComponent', () => {
    let component: SearchComponent;
    let fixture: ComponentFixture<SearchComponent>;

    beforeEach(async(() => {
        TestBed.configureTestingModule({
            declarations: [],
            imports: [AppModule]
        })
            .compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(SearchComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
        const filtersEl = fixture.debugElement.queryAll(By.css('filters-component')),
            tableEl = fixture.debugElement.queryAll(By.css('result-table'));

        expect(filtersEl.length).toEqual(1);
        expect(tableEl.length).toEqual(1);
    });

    it('should get a query from FiltersStateService', () => {
        const filtersStateService: FiltersStateService = TestBed.get(FiltersStateService);

        filtersStateService.set(SearchRequest.fromQuery({
            query: {
                match: {
                    action: Action.CREATE
                }
            }
        }));

        fixture = TestBed.createComponent(SearchComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();

        expect(component).toBeTruthy();
        expect(component.currentRequest).toBeTruthy();
    });

    it('search should start', () => {
        component.performSearch({
            action: Action.CREATE
        });

        expect(component.currentRequest).toBeTruthy();
        expect(component.progressModal).toBeTruthy();
        expect(component.subscription).toBeTruthy();
    });

    it('search should start with all data', () => {
        component.performSearch({
            action: Action.CREATE
        }, {
            perPage: 15,
            page: 1
        }, {
            field: 'timestamp',
            type: 'asc'
        });

        expect(component.currentRequest).toBeTruthy();
        expect(component.progressModal).toBeTruthy();
        expect(component.subscription).toBeTruthy();
    });

    it('incorrect search should raise an error', inject([BsModalService, ApiService], (bsModalService: BsModalService, apiService: ApiService) => {
        spyOn(bsModalService, 'show');

        spyOn(apiService, 'get').and.callFake((): any => {
            return new Observable((observer) => {
                observer.error({statusText: 'It\'s a test!'});
            });
        });

        component.performSearch({});
        expect(component.subscription).toBeTruthy();
        expect(component.currentRequest).toBeTruthy();

        expect(bsModalService.show).toHaveBeenCalledTimes(2);
    }));

    it('pagination should start the search', (): void => {
        const pagination: Pagination = {
                page: 1,
                perPage: 10
            }
        ;

        component.performSearch({});

        spyOn(component, 'performSearch');

        component.paginate(pagination);

        expect(component.performSearch).toHaveBeenCalled();
    });

    it('sort should start the search', (): void => {
        const sort: Sorting = {
                field: 'timestamp',
                type: 'asc',
            }
        ;

        component.performSearch({});

        spyOn(component, 'performSearch');

        component.sort(sort);

        expect(component.performSearch).toHaveBeenCalled();
    });

    it('request could be cancelled', (inject([HttpClient], (http: HttpClient): void => {
        spyOn(http, 'post').and.callFake((): any => {
            return new Observable(() => {

            });
        });


        component.performSearch({});

        expect(component.subscription).toBeTruthy();
        expect(component.progressModal).toBeTruthy();

        const modals = document.body.getElementsByTagName('modal-container'),
            modal = modals.item(modals.length - 1),
            button = modal.getElementsByClassName('btn').item(0);

        button.dispatchEvent(new Event('click'));
        fixture.detectChanges();

        expect(component.subscription).toBeFalsy();
    })));
});
