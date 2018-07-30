$(function ()
{
    let $productsPage = $('.products-page');
    let $productsTable = $('.products-table');
    let $productsTableLoading = $('.products-table-loading');

    if ( ! $productsPage.size())
    {
        return;
    }

    $productsPage
        .find('.select2')
        .select2();

    let performCategoryRemoveConfirmationConfig = JSON.parse($('.perform-category-remove-confirmation-config').text());
    let performProductRemoveConfirmationConfig = JSON.parse($('.perform-product-remove-confirmation-config').text());
    let performListRemoveConfirmationConfig = JSON.parse($('.perform-list-remove-confirmation-config').text());

    let mustacheTemplateProductsModalAddProduct = $('.template-products-modal-add-product').text();
    let mustacheTemplateProductsModalCloneToOther = $('.template-products-modal-clone-to-other').text();

    $(document).on('click', '.category-perform-add', function (e)
    {
        $.ajax({
            type: 'GET',
            url: $productsTable.data('add-category-modal-href'),
            success: response =>
            {
                let $modal = $(response);
                $modal
                    .on('hidden.bs.modal', event => $modal.remove())
                    .modal('show');
                $modal
                    .find('.select2')
                    .select2();
            },
            error: xhr =>
            {
                console.error(xhr);
            },
        });
    });

    $(document).on('click', '.product-perform-add', function (e)
    {
        $.ajax({
            type: 'GET',
            url: $productsTable.data('add-product-modal-href'),
            success: response =>
            {
                let $modal = $(response);
                $modal
                    .on('hidden.bs.modal', () => $modal.remove())
                    .modal('show');
                $modal
                    .find('.select2')
                    .select2();
            },
            error: xhr =>
            {
                console.error(xhr);
            },
        });
    });

    let mustacheTemplateProductsColumnImage = $('.template-products-column-image').text();
    let mustacheTemplateProductsColumnPrice = $('.template-products-column-price').text();
    let mustacheTemplateProductsColumnViewsandorders = $('.template-products-column-viewsandorders').text();
    let mustacheTemplateProductsColumnConversion = $('.template-products-column-conversion').text();
    let mustacheTemplateProductsColumnAvailable = $('.template-products-column-available').text();
    let mustacheTemplateProductsColumnOptions = $('.template-products-column-options').text();

    $productsTable.fancytree({
        extensions: ['table', 'dnd', 'filter'],
        selectMode: 3,
        checkbox: true,
        table:
            {
                indentation: 25,
                nodeColumnIdx: 2,
                checkboxColumnIdx: 0,
            },
        dnd: {
            autoExpandMS: 400,
            focusOnClick: true,
            preventVoidMoves: true,
            preventRecursiveMoves: true,
            dragStart: function (node, data)
            {
                return true;
            },
            dragEnter: function (node, data)
            {
                if (( ! data.otherNode.isFolder()) && ( ! node.isFolder()) && (data.otherNode.data.id == node.data.id))
                {
                    return false;
                }

                if (node.isFolder() ^ data.otherNode.isFolder())
                {
                    if (node.isFolder())
                    {
                        let lastNearCategoryNode = node.parent.children.filter(node => node.isFolder()).slice(-1)[0];

                        return (node.lazy && (node.children === null)) ?
                            ((lastNearCategoryNode == node) ? ['after'] : false) :
                            ((lastNearCategoryNode == node) ? ['over', 'after'] : ['over']);
                    }
                    else
                    {
                        let firstNearProductNode = node.parent.children.filter(node => ! node.isFolder())[0];

                        return (firstNearProductNode == node) ? ['before'] : false;
                    }
                }

                return (node.isFolder() && (node.children !== null)) ? true : ['before', 'after'];
            },
            dragDrop: function (node, data)
            {
                let requestData =
                    {
                        categoryId: data.otherNode.isFolder() ? data.otherNode.data.id : data.otherNode.parent.data.id,
                        productId: data.otherNode.isFolder() ? undefined : data.otherNode.data.id,
                        mode: data.hitMode,
                        destinationCategoryId: node.isFolder() ? node.data.id : node.parent.data.id,
                        destinationProductId: node.isFolder() ? undefined : node.data.id,
                    };

                if (data.otherNode.isFolder() && ( ! node.isFolder()) && (data.hitMode == 'before'))
                {
                    requestData.mode = 'over';
                    requestData.destinationCategoryId = node.parent.data.id;
                    requestData.destinationProductId = undefined;
                }

                $productsTableLoading.show();
                $.ajax({
                    type: 'POST',
                    url: $productsTable.data('move-href'),
                    data: requestData,
                    success: response =>
                    {
                        notifyService.showMessage('alert', 'topRight', $productsTable.data('move-successfully'));

                        let destinationNode = node;
                        let destinationMode = data.hitMode;

                        // if category drop over category insert it into top instead of bottom - if no other categories otherwise after last category
                        if (data.otherNode.isFolder() && node.isFolder() && (data.hitMode == 'over'))
                        {
                            let destinationChildCategories = node.children.filter(node => node.isFolder());

                            if ( ! destinationChildCategories.length)
                            {
                                destinationMode = 'firstChild';
                            }
                            else
                            {
                                destinationNode = destinationChildCategories.slice(-1)[0];
                                destinationMode = 'after';
                            }
                        }

                        // if product drop to top remove product from any categories
                        if (( ! data.otherNode.isFolder()) && (data.tree.rootNode == node.parent) && ((data.hitMode == 'after') || (data.hitMode == 'before')))
                        {
                            let nodesForRemoving = [];
                            data.tree.filterNodes(node =>
                                (
                                    ( ! node.isFolder()) &&
                                    (node != data.otherNode) &&
                                    (node.data.id == data.otherNode.data.id)
                                ) ?
                                    nodesForRemoving.push(node) :
                                    null);
                            nodesForRemoving.forEach(node => node.remove());
                        }

                        // if exists same node on destination level then remove it
                        if ( ! data.otherNode.isFolder())
                        {
                            (
                                ((data.hitMode == 'before') || (data.hitMode == 'after')) ?
                                    node.parent.children :
                                    ((node.children !== null) ? node.children : [])
                            )
                                .filter(node =>
                                    ( ! node.isFolder()) &&
                                    (node != data.otherNode) &&
                                    (node.data.id == data.otherNode.data.id)
                                )
                                .forEach(node => node.remove());
                        }

                        data.otherNode.moveTo(destinationNode, destinationMode);

                        if ( ! data.otherNode.isFolder())
                        {
                            setTimeout(() =>
                                {
                                    $(data.otherNode.tr).find('>td').eq(1).find('img').css({
                                        'left': 25 * (data.otherNode.getLevel() - 1) + 'px',
                                    });
                                },
                                0);
                        }
                    },
                    error: xhr =>
                    {
                        notifyService.showMessage('error', 'topRight', xhr.statusText);
                        console.error(xhr);
                    },
                    complete: () =>
                    {
                        $productsTableLoading.hide();
                    },
                });
            },
        },
        source: { url: $productsTable.data('href'), cache: false, },
        lazyLoad: function (event, data)
        {
            data.result =
                {
                    url: $productsTable.data('href'),
                    data: { categoryId: data.node.data.id, },
                    cache: false,
                };
        },
        renderColumns: function (event, data)
        {
            let item = data.node.data;
            let category = data.node.isFolder() ? item : null;
            let product = data.node.isFolder() ? null : item;
            let mustacheData = {item, category, product};

            let $tdList = $(data.node.tr).find('>td');

            if (product)
            {
                $tdList.eq(1).html(Mustache.render(mustacheTemplateProductsColumnImage, mustacheData)).find('img').css({
                    'position': 'relative',
                    'left': $tdList.eq(2).find('.fancytree-node').css('padding-left'),
                });
                $tdList.eq(2).find('.fancytree-node').css({'margin-left': '-25px'});
            }
            else
            {
                $tdList.eq(1).remove();
                $tdList.eq(2).attr('colspan', '2');
            }

            $tdList.eq(3).html(Mustache.render(mustacheTemplateProductsColumnPrice, mustacheData));
            $tdList.eq(4).html(Mustache.render(mustacheTemplateProductsColumnViewsandorders, mustacheData));
            $tdList.eq(5).html(Mustache.render(mustacheTemplateProductsColumnConversion, mustacheData));
            $tdList.eq(6).html(Mustache.render(mustacheTemplateProductsColumnAvailable, mustacheData));
            $tdList.eq(7).html(Mustache.render(mustacheTemplateProductsColumnOptions, mustacheData));

            //$('.styled').uniform({radioClass: 'choice'});
        },
        strings: $productsTable.data('strings-trans'),
    });

    $(document).on('submit', '.category-add-form', function (e)
    {
        e.preventDefault();
        let $form = $(this);
        let $modal = $form.closest('.modal');
        if ($form.data('ajax'))
        {
            return;
        }
        $form.find('.has-error').removeClass('has-error');
        $form.find('[type=submit]').button('loading');
        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: response =>
            {
                $productsTable.fancytree('getTree').reload();
                $modal.modal('hide');
                $form[0].reset();
                notifyService.showMessage('alert', 'topRight', $form.data('success-message'));
            },
            error: xhr =>
            {
                if ('object' === typeof xhr.responseJSON)
                {
                    for (let key in xhr.responseJSON)
                    {
                        $form.find('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                        notifyService.showMessage('error', 'topRight', xhr.responseJSON[key]);
                    }
                    return;
                }
                console.error(xhr);
            },
            complete: () =>
            {
                $form.removeData('ajax');
                $form.find('[type=submit]').button('reset');
            },
        }));
    });

    $(document).on('submit', '.product-add-form', function (e)
    {
        e.preventDefault();
        let $form = $(this);
        let $modal = $form.closest('.modal');
        if ($form.data('ajax'))
        {
            return;
        }
        $form.find('.has-error').removeClass('has-error');
        $form.find('[type=submit]').button('loading');
        $form.data('ajax', $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: response =>
            {
                $productsTable.fancytree('getTree').reload();
                $modal.modal('hide');
                $form[0].reset();
                notifyService.showMessage('alert', 'topRight', $form.data('success-message'));
            },
            error: xhr =>
            {
                if ('object' === typeof xhr.responseJSON)
                {
                    for (let key in xhr.responseJSON)
                    {
                        $form.find('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                        notifyService.showMessage('error', 'topRight', xhr.responseJSON[key]);
                    }
                    return;
                }
                console.error(xhr);
            },
            complete: () =>
            {
                $form.removeData('ajax');
                $form.find('[type=submit]').button('reset');
            },
        }));
    });

    $(document).on('click', '.perform-group-action', function (e)
    {
        let $this = $(this);
        let $performingGroupAction = $('.performing-group-action');

        switch ($performingGroupAction.val())
        {
            case 'perform-clone':
                $this.button('loading');
                $.ajax({
                    type: 'POST',
                    url: $performingGroupAction.find('option:selected').data('href'),
                    data:
                        {
                            categoriesIds: $productsTable.fancytree('getTree').getSelectedNodes().filter(node => node.folder).map(node => node.data.id),
                            productsIds: $productsTable.fancytree('getTree').getSelectedNodes().filter(node => ! node.folder).map(node => node.data.id),
                        },
                    success: response =>
                    {
                        notifyService.showMessage('alert', 'topRight', response.message);
                        $productsTable.fancytree('getTree').reload();
                    },
                    error: xhr =>
                    {
                        console.error(xhr);
                        notifyService.showMessage('error', 'topRight', xhr.statusText);
                    },
                    complete: () =>
                    {
                        $this.button('reset');
                    },
                });
                break;

            case 'perform-clone-to-other':

                let $modal = $(Mustache.render(mustacheTemplateProductsModalCloneToOther, {}));
                $modal
                    .on('hidden.bs.modal', () => $modal.remove())
                    .modal('show');
                $modal
                    .find('.select2')
                    .select2();

                break;

            case 'perform-remove':
                $.vizitkaNotification(performListRemoveConfirmationConfig)
                    .notification()
                    .then(() =>
                    {
                        $this.button('loading');
                        $.ajax({
                            type: 'DELETE',
                            url: $performingGroupAction.find('option:selected').data('href'),
                            data:
                                {
                                    categoriesIds: $productsTable.fancytree('getTree').getSelectedNodes().filter(node => node.folder).map(node => node.data.id),
                                    productsIds: $productsTable.fancytree('getTree').getSelectedNodes().filter(node => ! node.folder).map(node => node.data.id),
                                },
                            success: response =>
                            {
                                $productsTable.fancytree('getTree').reload();
                            },
                            error: xhr =>
                            {
                                console.error(xhr);
                                notifyService.showMessage('error', 'topRight', xhr.statusText);
                            },
                            complete: () =>
                            {
                                $this.button('reset');
                            },
                        });
                    });
                break;
        }

        $performingGroupAction.val(null).trigger('change');
    });

    $(document).on('click', '.products-perform-remove', function (e)
    {
        let $this = $(this);

        let data = {};
        let removeConfirmationConfig;

        switch (true)
        {
            case !!$this.data('category-id'):
                data.categoriesIds = [$this.data('category-id')];
                removeConfirmationConfig = performCategoryRemoveConfirmationConfig;
                break;

            case !!$this.data('product-id'):
                data.productsIds = [$this.data('product-id')];
                removeConfirmationConfig = performProductRemoveConfirmationConfig;
                break;
        }

        $.vizitkaNotification(removeConfirmationConfig)
            .notification()
            .then(() =>
            {
                $.ajax({
                    type: 'DELETE',
                    url: $this.data('href'),
                    data,
                    success: response =>
                    {
                        $productsTable.fancytree('getTree').reload();
                    },
                    error: xhr =>
                    {
                        console.error(xhr);
                        notifyService.showMessage('error', 'topRight', xhr.statusText);
                    },
                });
            });
    });

    $(document).on('submit', 'form.clone-to-other-form', function (e)
    {
        e.preventDefault();

        let $form = $(this);
        let $modal = $form.closest('.modal');

        if ($form.data('ajax'))
        {
            return;
        }

        $form.find('.has-error').removeClass('has-error');
        $form.find('[type=submit]').button('loading');

        $form.data('ajax', $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data:
            {
                productsIds: $productsTable.fancytree('getTree').getSelectedNodes().filter(node => ! node.folder).map(node => node.data.id),
                showcase_id: $('#modal-add-site select[name="showcase_id"]').val(),
            },
            success: response =>
            {
                $productsTable.fancytree('getTree').reload();
                $modal.modal('hide');
                notifyService.showMessage('alert', 'topRight', response.message);
            },
            error: xhr =>
            {
                if ('object' === typeof xhr.responseJSON)
                {
                    for (let key in xhr.responseJSON)
                    {
                        $form.find('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                        notifyService.showMessage('error', 'topRight', xhr.responseJSON[key]);
                    }
                    return;
                }
                console.error(xhr);
            },
            complete: () =>
            {
                $form.removeData('ajax');
                $form.find('[type=submit]').button('reset');
            },
        }));
    });

});
