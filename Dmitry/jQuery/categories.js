import slug from "slug";

$(function ()
{
    let $blogCategoriesList = $('.blog-categories-list');

    if (!$blogCategoriesList.size())
    {
        return;
    }

    let $blogCategorySettingsContainer = $('.blog-categories-settings-container');
    let blogCategorySettingsLoadingTemplate = $('.blog-categories-settings-loading-template').text();
    let blogCategoryDeleteConfirmationConfig = JSON.parse($('.blog-category-delete-confirmation-config').text());
    let blogCategorySaveConfirmationConfig = JSON.parse($('.blog-category-save-confirmation-config').text());

    let blogCategorySettingsContainerSetContent = (content) =>
    {
        $blogCategorySettingsContainer.html(content);
        $blogCategorySettingsContainer.find('.switch').bootstrapSwitch();
        $blogCategorySettingsContainer.find('.select2').select2({
            minimumResultsForSearch: Infinity,
        });
    };

    $(document).on('click', '.blog-categories-perform-delete', function (e, options = {confirmed: false})
    {
        e.preventDefault();
        let $this = $(this);
        if ($this.closest('.disabled').size())
        {
            return;
        }
        if (!options.confirmed)
        {
            $.vizitkaNotification(blogCategoryDeleteConfirmationConfig)
                .notification()
                .then(() => $this.trigger('click', {confirmed: true}));
            return;
        }
        $.ajax({
            type: 'delete',
            url: $this.attr('href'),
            success: response =>
            {
                let $blogCategoriesListItem = $blogCategoriesList.find('.blog-categories-list-item[data-blog-category-id="' + response.category.id + '"]');
                if ($blogCategoriesListItem.find('.blog-categories-list-item-activetitle:visible').size())
                {
                    $blogCategorySettingsContainer.empty();
                }
                $blogCategoriesListItem.remove();

                notifyService.showMessage('alert', 'topRight', response.message);
            },
            error: xhr =>
            {
                console.error(xhr);
            },
        });
    });

    var isChange = false;

    $(document).on('submit', '.blog-category-settings-form', function (e)
    {
        e.preventDefault();
        let $form = $(this);
        if ($form.data('ajax'))
        {
            return;
        }
        $form.find('.has-error').removeClass('has-error');
        $form.find('[type=submit]').bsButton('loading');
        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: response =>
            {
                notifyService.showMessage('alert', 'topRight', response.message);
                let $categoriesListItem = $blogCategoriesList.find('.blog-categories-list-item[data-blog-category-id="' + response.category.id + '"]');
                if ($categoriesListItem.size())
                {
                    $categoriesListItem.replaceWith(response.row);
                }
                else
                {
                    $blogCategoriesList.append(response.row);
                }

                blogCategorySettingsContainerSetContent(response.settings);
                $blogCategoriesList.find('.switch').bootstrapSwitch();

                isChange = false;
            },
            error: xhr =>
            {
                if ('object' === typeof xhr.responseJSON)
                {
                    for (let key in xhr.responseJSON)
                    {
                        $form.find('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                    }
                }
                if (xhr.responseJSON.address) {
                    notifyService.showMessage('error', 'topRight', xhr.responseJSON.address[0]);
                }
                console.error(xhr);
            },
            complete: () =>
            {
                $form.removeData('ajax');
                $form.find('[type=submit]').bsButton('reset');
            },
        }));
    });

    $(document).on('change keyup', '.blog-category-settings-form', function (e)
    {
        let $form = $(this);
        let $input = $(e.target);

        isChange = true;

        if (!$input.is('input,select,textarea'))
        {
            return;
        }

        if ((e.type == 'keyup') && ($input.attr('type') != 'text'))
        {
            return;
        }

        $form.find('[type=submit]')
            .removeClass('btn-default')
            .addClass('btn-primary')
            .prop('disabled', false);
    });

    $(document).on('click', '.blog-categories-settings-open', function (e, options = {confirmed: false}) {
        e.preventDefault();
        let $this = $(this);

        if (isChange) {
            if (!options.confirmed) {
                $.vizitkaNotification(blogCategorySaveConfirmationConfig)
                    .notification()
                    .then(() => $this.trigger('click', {confirmed: true}));
                return;
            }
            else isChange = false;
        }

        if ($blogCategorySettingsContainer.data('ajax')) {
            $blogCategorySettingsContainer.data('ajax').abort();
        }
        $blogCategorySettingsContainer.html(blogCategorySettingsLoadingTemplate);
        $blogCategorySettingsContainer.data('ajax', $.ajax({
            type: 'get',
            url: $this.attr('href'),
            cache: false,
            success: response => {
                blogCategorySettingsContainerSetContent(response);
            },
            error: xhr => {
                if (xhr.statusText == 'abort') {
                    return;
                }
                console.error(xhr);
                $blogCategorySettingsContainer.empty();
            },
            complete: () => {
                $blogCategorySettingsContainer.removeData('ajax');
            },
        }));
    });

    $(document).on('change switchChange.bootstrapSwitch', '.category-perform-status', function (e)
    {
        let $this = $(this);
        let active = $this.prop('checked') ? 1 : 0;

        $.ajax({
            type: 'post',
            url: $this.data('href'),
            data: {active},
            success: response =>
            {
                notifyService.showMessage(active ? 'alert' : 'error', 'topRight', response);
            },
            error: xhr =>
            {
                console.error(xhr);
            },
        });
    });

    var CategoriesPageService = CategoriesPageService || {};

    dragula([document.getElementById('media-list-target-left')], {
        mirrorContainer: document.querySelector('.media-list-container'),
        moves: function(el, container, handle) {
            return handle.classList.contains('dragula-handle');
        }
    }).on('dragend', function(el) {
        CategoriesPageService.systemsList.saveSequence();
    });

    CategoriesPageService.systemsList = {
        saveSequence: function() {
            var categories = [];

            $('#media-list-target-left > li').each(function(index, el){
                categories.push($(el).data('blog-category-id'))
            });

            $.ajax({
                method: 'POST',
                url: backendPageConfig.saveCategoryUrl,
                dataType: 'json',
                data: {
                    categories: categories
                },
                success: function(response) {
                    CategoriesPageService.showMessage('alert', 'topRight', response.message);
                },
                error: function(response) {
                    CategoriesPageService.showMessage('error', 'topRight', response.message);
                },
            });
        },
    };

    CategoriesPageService.showMessage = function(type, layout, text) {
        notifyService.showMessage(type, layout, text);
    };

    $('.static-page-settings-form').find('.select2').select2({
        minimumResultsForSearch: Infinity,
    });

    $(document).on('submit', '.static-page-settings-form', function (e) {
        e.preventDefault();
        let $form = $(this);
        if ($form.data('ajax')) {
            return;
        }
        $form.find('.has-error').removeClass('has-error');
        $form.find('[type=submit]').bsButton('loading');
        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: response => {
                notifyService.showMessage(response.error ? 'error' : 'alert', 'topRight', response.message);

                let $goToPageButton = $('.url').find('.btn-default');
                let slugPrefix = $('.flag-left-addon').text().replace(/\s+/g, '');
                $goToPageButton.attr('href', slugPrefix + $('.address').val());

                isChange = false;
            },
            error: xhr => {
                if(xhr.responseJSON.page_address !== undefined) {
                    notifyService.showMessage('error', 'topRight', xhr.responseJSON.page_address);
                }
                if ('object' === typeof xhr.responseJSON) {
                    for (let key in xhr.responseJSON) {
                        $form.find('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                    }
                    return;
                }
                console.error(xhr);
            },
            complete: () => {
                $form.removeData('ajax');

                $form.find('[type=submit]')
                    .bsButton('reset')
                    .removeClass('btn-primary')
                    .addClass('btn-default');

                setTimeout(function () {
                    $form.find('[type=submit]').attr('disabled', 'disabled'); // Disables the button correctly.
                }, 0);
            },
        }));
    });

    $(document).on('input change keyup', '.static-page-settings-form', function (e) {
        let $form = $(this);
        let $input = $(e.target);

        isChange = true;

        if (!$input.is('input,select,textarea')) {
            return;
        }

        if ((e.type == 'keyup') && ($input.attr('type') != 'text')) {
            return;
        }

        $form.find('.has-error').removeClass('has-error');
        $form.find('[type=submit]')
            .removeClass('btn-default')
            .addClass('btn-primary')
            .prop('disabled', false);
    });

    $(document).on('click', '.blog-page-settings-open', function (e, options = {confirmed: false}) {
        e.preventDefault();
        let $this = $(this);
        let $pageSetting = $('#page-settings');
        let $blogPageSettingsLoadingTemplate = $('.blog-page-settings-loading-template').text();

        if (isChange) {
            if (!options.confirmed) {
                $.vizitkaNotification(blogItemSaveConfirmationConfig)
                    .notification()
                    .then(() => $this.trigger('click', {confirmed: true}));
                return;
            }
            else isChange = false;
        }

        if ($pageSetting.data('ajax')) {
            $pageSetting.data('ajax').abort();
        }
        $pageSetting.html($blogPageSettingsLoadingTemplate);
        $pageSetting.data('ajax', $.ajax({
            type: 'get',
            url: $this.attr('href'),
            cache: false,
            success: response =>
            {
                $pageSetting.html(response);
                $pageSetting.find('.switch').bootstrapSwitch();
                $pageSetting.find('.select2').select2({ minimumResultsForSearch: Infinity });
            },
            error: xhr => {
                if (xhr.statusText == 'abort') {
                    return;
                }
                console.error(xhr);
                $pageSetting.empty();
            },
            complete: () => {
                $pageSetting.removeData('ajax');
            },
        }));
    });

    $(document).on('change keyup', '.category-name-input', function () {
        let title = $(this).val();
        $('.category-address-input').val(slug(title));
    });

});
