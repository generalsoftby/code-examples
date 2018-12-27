'use strict';

angular
    .module('app.contacts')
    .factory('ContactsDynamicFiltersService', ContactsDynamicFiltersService);

function ContactsDynamicFiltersService() {

    var ContactsDynamicFiltersService = function (selectedFilters) {
        this.selectedFilters = selectedFilters;

        this.table = [];

        this.updateRows();
    };

    ContactsDynamicFiltersService.prototype = {
        getFilters: function() {
            return this.selectedFilters;
        },

        getFilterSize: function(filterName) {
            return filterName === 'q' ? 3 : 1;
        },

        addFilter: function(filterName) {
            if (filterName === 'q') {
                this.selectedFilters.unshift(filterName);
            } else {
                this.selectedFilters.push(filterName);
            }
            this.updateRows();
        },

        removeFilter: function(filterName) {
            var index = _.indexOf(this.selectedFilters, filterName);
            if (index !== -1) {
                this.selectedFilters.splice(index, 1);
            }
            this.updateRows();
        },

        isSelected: function(filterName) {
            return _.indexOf(this.selectedFilters, filterName) !== -1;
        },

        getRows: function() {
            return this.table;
        },

        updateRows: function() {
            this.table = [[]];
            var index = 0;
            var sum = 0;
            var self = this;
            angular.forEach(this.selectedFilters, function(item){
                if (sum + self.getFilterSize(item) > 7) {
                    ++index;
                    self.table[index] = [];
                    sum = 0;
                }
                self.table[index].push(item);
                sum += self.getFilterSize(item);
            });
        }
    };

    return ContactsDynamicFiltersService;
}
