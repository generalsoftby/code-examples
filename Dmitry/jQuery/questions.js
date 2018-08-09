$(function () {
    let $questionsTable = $('.questions-table');

    if ( ! $questionsTable.size())
    {
        return;
    }

    let mustacheTemplateQuestionsColumnCreated = $('.template-questions-column-created').text();
    let mustacheTemplateQuestionsColumnTitle = $('.template-questions-column-title').text();
    let mustacheTemplateQuestionsColumnStatus = $('.template-questions-column-status').text();
    let mustacheTemplateQuestionsColumnAuthor = $('.template-questions-column-author').text();
    let mustacheTemplateQuestionsColumnUpdated = $('.template-questions-column-updated').text();
    let mustacheTemplateQuestionsColumnUpdatedBy = $('.template-questions-column-updatedby').text();
    let mustacheTemplateQuestionsColumnViewed = $('.template-questions-column-viewed').text();
    let mustacheTemplateQuestionsColumnActions = $('.template-questions-column-actions').text();

    let drawReversDropdowns = function($table, settings)
    {
        //Reverse last dropdowns orientation
        let recordsDisplay = settings.fnDisplayEnd() - settings._iDisplayStart;

        if(recordsDisplay > 3)
        {
            let offset = recordsDisplay < 6 ? recordsDisplay - 3 : 3;
            $table.find('tbody tr').slice(-1 * offset).find('.dropdown, .btn-group').addClass('dropup');
        }
    };

    $questionsTable.DataTable({
        processing: true,
        serverSide: true,
        ajax:
            {
                url: $questionsTable.data('href'),
                data: function (data)
                {
                    $('.questions-filter-value').serializeArray().forEach(function (filter)
                    {
                        data[filter.name] = filter.value;
                    });
                },
                error: xhr => notifyService.showMessage('error', 'topRight', xhr.status + ' ' + xhr.statusText),
            },
        autoWidth: false,
        columnDefs: [
            {
                targets: 0,
                data: 'created_at',
                width: '10%',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnCreated, Object.assign({faq}, mustacheDateTimeFormats)),
            },
            {
                targets: 1,
                width: '15%',
                data: 'title',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnTitle, {faq}),
            },
            {
                targets: 2,
                data: 'active',
                width: '15%',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnStatus, {faq}),
            },
            {
                targets: 3,
                data: 'author_id',
                width: '15%',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnAuthor, {faq}),
            },
            {
                targets: 4,
                data: 'updated',
                width: '15%',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnUpdated, Object.assign({faq}, mustacheDateTimeFormats)),
            },
            {
                targets: 5,
                data: 'updater_id',
                width: '15%',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnUpdatedBy, {faq}),
            },
            {
                targets: 6,
                data: 'view_count',
                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnViewed, {faq}),
            },
            {
                targets: 7,
                orderable: false,

                render: (data, type, faq) => Mustache.render(mustacheTemplateQuestionsColumnActions, {faq}),
            },
        ],
        order: [[ 0, 'desc' ]],
        dom: '<"datatable-scroll-lg"tr><"datatable-footer"ilp>',
        lengthMenu: [ 15, 25, 50, 75, 100 ],
        displayLength: 25,
        drawCallback: function (settings)
        {
            let $this = $(this);
            $this.closest('.dataTables_wrapper').find('.dataTables_length select').select2
                ({
                    width: 'auto',
                    minimumResultsForSearch: Infinity,
                });

            drawReversDropdowns($questionsTable, settings);
            $('.questions-online').text(settings.json.counters.questions_online);
            $('.questions-views-count').text(settings.json.counters.views_count);
            $this.find('[data-popup="tooltip"]').tooltip();
        },
        preDrawCallback: function(settings)
        {
            drawReversDropdowns($questionsTable, settings);
        },
    }).on('page.dt', () => $('html, body').animate({scrollTop:0}, 500, 'swing'));

    $(document).on('change', '.questions-filter-value', function (e)
    {
        $questionsTable.DataTable().ajax.reload();
        let query = $('.questions-filter-value')
            .filter((index, element) =>
            {
                let $element = $(element);
                let hasValue = !! $element.val();
                if ( ! hasValue)
                {
                    return;
                }
                return true;
            })
            .serialize();

        history.pushState(null, null, $questionsTable.data('href') + (query ? ('?' + query) : ''));
    });

    let faqDeleteConfirmationConfig = JSON.parse($('.faq-delete-confirmation-config').text());

    $(document).on('click', '.faq-perform-delete', function (e, options = {confirmed: false})
    {
        e.preventDefault();
        let $this = $(this);
        if ($this.closest('.disabled').size())
        {
            return;
        }
        if ( ! options.confirmed)
        {
            $.vizitkaNotification(faqDeleteConfirmationConfig)
                .notification()
                .then(() => $this.trigger('click', {confirmed: true}));
            return;
        }
        $.ajax({
            type: 'delete',
            url: $this.attr('href'),
            success: response =>
            {
                notifyService.showMessage('alert', 'topRight', response.message);
                $questionsTable.DataTable().ajax.reload();

                $('.questions-online').text(response.questionsOnline);
            },
            error: xhr =>
            {
                console.error(xhr);
            },
        });
    });

    $(document).on('click', '.faq-perform-status', function (e)
    {
        e.preventDefault();

        if ($(this).parent("li").hasClass("active"))
        {
            return;
        }

        let status = $(this).attr('data-status');
        let id_status = $(this).attr('data-article');
        let url = $(this).attr('data-image');

        $('tr .status-'+ id_status +' > div').remove();
        $('tr .status-'+ id_status).append('<div><img src="' + url + '" style="width: 60px;"></div>');

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                id: id_status,
                status: status
            },
            success: function (data) {
                $('tr .status-'+ id_status +' > div').remove();
                $('tr .status-'+ id_status).append(data.success);

                $('.questions-online').text(data.questionsOnline);
            },
            error: function (data) {
                console.log(data);
            }
        });
    });
});