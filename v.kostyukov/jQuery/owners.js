let owners = jsData.ownersData;
module.exports = {
    owners: owners,
    deleteByIndex: removeOwnerByIndex
};
let clientFineuploaderHelpers;
$(document).ready(function () {
    let ownerList = $('.owners-list');
    let modal = $('#modal-owners-actions');
    let editedIndex = null;
    let fineuploaderHelpers = require('../fineuploader');
    clientFineuploaderHelpers=fineuploaderHelpers;
    init();

    $(document).on('click', '.owners-control-buttons .choose-existing', function (e) {
        e.preventDefault();
        let $this = $(this);
        $.get('/profile/clients/chooseTable', {}, function (data) {
            modal.find('.uk-modal-content').html(data);
            modal.find('.uk-modal-header .uk-modal-title').html($this.attr('data-modal-title'));
            modal.find('.uk-modal-footer .uk-button').removeClass('uk-hidden');
            modal.find('.uk-modal-footer .modal-footer-additional-fields').html('').addClass('uk-hidden');
            modal.find('.uk-modal-footer .uk-button.uk-button-save').addClass('uk-hidden');
            UIkit.modal(modal).show();
            $('.filter-client-select2').select2();
        });
    });

    modal.on('click', '.uk-modal-footer .uk-button.uk-button-save', function (e) {
        e.preventDefault();
        let $this = $(this);
        modal.find('form [type="submit"]').click();
    });

    modal.on('change', '.uk-modal-footer .modal-footer-additional-fields .check-update-client', function (e) {
        let $this = $(this);
        modal.find('form .check-update-client').closest('label').click();
    });

    $(document).on('submit', '.choose-client form', function () {
        $.get('/profile/clients/chooseTable', $(this).serialize(), function (data) {
            modal.find('.uk-modal-content').html(data);
            $('.filter-client-select2').select2();
        });
        return false;
    });

    $(document).on('click', '.choose-client .uk-pagination a', function () {
        $.get($(this).attr('href'), {}, function (data) {
            modal.find('.uk-modal-content').html(data);
            $('.filter-client-select2').select2();
            modal.find('.uk-modal-body').scrollTop(0);
        });
        return false;
    });

    $(document).on('click', '.choose-client .add-button', function () {
        let clientId = $(this).closest('.client-row').data('client-id');

        $.get('/profile/properties/owners/tableRow', {
            'client_id': clientId
        }, function (data) {
            let newOwner = data.owner;
            let ownerIndex = _.findIndex(owners, function (item) {
                return _.isEqual(_.get(item.client, 'public_id'), _.get(newOwner.client, 'public_id'));
            });

            if (ownerIndex < 0) {
                $('.owners-list').append(data.view);
                owners.push(newOwner);
                $('.owners-list-thead').removeClass('uk-hidden');
            }
        });
        UIkit.modal(modal).hide();
        return false;
    });


    $(document).on('click', '.owners-list .remove-button', function () {
        let index = ownerList.find('.owner-row').index($(this).closest('.owner-row'));
        let self = $(this);

        UIkit.modal.confirm('Вы действительно хотите удалить данного собственника?').then(function () {
            removeOwnerByIndex(index);
            initOwnerShareFraction();
        }).catch(function () {
            // В случае отмены ничего не делаем
        });

        return false;
    });

    $(document).on('click', '.owners-list .edit-button', function () {
        let $this = $(this);
        let index = $('.owners-list').find('.owner-row').index($(this).closest('.owner-row'));
        let client = _.get(owners, index, {});

        editedIndex = index;
        editOwner(client, $this);

        return false;
    });


    $(document).on('click', '.owners-control-buttons .create-owner', function (e) {
        let $this = $(this);
        e.preventDefault();
        let client = {};

        editedIndex = -1;
        editOwner(client, $this);

        return false;
    });


    $(document).on('submit', '.owner-form', function (e) {
        e.preventDefault();
        let formData = $(this).serializeObject();
        let editData = _.get(owners, editedIndex, {});

        $.ajax({
            'type': 'post',
            'url': '/profile/properties/owners/edit',
            'data': {
                'formData': formData,
                'editData': editData
            },
            'dataType': 'json',
            'success': function (data) {

                let newRow = data.view;
                let newOwner = data.owner;


                if (editedIndex >= 0) {
                    ownerList.find('.owner-row:eq(' + editedIndex + ')').html($(newRow).html());
                    owners[editedIndex] = newOwner;
                } else {
                    ownerList.append(newRow);
                    owners.push(newOwner);
                }
                $('.owners-list-thead').removeClass('uk-hidden');


                UIkit.modal('#modal-owners-actions').hide();
                initOwnerShareFraction();
            },
            'error': function (data) {
                let errors = data.responseJSON.errors;

                $('.uk-text-danger').html('');
                $('.uk-form-danger').removeClass('uk-form-danger');

                _.each(errors, function (errorMessage, key) {
                    error(key, errorMessage);


                    if(key == 'fullName') {
                        error('name', '');
                        error('last_name', '');
                        error('middle_name', '');
                    }
                });

                modal.find('.uk-modal-body').scrollTop(0);

                let alertBlock = $('.uk-alert');
                alertBlock.removeClass('uk-hidden');
            }
        });
        return false;
    });


    function init() {
        ownerList = $('.owners-list');
        initUploadFields();

        initOwnerShareFraction();

    }

    function initUploadFields() {
        let photosUploadBlock = $('.file-upload-block.client-avatar-block');

        let validationRulesPhotos = {
            simple: JSON.stringify([
                'image'
            ])
        };
        fineuploaderHelpers.uploadImage(photosUploadBlock, 'public', 'images/avatars', validationRulesPhotos);
    }


    function editOwner(data, objectClick) {
        let $objectClick = objectClick;
        let $formProperty = $('.property-form');
        let use_purpose = $formProperty.find('[name="use_purpose"]').val();
        let role = $formProperty.find('[name="role"]').val();
        $.post('/profile/properties/owners/editForm', {
                'ownerData': data,
                'use_purpose': use_purpose,
                'role': role
            },
            function (data) {
                modal.find('.uk-modal-content').html(data);
                $('.phone-mask').inputmask("+7(999)999-99-99");
                modal.find('.uk-modal-header .uk-modal-title').html($objectClick.attr('data-modal-title'));
                modal.find('.uk-modal-footer .uk-button').removeClass('uk-hidden');
                modal.find('.uk-modal-footer .modal-footer-additional-fields').html(modal.find('form .cont-form-update-client').html()).removeClass('uk-hidden');
                modal.find('form [type="submit"]').addClass('uk-hidden');
                modal.find('form .cont-form-update-client').addClass('uk-hidden');
                UIkit.modal(modal).show();
                init();
            });
    }

    function error(key, errorMessage) {
        let errorInput = $('.owner-form [name="' + key + '"]');
        if (!errorInput.length) {
            errorInput = $('.owner-form [name="' + key + '[]"]');
        }
        let inputBlock = errorInput.closest('.input-block');
        let errorMessageBlock = inputBlock.find('.uk-text-danger');

        errorInput.addClass('uk-form-danger');
        errorMessageBlock.html(errorMessage);
    }
});


function removeOwnerByIndex(index) {
    $('.owners-list').find('.owner-row:nth-child(' + (index + 1) + ')').remove();
    owners.splice(index, 1);
}

function initOwnerShareFraction() {
    let $listShare = $('.property-owner-share');
    let totalShare = new Fraction('0');
    $listShare.each(function (index, element) {
        let strShare = $(element).text();
        if(strShare){
            totalShare = new Fraction(totalShare).add(strShare);
        }
    });
    if(totalShare > 1){
        $listShare.addClass('uk-text-danger');
    }
    else{
        $listShare.removeClass('uk-text-danger');
    }
}
