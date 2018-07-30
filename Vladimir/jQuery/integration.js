$(function () {
    let $creator = $('.settings-product-category-tabs');

    if (!$creator.size()) {
        return;
    }

    $(document).on('submit', '.order-form', function (e) {
        e.preventDefault();

        let $form = $(this);

        if ($form.data('ajax')) {
            $form.data('ajax').abort();
        }

        $form.find('[type=submit]')
            .button('loading')
            .prop('disabled', true);

        let formData = new FormData($form[0]);

        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            success: (response) => {
                notifyService.showMessage('alert', 'topRight', response);
            },
            error: (xhr) => {
                console.log(xhr);
            },
            complete: () => {
                $form.removeData('ajax');

                $form.find('[type=submit]')
                    .button('reset')
                    .removeClass('btn-primary')
                    .addClass('btn-default');

                setTimeout(() => $form.find('[type=submit]').prop('disabled', true), 0);
            },
        }));
    });

    $(document).on('change keyup switchChange.bootstrapSwitch', '.order-form', function (e) {
        let $form = $(this);
        let $input = $(e.target);

        if (($input.attr('type') == 'file') && (e.type != 'change')) {
            return;
        }

        if (!$input.is('input,select,textarea')) {
            return;
        }

        $input.closest('.form-group')
            .removeClass('has-error');

        $form.find('[type=submit]')
            .removeClass('btn-default')
            .addClass('btn-primary')
            .prop('disabled', false);
    });

    $(document).on('change keyup switchChange.bootstrapSwitch', '.uploader-form', function (e) {
        let $form = $(this);
        let $input = $(e.target);

        if (($input.attr('type') == 'file') && (e.type != 'change')) {
            return;
        }

        if (!$input.is('input,select,textarea')) {
            return;
        }

        $input.closest('.form-group')
            .removeClass('has-error');

        $form.find('[type=submit]')
            .removeClass('btn-default')
            .addClass('btn-primary')
            .prop('disabled', false);
    });

    $(document).on('submit', '.uploader-form', function (e) {
        e.preventDefault();

        let $form = $(this);

        if ($form.data('ajax')) {
            $form.data('ajax').abort();
        }

        $form.find('[type=submit]')
            .button('loading')
            .prop('disabled', true);

        let formData = new FormData($form[0]);

        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            success: (response) => {
                notifyService.showMessage('alert', 'topRight', response);
            },
            error: (xhr) => {
                console.log(xhr);
            },
            complete: () => {
                $form.removeData('ajax');

                $form.find('[type=submit]')
                    .button('reset')
                    .removeClass('btn-primary')
                    .addClass('btn-default');

                setTimeout(() => $form.find('[type=submit]').prop('disabled', true), 0);
            },
        }));
    });

    $(document).on('change keyup switchChange.bootstrapSwitch', '.constructor-form', function (e) {
        let $form = $(this);
        let $input = $(e.target);

        if (($input.attr('type') == 'file') && (e.type != 'change')) {
            return;
        }

        if (!$input.is('input,select,textarea')) {
            return;
        }

        $input.closest('.form-group')
            .removeClass('has-error');

        $form.find('[type=submit]')
            .removeClass('btn-default')
            .addClass('btn-primary')
            .prop('disabled', false);
    });

    $(document).on('submit', '.constructor-form', function (e) {
        e.preventDefault();

        let $form = $(this);

        if ($form.data('ajax')) {
            $form.data('ajax').abort();
        }

        $form.find('[type=submit]')
            .button('loading')
            .prop('disabled', true);

        let formData = new FormData($form[0]);

        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            success: (response) => {
                notifyService.showMessage('alert', 'topRight', response);
            },
            error: (xhr) => {
                console.log(xhr);
            },
            complete: () => {
                $form.removeData('ajax');

                $form.find('[type=submit]')
                    .button('reset')
                    .removeClass('btn-primary')
                    .addClass('btn-default');

                setTimeout(() => $form.find('[type=submit]').prop('disabled', true), 0);
            },
        }));
    });

    // EVENTS

    $(document).on('click', '.remove-creator', function (e, options = {confirmed: false}) {
        e.preventDefault();

        let $this = $(this);

        if ($this.closest('.disabled').size()) {
            return;
        }

        let removeConfirmationConfig = JSON.parse($('.creator-remove-connection-confirmation-config').text());

        if (!options.confirmed) {
            $.vizitkaNotification(removeConfirmationConfig)
                .notification()
                .then(() => $this.trigger('click', {confirmed: true}));
            return;
        }

        $.ajax({
            type: 'delete',
            url: $this.attr('href'),
            success: response => {
                $this.closest('.panel').remove();
                notifyService.showMessage('alert', 'topRight', response.message);
            },
            error: xhr => {
                console.error(xhr);
            },
        });
    });

    $(document).on('click', '.creator-remove-connection', function (e, options = {confirmed: false}) {
        let $this = $(this);

        let removeConfirmationConfig = JSON.parse($('.creator-remove-connection-confirmation-config').text());

        if (!options.confirmed) {
            $.vizitkaNotification(removeConfirmationConfig)
                .notification()
                .then(() => $this.trigger('click', {confirmed: true}));
            return;
        }

        $this.closest('.form-group').remove();

        var creator = $(this).attr('name');
        let $form = $('.' + creator + '-form');

        $form.find('[type=submit]')
            .removeClass('btn-default')
            .addClass('btn-primary')
            .prop('disabled', false);
    });

    $(document).on('click', '.creator-add-connection', function (e) {
        let $this = $(this);
        let $containerIntegration = $this.closest('.container-integrarion-entity');
        let creator = $this.attr('name');

        let $container = $containerIntegration.find('.' + creator + '-block-connection');
        let itemTemplate = $containerIntegration.find('.' + creator + '-connection-item').text();
        $container.append(itemTemplate);
        $container.find('.select').select2();
    });


    var CreatorPageService = CreatorPageService || {};

    dragula([document.querySelector('.integration-accordion-target')], {
        mirrorContainer: document.querySelector('.panel-creator'),
        moves: function (el, container, handle) {
            return handle.classList.contains('dragula-handle-int');
        }
    }).on('dragend', function (element) {
        CreatorPageService.systemsList.saveSequence();
    });

    CreatorPageService.systemsList = {
        saveSequence: function () {
            var integrations = [];

            $('.integration-accordion-target > div').each(function (index, el) {
                integrations.push($(el).data('page-integration-id'))
            });

            $.ajax({
                method: 'POST',
                url: backendPageConfig.saveCreatorSequence,
                dataType: 'json',
                data: {
                    integrations: integrations
                },
                success: function (response) {
                    CreatorPageService.showMessage('alert', 'topRight', response.message);
                },
                error: function (response) {
                    CreatorPageService.showMessage('error', 'topRight', response.message);
                },
            });
        },
    };

    CreatorPageService.showMessage = function (type, layout, text) {
        notifyService.showMessage(type, layout, text);
    };

});