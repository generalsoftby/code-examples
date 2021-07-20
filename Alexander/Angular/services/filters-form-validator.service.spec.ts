import { inject, TestBed }        from '@angular/core/testing';
import { FormControl, FormGroup } from '@angular/forms';

import { FiltersFormValidatorService } from './filters-form-validator.service';

describe('FiltersFormValidatorService', () => {
    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [FiltersFormValidatorService]
        });
    });

    it('should be created', inject([FiltersFormValidatorService], (service: FiltersFormValidatorService) => {
        expect(service).toBeTruthy();
    }));

    it('should run validation', inject([FiltersFormValidatorService], (service: FiltersFormValidatorService) => {
        const formGroup: FormGroup = new FormGroup({
            subjects: new FormControl(),
            subjectsType: new FormControl()
        });

        service.setValidators(formGroup);

        formGroup.setValue({
            subjects: '',
            subjectsType: '',
        });

        expect(formGroup.valid).toBeFalsy();

        formGroup.setValue({
            subjects: 'Tester',
            subjectsType: '',
        });

        expect(formGroup.valid).toBeFalsy();

        formGroup.setValue({
            subjects: 'Tester',
            subjectsType: 'Patient',
        });

        expect(formGroup.valid).toBeTruthy();

        formGroup.setValue({
            subjects: '',
            subjectsType: 'Patient',
        });

        expect(formGroup.valid).toBeTruthy();
    }));

    it('can validate a form without controls', inject([FiltersFormValidatorService], (service: FiltersFormValidatorService) => {
        const formGroup: FormGroup = new FormGroup({
                test: new FormControl(),
                subjects: new FormControl(),
                subjectsType: new FormControl()
            }),

            formGroup2: FormGroup = new FormGroup({
                test: new FormControl(),
                subjectsType: new FormControl()
            }),

            formGroup3: FormGroup = new FormGroup({
                test: new FormControl(),
                subjects: new FormControl(),
            });

        service.setValidators(formGroup);
        service.setValidators(formGroup2);
        service.setValidators(formGroup3);

        formGroup.patchValue({
            test: 42
        });

        formGroup2.patchValue({
            test: 42
        });

        formGroup3.patchValue({
            test: 42
        });

        expect(formGroup.valid).toBeTruthy();
        expect(formGroup2.valid).toBeTruthy();
        expect(formGroup3.valid).toBeTruthy();
    }));
});
