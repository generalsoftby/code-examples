$(document).ready(function () {
    let notificationsCount = 5;

    if (jsData.user) {
        window.Echo.private('App.Models.User.' + jsData.user.id)
            .notification((notification) => {
                let notificationType = notification.notificationType;
                let list = $(".notification_drop_down .notification_list[data-type='" + notificationType + "']");

                if (list.length) {
                    let counter = list.closest('.notification_message').find('.counter');
                    counter.html(Number($.trim(counter.html())) + 1);

                    let copyItem = list.closest('.notification_list_container').find('ul.copy').find('li').last();
                    let newItem = copyItem.clone().addClass('new_message');
                    newItem.find('.notification_content').html(notification.text);
                    newItem.find('a').attr('href', notification.link);
                    newItem.attr('data-notification-id', notification.id);
                    list.prepend(newItem);

                    if (list.first().children('li').length > notificationsCount) {
                        list.find('li:last-child').remove();
                    }

                    list.closest('.notification_message').addClass('new_messages');
                }
            });
    }


    $('.notification_drop_down .notification_list').on('mouseenter', 'li.new_message', function (e) {
        let notificationId = $(this).data('notification-id');
        $("li.new_message[data-notification-id="+ notificationId +"]").removeClass('new_message');

        let id = $(this).data('notification-id');
        $.post('notifications/notice', {
            notificationIds: [id]
        });
    });


});
