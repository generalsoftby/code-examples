import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';

/**
 * Autocomplete component
 */
@Component({
    selector: 'base-autocomplete',
    templateUrl: './base-autocomplete.component.html',
    styleUrls: ['./base-autocomplete.component.scss']
})
export class BaseAutocompleteComponent implements OnInit {

    @Input() suggestions: string[];

    @Output() selectSuggestion: EventEmitter<string> = new EventEmitter<string>();
    @Output() afterInit: EventEmitter<this> = new EventEmitter();

    isOpen = false;

    constructor() {
    }

    /**
     * Lifecycle hook emits event with 'this' for dynamic views
     */
    ngOnInit() {
        this.afterInit.emit(this);
    }

    /**
     * Toggles dropdown with autocomplete
     *
     * @param {Event} [event] An event that will be stopped
     */
    toggleDropdown(event?: Event): void {
        event && this.stopEvent(event);
        this.isOpen = !this.isOpen;
    }

    /**
     * Opens dropdown
     *
     * @param {Event} [event] An event that will be stopped
     */
    open(event?: Event): void {
        event && this.stopEvent(event);
        this.isOpen = true;
    }

    /**
     * Closes dropdown
     *
     * @param {Event} [event] An event that will be stopped
     */
    close(event?: Event): void {
        event && this.stopEvent(event);
        this.isOpen = false;
    }

    /**
     * Selects a value from autocomplete and closes dropdown
     *
     * @param {string} value Value that should be selected
     * @param {Event} [event] An event that will be stopped
     */
    selectValue(value: string, event?: Event): void {
        event && this.stopEvent(event);
        this.selectSuggestion.emit(value);
        this.isOpen = false;
    }

    /**
     * Stops an event
     *
     * @param {Event} [event] An event that will be stopped
     */
    private stopEvent(event: Event): void {
        event.stopPropagation();
        event.preventDefault();
    }
}
