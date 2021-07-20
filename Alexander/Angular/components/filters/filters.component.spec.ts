import { async, ComponentFixture, inject, TestBed } from '@angular/core/testing';
import { By }                                       from '@angular/platform-browser';
import { of }                                       from 'rxjs/observable/of';
import { BsModalService }                           from 'ngx-bootstrap';

import { FiltersComponent }               from './filters.component';
import { AppModule }                      from '../../../app.module';
import { Action, SubjectType }            from '../../types';
import { FiltersFormAutocompleteService } from '../../services/filters-form-autocomplete.service';

describe('FiltersComponent', () => {
    let component: FiltersComponent;
    let fixture: ComponentFixture<FiltersComponent>;

    beforeEach(async(() => {
        TestBed.configureTestingModule({
            declarations: [],
            imports: [AppModule]
        })
            .compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(FiltersComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('should have Clear button', () => {
        let buttons = fixture.debugElement.queryAll(By.css('.btn'));

        let isContainsText = false;

        for (let number in buttons) {
            if (buttons[number].nativeElement.textContent.trim() === 'COMMON.BUTTON.CLEAR') {
                isContainsText = true;
            }
        }

        expect(isContainsText).toEqual(true);
    });

    it('should have Search button', () => {
        let buttons = fixture.debugElement.queryAll(By.css('.btn'));

        let isContainsText = false;

        for (let number in buttons) {
            if (buttons[number].nativeElement.textContent.trim() === 'COMMON.BUTTON.SEARCH') {
                isContainsText = true;
            }
        }

        expect(isContainsText).toEqual(true);
    });

    it('should contain all filters', () => {
        let sourceEl = fixture.debugElement.queryAll(By.css('#source'));
        let destinationEl = fixture.debugElement.queryAll(By.css('#destination'));
        let subjectEl = fixture.debugElement.queryAll(By.css('#subjects'));
        let subjectTypeEl = fixture.debugElement.queryAll(By.css('.base-select'))[0];
        let fromEl = fixture.debugElement.queryAll(By.css('#from'));
        let toEl = fixture.debugElement.queryAll(By.css('#to'));
        let actionEl = fixture.debugElement.queryAll(By.css('.base-select'))[1];

        expect(sourceEl.length).toEqual(1);
        expect(destinationEl.length).toEqual(1);
        expect(subjectEl.length).toEqual(1);
        expect(subjectTypeEl).toBeTruthy();
        expect(fromEl.length).toEqual(1);
        expect(toEl.length).toEqual(1);
        expect(actionEl).toBeTruthy();
    });

    it('should show error on empty filters', () => {
        component.onSubmit();

        let modal = document.body.getElementsByTagName('modal-container').item(0);

        expect(modal.textContent).toContain('');
    });

    it('should emit all events', () => {
        let changedEvent = {
            name: 'test-name',
            value: 'test-value'
        };

        let performSearchEvent = {
            source: 'test',
            destination: 'test',
            subjectsType: SubjectType.DOCUMENT,
            subjects: 'test',
            action: Action.CREATE,
            from: new Date(),
            to: new Date()
        };

        let filtersComponent = fixture.componentInstance;

        filtersComponent.changed.subscribe(event => {
            expect(event).toEqual(changedEvent);
        });

        filtersComponent.performSearch.subscribe(event => {
            expect(event).toEqual(performSearchEvent);
        });

        filtersComponent.performSearch.emit(performSearchEvent);
        filtersComponent.changed.emit(changedEvent);
    });

    it('should submit', () => {
        component.filters = {
            action: Action.CREATE
        };
        component.ngOnInit();

        spyOn(component.performSearch, 'emit');

        component.onSubmit();
        expect(component.performSearch.emit).toHaveBeenCalled();
    });

    it('should not submit on empty', inject([BsModalService], (bsModalService: BsModalService) => {
        spyOn(component.performSearch, 'emit');
        spyOn(bsModalService, 'show');

        component.onSubmit();
        expect(component.performSearch.emit).toHaveBeenCalledTimes(0);
        expect(bsModalService.show).toHaveBeenCalledTimes(1);
    }));

    it('should clear', () => {
        spyOn(component.filtersForm, 'reset');

        component.clear();

        expect(component.filtersForm.reset).toHaveBeenCalled();
    });

    it('Fields change should call autocomplete service', inject([FiltersFormAutocompleteService], (autocomplete: FiltersFormAutocompleteService) => {
        const field = component.filtersForm.get('source');

        spyOn(autocomplete, 'getSuggestions').and.returnValue(of([1, 2, 3]));
        field.setValue('TMA');

        expect(autocomplete.getSuggestions).toHaveBeenCalled();
    }));

    it('Fields change on empty should call autocomplete service', inject([FiltersFormAutocompleteService], (autocomplete: FiltersFormAutocompleteService) => {
        const field = component.filtersForm.get('source');

        spyOn(autocomplete, 'getSuggestions').and.returnValue(of([]));
        field.setValue('');

        expect(autocomplete.getSuggestions).toHaveBeenCalledTimes(0);
    }));
});
