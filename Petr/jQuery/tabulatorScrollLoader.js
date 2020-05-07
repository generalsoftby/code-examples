const mutateFields = require('./tabulatorFiledsMutators').mutateFields;
const numberFormatting = require('../helpers/numberFormatting');
const showAlert = require('../alerts/index');
const TabulatorScrollLoader = class {
    constructor(tableSelector,
                loader,
                summaryLine,
                divScroller,
                filterForm,
                fieldForMutate=[],
                loadUrl,
                runWithAddItems=undefined,
                runAfterLoadDone=undefined,
                runAfterAll=undefined
    ) {
        this.page = 1;
        this.lastPage;
        this.loadingRun = false;
        this.end = false;
        this.scrollTopScreen;
        this.scrollLeftScreen;
        this.summaryLine = summaryLine;
        this.loadUrl = loadUrl;
        this.loader = loader;
        this.divScroller = divScroller;
        this.tableSelector = tableSelector;
        this.filterForm = filterForm;
        this.fieldForMutate = fieldForMutate;
        this.runWithAddItems = runWithAddItems;
        this.runAfterLoadDone = runAfterLoadDone;
        this.runAfterAll = runAfterAll;
        this.currentSort = undefined;
        this.groupSort = undefined;

        if (!this.loadUrl) {
            console.error('option loadUrl is required');
        }

        this.setGroupSort = this.setGroupSort.bind(this);
    }

    setPage = (number) => {
        this.page = number;
    }

    setSort = (sort) => {
        this.currentSort = sort;
    }

    setGroupSort = (sort) =>{
        this.groupSort = sort;
    }

    getLastPage() {
        return this.lastPage;
    }

    bindEvents = () =>{
        $(window).scroll(() =>{
            if(!this.end && $(window).scrollTop() + $(window).height() >= $(document).height() && !this.loadingRun && this.lastPage) {
                this.page++;
                this.loadingRun = true;
                this.loadMoreData(this.page);
            }
        });
    }

    loadMoreData = (page = this.page, fromRefresh = false,) => {

        let sorts = [];

        if (this.groupSort) {
            sorts = [...this.groupSort];
        }

        if (this.currentSort) {
            sorts.push(this.currentSort);
        }

        const data = {
            "page": page,
            "refresh": fromRefresh,
            "sorts":sorts
        };

        $.ajax(
            {
                url: this.loadUrl,
                type: "get",
                data: data,
                beforeSend: () => {
                    if (!fromRefresh) {
                        this.loader.show();
                    }
                    this.summaryLine.find('i').show();
                }
            })
            .done((data)=>{
                if (this.runAfterLoadDone) {
                    this.runAfterLoadDone()
                }
                sorts.length = 0;
                this.scrollTopScreen = this.divScroller.scrollLeft();
                this.scrollTopScreen = $(window).scrollTop();
                if (fromRefresh) {
                    $(this.tableSelector).tabulator('setData', []);
                }
                this.addItems(data);
                if (this.runAfterAll) {
                    this.runAfterAll()
                }
            })
            .fail(function(jqXHR, ajaxOptions, thrownError)
            {
                showAlert('Error. More details in console', 'danger');
                const responseText = jQuery.parseJSON(jqXHR.responseText);
                console.error(responseText);
            });
    }

    addItems = (data) => {
        $(this.tableSelector).tabulator("addData", this.fieldForMutate.length ? mutateFields(data.items, this.fieldForMutate) : data.items);
        this.summaryLine.find('i').hide();

        if (this.summaryLine) {
            $('.items-count', this.summaryLine).html($(this.tableSelector).tabulator('getRows').length);

            if (data.counters) {
                $('.records-count', this.summaryLine).html(numberFormatting(data.counters.count_records));

                $('.total-cost', this.summaryLine).html(numberFormatting(data.counters.sum_orders_cost));
                $('.total-debt', this.summaryLine).html(numberFormatting(data.counters.sum_orders_debt));

                $(".total-cost").html(numberFormatting(data.counters.total_cost));
                $(".total-free").html(numberFormatting(data.counters.externalTotalFree));
            }
        }

        if (data.total_count) {
            this.summaryLine.find('.items-total').html(numberFormatting(data.total_count));
        }

        if (data.total_cost) {
            $('.total-cost', this.summaryLine).html(numberFormatting(data.total_cost));
        }

        if (data.lastPage) {
            this.lastPage = data.lastPage;
        }

        this.loadingRun = false;

        this.end = this.lastPage === this.page;

        if (this.lastPage < this.page) {
            this.end = true;
            this.page = this.lastPage;
        }

        if (this.scrollTopScreen > 0 || this.scrollLeftScreen > 0){
            $(window).scrollTop(this.scrollTopScreen);
            this.divScroller.scrollLeft(this.scrollLeftScreen);
            this.scrollTopScreen = 0;
            this.scrollLeftScreen = 0;
        }

        this.loader.hide();

        if (this.runWithAddItems) {
            this.runWithAddItems()
        }

        $(this.tableSelector).trigger('tabulator-added-data');
    }

    searchTableRow = (e) => {
        var keycode = e.keyCode || e.which;
        if(keycode == '13' && !this.loadingRun) {
            e.preventDefault();
            this.loadingRun = true;
            let form = this.filterForm;
            let url = form.attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                beforeSend: () => {
                    this.summaryLine.find('.order-summary-line-value').html('0');
                    this.summaryLine.find('.order-summary-line-value').hide();
                    this.summaryLine.find('i').show();
                    this.loader.show();
                    $(this.tableSelector).tabulator('setData',[]);
                },
                success: () => {
                    this.page = 1;
                    this.loadMoreData();
                }
            });
        }
    }

    refreshTable = (e) => {
        let form = this.filterForm;
        let url = form.attr('action');
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            beforeSend: () => {
                this.summaryLine.find('i').show();
            },
            success:(data) => {
                this.loadMoreData(this.page, true)
            }
        });
    }
};

module.exports = TabulatorScrollLoader;