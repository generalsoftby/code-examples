import JSONEditor from 'jsoneditor';

$(function () {
    $(document).on('init', '.settings-json-editor', (event, {settings} = {}) => {
        const $editor = $(event.currentTarget);
        const $form = $editor.closest('form');
        const $input = $form.find('[name=settings]');
        const isDisabled = !$input.size() || $input.prop('disabled');
        settings = settings || $editor.data('settings') || JSON.parse($input.val());
        $editor.removeAttr('settings').removeData('settings').empty();
        const editor = new JSONEditor(event.currentTarget,
            {
                onChange: () => void $input.val(editor.getText()).trigger('change'),
                onEditable: () => !isDisabled,
                mode: isDisabled ? 'form' : 'tree',
                modes: isDisabled ? ['form'] : ['tree', 'code', 'text'],
                enableSort: false,
                enableTransform: false,
                maxVisibleChilds: 25,
            },
            settings);
        $input.val(editor.getText());
    });

    $(document).on('change', '.settings-json-sub-type-input', event => {
        const $subTypeInput = $(event.currentTarget);
        const $form = $subTypeInput.closest('form');
        const $editor = $form.find('.settings-json-editor');
        const settings = $subTypeInput.find(':selected').data('settings');
        $editor.trigger('init', {settings});
    });
});
