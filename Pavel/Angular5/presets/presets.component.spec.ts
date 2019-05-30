import { async, ComponentFixture, inject, TestBed } from '@angular/core/testing';
import { By }                                       from '@angular/platform-browser';
import { of }                                       from 'rxjs/observable/of';
import { BsModalService }                           from 'ngx-bootstrap';

import { PresetsComponent }    from './presets.component';
import { AppModule }           from '../../../app.module';
import { PresetService }       from '../../services/preset.service';
import { FiltersStateService } from '../../services/filters-state.service';

describe('PresetsComponent', () => {

    const promisedData = {
        data: require('../../../../assets/presets.json')
    };

    let component: PresetsComponent;
    let fixture: ComponentFixture<PresetsComponent>;

    beforeEach(async(() => {
        TestBed.configureTestingModule({
            declarations: [],
            imports: [AppModule],
            providers: [
                PresetService,
            ]
        })
            .compileComponents();

        fixture = TestBed.createComponent(PresetsComponent);
        let debugElement = fixture.debugElement;

        let presetService = debugElement.injector.get(PresetService);
        spyOn(presetService, 'get').and.returnValue(of(promisedData));
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(PresetsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        const compiled = fixture.debugElement.nativeElement,
            table = compiled.querySelector('#presetsList'),
            rows = compiled.querySelectorAll('#presetsList tr'),
            row = compiled.querySelector('#presetsList tr'),
            input = row.querySelector('input')
        ;

        expect(component).toBeTruthy();
        expect(compiled.querySelector('.widget-header').textContent).toContain('PRESETS.TITLE');
        expect(table).toBeTruthy();
        expect(rows.length).toBeGreaterThan(0);
        expect(input).toBeTruthy();
        expect(input.value).toEqual('');
        expect(row.querySelectorAll('button').length).toEqual(2);
    });

    it('A clear button should reset the input', async () => {
        let debugElement = fixture.debugElement,
            input = debugElement.query(By.css('#presetsList tr input')),
            clear = debugElement.query(By.css('#presetsList tr .button-clear'))
        ;

        input.nativeElement.value = 42;
        input.nativeElement.dispatchEvent(new Event('input'));

        expect(fixture.componentInstance.values[0]).toEqual({
            $documentId$: '42'
        });

        clear.nativeElement.dispatchEvent(new Event('click'));

        expect(fixture.componentInstance.values[0]).toEqual({});
    });

    it('An empty value should not raise an error', async () => {
        const debugElement = fixture.debugElement,
            input = debugElement.query(By.css('#presetsList tr input'))
        ;

        input.nativeElement.value = 42;
        input.nativeElement.dispatchEvent(new Event('input'));

        expect(fixture.componentInstance.values[0]).toEqual({
            $documentId$: '42'
        });

        input.nativeElement.value = '';
        input.nativeElement.dispatchEvent(new Event('input'));

        expect(fixture.componentInstance.values[0]).toEqual({
            $documentId$: ''
        });
    });

    it('Select value should work', () => {
        component.selectValue(42, 0, '$documentId$');

        expect(fixture.componentInstance.values[0]).toEqual({
            $documentId$: 42
        });
    });

    it('A search button should raise an error when the input is empty', async () => {
        let debugElement = fixture.debugElement,
            search = debugElement.query(By.css('#presetsList tr .button-search')),
            bsModalService = debugElement.injector.get(BsModalService)
        ;

        spyOn(bsModalService, 'show');

        search.nativeElement.dispatchEvent(new Event('click'));

        expect(bsModalService.show).toHaveBeenCalled();
    });

    it('A search button should not raise an error when the input is filled', async () => {
        let debugElement = fixture.debugElement,
            input = debugElement.query(By.css('#presetsList tr input')),
            search = debugElement.query(By.css('#presetsList tr .button-search'))
        ;

        input.nativeElement.value = 42;
        input.nativeElement.dispatchEvent(new Event('input'));

        spyOn(window, 'alert');

        search.nativeElement.dispatchEvent(new Event('click'));
        expect(window.alert).toHaveBeenCalledTimes(0);
    });

    it('A search button should put the request into the service', inject([FiltersStateService], (filterStateService: FiltersStateService) => {
        let debugElement = fixture.debugElement,
            input = debugElement.query(By.css('#presetsList tr input')),
            search = debugElement.query(By.css('#presetsList tr .button-search'))
        ;

        input.nativeElement.value = 42;
        input.nativeElement.dispatchEvent(new Event('input'));
        search.nativeElement.dispatchEvent(new Event('click'));

        expect(filterStateService.get()).toBeTruthy();
        expect(filterStateService.get().filters).toBeTruthy();
        expect(filterStateService.get().filters.subjects).toBe('42');

    }));
});
