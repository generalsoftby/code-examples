const mustacheTemplateTranslationsModalImport = $('.template-translations-modal-import').text();

$(document).on('change', '.translations-perform-import input[type=file]', event => {
    const input = event.currentTarget;
    const $input = $(input);
    const $button = $input.closest('.translations-perform-import');

    if (!input.files.length) {
        return;
    }

    $button.bsButton('loading');

    const formData = new FormData;
    formData.append('file', input.files[0]);

    let query = getTranslationsQuery();
    query += (query ? '&' : '') + 'action=import';

    $.ajax({
        type: 'POST',
        url: getTranslationsUrl(query),
        data: formData,
        contentType: false,
        processData: false,
        success: response => {
            const $modal = $(Mustache.render(mustacheTemplateTranslationsModalImport));
            $modal
                .on('hidden.bs.modal', () => {
                    $modal.remove();
                    loadTranslations();
                })
                .modal('show');

            $modal.find('.translations-import-lines').text(response.lines);

            Echo
                .listen('TranslationsImportWasChanged', e => {
                    if (!$modal.size()) {
                        return;
                    }

                    $modal.find('.translations-import-title-waiting').hide();
                    $modal.find('.translations-import-title-processing').show();

                    $modal.find('.translations-import-lines').text(e.lines);
                    $modal.find('.translations-import-processed').text(e.processed);
                    $modal.find('.translations-import-unchanged').text(e.unchanged);
                    $modal.find('.translations-import-success').text(e.success);
                    $modal.find('.translations-import-warning').text(e.warning);

                    let processedProgress = e.processed / e.lines;
                    let unchangedProgress = e.unchanged / e.lines;
                    let successProgress = e.success / e.lines;
                    let warningProgress = e.warning / e.lines;

                    $modal.find('.translations-import-progress-processed').css('width', (processedProgress * 100) + '%').text((parseInt(processedProgress * 100 * 100) / 100) + '%');
                    $modal.find('.translations-import-progress-unchanged').css('width', (unchangedProgress * 100) + '%');
                    $modal.find('.translations-import-progress-success').css('width', (successProgress * 100) + '%');
                    $modal.find('.translations-import-progress-warning').css('width', (warningProgress * 100) + '%');

                    if (e.processed === e.lines) {
                        $modal.find('.progress-bar').removeClass('progress-bar-striped active');
                        $modal.find('.translations-import-close').show();
                    }
                });
        },
        error: xhr => {
            notifyService.showMessage('error', 'topRight', xhr.status + ' ' + xhr.statusText);
        },
        complete: () => {
            $input.val('');
            $button.bsButton('reset');
        },
    });
});
