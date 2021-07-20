import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BaseAutocompleteComponent } from './base-autocomplete.component';
import { AppModule }                 from '../../../app.module';

describe('BaseAutocompleteComponent', () => {
    let component: BaseAutocompleteComponent;
    let fixture: ComponentFixture<BaseAutocompleteComponent>;

    beforeEach(async(() => {
        TestBed.configureTestingModule({
            imports: [
                AppModule
            ],
            declarations: []
        })
            .compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(BaseAutocompleteComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('should be closed', () => {
        expect(component.isOpen).toEqual(false);
    });

    it('should emit all events', () => {
        let selectSuggestion = 'test';
        let filtersComponent = fixture.componentInstance;

        filtersComponent.selectSuggestion.subscribe(event => {
            expect(event).toEqual(selectSuggestion);
        });

        filtersComponent.selectSuggestion.emit(selectSuggestion);
    });

    it('should be closed after close', () => {
        component.close();
        expect(component.isOpen).toEqual(false);
    });

    it('should be opened after open', () => {
        component.open();
        expect(component.isOpen).toEqual(true);
    });

    it('should be toggled', () => {
        component.toggleDropdown();
        expect(component.isOpen).toEqual(true);
        component.toggleDropdown();
        expect(component.isOpen).toEqual(false);
    });

    it('should stop events', () => {
        const event = new Event('click');

        spyOn(event, 'preventDefault');
        spyOn(event, 'stopPropagation');

        component.close(event);

        expect(event.stopPropagation).toHaveBeenCalledTimes(1);
        expect(event.preventDefault).toHaveBeenCalledTimes(1);

        component.open(event);

        expect(event.stopPropagation).toHaveBeenCalledTimes(2);
        expect(event.preventDefault).toHaveBeenCalledTimes(2);

        component.toggleDropdown(event);

        expect(event.stopPropagation).toHaveBeenCalledTimes(3);
        expect(event.preventDefault).toHaveBeenCalledTimes(3);
    });

    it('selectValue should be called ', () => {
        spyOn(component.selectSuggestion, 'emit');

        component.selectValue('42', null);

        expect(component.selectSuggestion.emit).toHaveBeenCalled();
    });

    it('selectValue should be called with an event', () => {
        spyOn(component.selectSuggestion, 'emit');

        component.selectValue('42', new Event('click'));

        expect(component.selectSuggestion.emit).toHaveBeenCalled();
    });
});
