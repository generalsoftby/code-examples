import { Injectable }             from '@angular/core';
import { FormGroup, ValidatorFn } from '@angular/forms';

import * as forEach from 'lodash/forEach';

/**
 * Filters validator service
 * Apply validators to the filters form
 */
@Injectable()
export class FiltersFormValidatorService {

    constructor() {
    }

    /**
     * Sets the validators to the form group
     *
     * @param {FormGroup} formGroup Target form group
     */
    setValidators(formGroup: FormGroup): void {
        formGroup.setValidators([
            this.requiredSubjectTypeValidator(),
            this.requireAnyValueValidator()
        ]);
    }

    /**
     * Validator for subject type filter
     *
     * @return {ValidatorFn} Function validator
     */
    private requiredSubjectTypeValidator(): ValidatorFn {
        return (formControl: FormGroup): { [key: string]: any } => {
            const subjectControl = formControl.controls.subjects;
            const subjectsTypeControl = formControl.controls.subjectsType;

            let error: { [key: string]: any } = null;

            if (!subjectControl || !subjectsTypeControl) {
                return error;
            }

            if (subjectControl.value && !subjectsTypeControl.value) {
                error = {
                    requiredSubject: 'FILTERS.SUBJECT_REQUIRED'
                };
            }

            subjectsTypeControl.setErrors(error);

            return error;
        };
    }

    /**
     * Validator for non empty filters status
     *
     * @return {ValidatorFn} Function validator
     */
    private requireAnyValueValidator(): ValidatorFn {
        return (formControl: FormGroup): { [key: string]: any } => {
            let filledFields = 0;

            forEach(formControl.controls, control => {
                if (control.value) {
                    filledFields++;
                }
            });

            return filledFields ? null : {
                requireAnyValue: 'FILTERS.EMPTY_SEARCH_MSG'
            };
        };
    }
}
