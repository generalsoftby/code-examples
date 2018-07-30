function uploadImage(uploadBlock, storage, directory, validationRules) {
    let clickElement = uploadBlock.find('.attach-file');
    let errorElement = uploadBlock.find('.error-text');
    let inputElement = uploadBlock.find('.file-field');
    let imageElement = uploadBlock.find('.image-div');


    let asd = new qq.FineUploaderBasic({
        debug: true,
        button: clickElement[0],
        request: {
            endpoint: '/ajax/upload',
            method: 'POST',
            params: {
                storage: storage,
                directory: directory,
                validationRules: validationRules
            },
            paramsInBody: true
        },
        callbacks: {
            onComplete: function (id, name, response) {
                if (response.success) {
                    let attachment = response.attachment;
                    inputElement.val(attachment.id);
                    imageElement.attr('src', attachment.url);
                } else {
                    let errors = response.errors;
                    let fileErrors = errors['qqfile'];
                    let error = _.first(fileErrors);

                    errorElement.html(error);
                }
            },
            onUpload: function () {
                errorElement.html('');
            }
        }
    });
}

module.exports = {
    uploadImage: uploadImage,
};
