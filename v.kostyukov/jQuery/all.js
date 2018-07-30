$(document).ready(function () {
    $(document).on('focus', '.geocoder', function (e) {
        $(this).autocomplete({
            source: function (request, responce) {
                let search_query = request.term;
                axios.get('https://geocode-maps.yandex.ru/1.x/', {
                    transformRequest: [function (data, headers) {
                        delete headers['X-Socket-Id'];
                        delete headers['common']['X-Requested-With'];
                        delete headers['common']['X-CSRF-TOKEN'];
                        return data;
                    }],
                    params: {
                        'format': 'json',
                        'geocode': search_query
                    }
                }).then(function (response) {
                    let data = response.data;
                    let search_result = [];
                    $.each(data.response.GeoObjectCollection.featureMember, function (index, item) {
                        let itemLabel = item.GeoObject.description + ', ' + item.GeoObject.name;
                        let autocompleteItem = {
                            label: itemLabel,
                            value: itemLabel,
                        };
                        search_result.push(autocompleteItem);
                    });
                    responce(search_result);
                });
            }
        });
    });

    $('.phone-mask').inputmask("+7(999)999-99-99");


    $(document).on('click', '.letter_search', function (e) {
        e.preventDefault();
        let key = $(this).data('key');
        let form = $(this).closest('form');
        form.find('input[name="start_with"]').val(key);
        form.submit();
    });

    //ajax-tab
    $(document).on('click', '.ajax-tab', function (e) {
        if ($(this).attr('loaded')) {

        } else {
            let index = $(this).closest('.uk-tab').find('li').index($(this).closest('li'));
            let blocks = $(this).closest('.uk-tab').parent();//$(this).closest('.uk-tab').closest('div');
            let li = blocks.find('.uk-switcher').children('li').eq(index);
            $(this).attr('loaded', 'true');
            $.get($(this).attr('href'), {}, function (data) {
                li.html(data);
                enableDatePickers();
            });
        }
    });

    $('.uk-active .ajax-tab').attr('loaded', 'true');

    $(document).on('click', '.ajax-tab-content .uk-pagination a', function (e) {
        let tabContent=$(this).closest('.ajax-tab-content');
        $.get($(this).attr('href'), {}, function (data) {
            tabContent.html(data);
            enableDatePickers();
            $(document).scrollTop(0);
        });
        return false;
    });

    $(document).on('submit', '.ajax-tab-content .ajax-tab-form', function () {
        let tabContent=$(this).closest('.ajax-tab-content');
        $.get($(this).attr('action'), $(this).serialize(), function (data) {
            tabContent.html(data);
            enableDatePickers();
        });
        return false;
    });




    $(document).on('keyup', 'input[data-display-name-type]', _.debounce(function (e) {
        let context = '';
        if($(this).data('display-name-type-context')) {
            context = $(this).data('display-name-type-context') + ' ';
        }

        let name = $(context + 'input[data-display-name-type="name"]').val();
        let lastName = $(context + 'input[data-display-name-type="last_name"]').val();
        let middleName = $(context + 'input[data-display-name-type="middle_name"]').val();
        let val = $(context + '.display-name-types').val();

        $.get('/displayNameTypes', {
            'name': name,
            'last_name': lastName,
            'middle_name': middleName,
            'display_name_type': val
        }, function (data) {
            let selectDisplayName = $(context + '.display-name-types');
            selectDisplayName.html($(data).html());
            if (!selectDisplayName.find('option').length) {
                selectDisplayName.prop('disabled', true);
            } else {
                selectDisplayName.prop('disabled', false);
            }
        })

    }, 200));

    $('.ui-datepicker').datepicker($.extend({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd.mm.yy'
        },
        $.datepicker.regional['ru']
    ));

    jQuery(function ($) {
        $.datepicker.regional['ru'] = {
            closeText: 'Закрыть',
            prevText: '&#x3c;Пред',
            nextText: 'След&#x3e;',
            currentText: 'Сегодня',
            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            monthNamesShort: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
            dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            weekHeader: 'Нед',
            dateFormat: 'dd.mm.yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['ru']);
    });

    function enableDatePickers() {
        $('.ui-datepicker').datepicker($.extend({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'dd.mm.yy'
            },
            $.datepicker.regional['ru']
        ));
    }

});
