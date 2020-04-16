class SelectAllPanel {
    constructor(tabulatorScrollLoader = null, tableSelector, timer = undefined, runTimer = undefined) {
        this.tabulatorScrollLoader = tabulatorScrollLoader;
        this.timer = timer;
        this.runTimer = runTimer;
        this.wrapper = '.select-all-wrapper';
        this.itemsCount =  '.items-count';
        this.itemsTotal = '.items-total';
        this.selectLink = '.select-all-link';
        this.cancelLink = '.cancel-select-all-link';
        this.tableSelector = tableSelector;
        this.checkAllInp = '[name="checked_rows_all"]';
    }

    bindEvents = () => {
        $(this.selectLink).click(this.handlerLinkClick);
        $(document).on('click', this.cancelLink, () => {
            $(this.checkAllInp).prop('checked', false);
            $(this.checkAllInp).trigger('change');
        });
    }

    show() {
        let re = new RegExp(' ', 'g');
        if (+($(this.itemsCount).html().replace(re,'')) < +($(this.itemsTotal).html().replace(re,''))) {
            $(this.wrapper).show();
        }
    }

    hide() {
        $(this.wrapper).hide();
    }

    setCount(count = undefined) {
        $(this.wrapper+' .select-all-count').html(count)
    }

    handlerLinkClick = () => {
        waitingDialog.show('Wait...');

        if (this.timer) {
            clearInterval(this.timer)
        }

        const tabulatorScrollLoader = this.tabulatorScrollLoader;
        tabulatorScrollLoader.setPage(tabulatorScrollLoader.getLastPage())
        tabulatorScrollLoader.loadMoreData(undefined, true);

        $(this.tableSelector).on('tabulator-added-data', ()=>{
            $(this.checkAllInp).trigger('change');
            $(this.cancelLink).show();
            waitingDialog.hide();
            $(this.selectLink).hide();
            if (this.timer) {
                this.runTimer();
            }

            $(this.tableSelector).off('tabulator-added-data');
        });
    }
}

module.exports = SelectAllPanel;