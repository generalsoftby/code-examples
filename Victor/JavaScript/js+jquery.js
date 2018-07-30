class Chat
{
    constructor(settings)
    {
        this.currentColor = 'blue';
        this.settings = Object.assign(this.defaultSettings, settings);
        this.chatMessageAudio = new Audio(this.settings.chatMessageAudioFile);
        this.drawUnreadIndicate();
    }

    get defaultSettings()
    {
        return {
            address: '127.0.0.1',
            port: 7080,
            chatMessageAudioFile: '/sounds/chat-message.mp3',
            reconnectTimeInterval: 10 * 1000,
            messagesBlockSelector: '.rq-chat .direct-chat-messages .padder',
        };
    }

    connect ()
    {
        let stopConnectingInterval;

        console.log('Trying to connect to server');

        stopConnectingInterval = () =>
        {
            if (this.reconnectInterval)
            {
                clearInterval(this.reconnectInterval);
                this.reconnectInterval = undefined;
            }
        };

        this.socket = new WebSocket("ws://" + this.settings.address + ":" + this.settings.port);

        this.socket.onopen = function ()
        {
            console.log("Connection established");

            stopConnectingInterval();
        };

        this.socket.onclose = (event) =>
        {
            console.log('Chat socket is closed (' + (event.wasClean ? 'Clean' : 'Dirty') + ',' + event.code + ',"' + event.reason + '");');

            if (event.wasClean)
            {
                stopConnectingInterval();
            } else if (!this.reconnectInterval)
            {
                return this.reconnectInterval = setInterval(this.connect, this.settings.reconnectTimeInterval);
            }
        };

        this.socket.onmessage = (event) =>
        {
            let json_data = JSON.parse(event.data);

            $(this.settings.messagesBlockSelector).append(json_data.msg.html);

            this.scrollToBottom();

            this.newMessageIndicate();

            if (json_data.is_own === false)
            {
                this.playMessageAudio();

                if (this.chatIsOpen() === false)
                {
                    this.showNotify(json_data.msg);
                }
            }
        };

        this.socket.onerror = (error) =>
        {
            console.log("Error " + (error ? error.message : error));
        };
    }

    send(data)
    {
        return this.socket.send(JSON.stringify(data));
    }

    scrollToBottom()
    {
        $('.rq-chat .direct-chat-messages').scrollTop($('.rq-chat .direct-chat-messages')[0].scrollHeight);
    }

    newMessageIndicate()
    {
        if (Cookies.get('rside-open') === 'true')
        {
            return;
        }

        let counter = Cookies.get('unread-massages');

        counter = counter === void 0 ? 0 : parseInt(counter);

        Cookies.set('unread-massages', counter + 1, {path: ''});

        this.drawUnreadIndicate();
    };

    drawUnreadIndicate()
    {
        let counter = Cookies.get('unread-massages');
        let indicator = $('#chat-unread-indicate');

        counter = counter === void 0 ? 0 : parseInt(counter);

        indicator.html(counter);

        if (counter === 0)
        {
            indicator.hide();
        } else
        {
            indicator.show();
        }
    };

    playMessageAudio()
    {
        this.chatMessageAudio.play();
    };

    showNotify(msg)
    {
        $.notify(
            {
                title: msg.author,
                text: msg.text,
                image: "<img src='/bower_components/notifyjs/examples/images/Mail.png'/>"
            },
            {
                style: 'metro',
                className: 'white',
                autoHide: true,
                clickToHide: true
            });
    };

    chatIsOpen()
    {
        return $('body').hasClass('control-sidebar-open') || $('.rq-chat').hasClass('control-sidebar-open');
    };
}

$(function ()
{
    App = window.App;

    App.chat = new Chat({address: window.location.hostname, port: $('meta[name="chat-server-port"]').attr('content')});

    App.chat.connect();

    $(document).ready(function ()
    {
        App.chat.scrollToBottom();
    });

    $('.chat-colors-popover').popover(
    {
        html: true,
        placement: 'top',
        trigger: 'click',
        content: function ()
        {
            return $('#chat-colors-popover-tpl').html();
        }
    }).on("hidden.bs.popover", function ()
    {
        return $(this).data("bs.popover").inState.click = false;
    });

    $(document).on('click', "#color-chooser > li > a", function (e)
    {
        let btn, color_new;
        e.preventDefault();
        color_new = $(this).data("color");
        btn = $('#chat-send-btn');
        btn.removeClass('bg-' + App.chat.currentColor);
        App.chat.currentColor = color_new;
        btn.addClass('bg-' + App.chat.currentColor);
        return $('.chat-colors-popover').popover('hide');
    });

    $(document).on('submit', '.rq-chat #chat-message-form', function (e)
    {
        e.preventDefault();

        let $input = $('.rq-chat textarea[name=message]');

        let msg = $input.val();

        if (msg.trim() === '')
        {
            return false;
        }

        App.chat.send({
            msg: msg,
            color: App.chat.currentColor
        });

        $input.val('');

        return false;
    });

    $(document).on('submit', '#chatHistoryModal #chat-history-form', function (e)
    {
        e.preventDefault();

        $.ajax({
            method: 'GET',
            url: '/ajax/chat-history',
            data: $(this).serialize(),
            success: (response) =>
            {
                $('#chatHistoryModal .messages-container').html(response);
            },
            error: (response) =>
            {
                console.log(response);
            }
        });
        return false;
    });

    $(document).on('click', 'aside.rq-chat .chat-show-more', function (e)
    {
        e.preventDefault();

        let btn = $(this);

        btn.hide();

        let el = $('aside.rq-chat .direct-chat-msg').first();

        if (!el)
        {
            return false;
        }

        $.ajax({
            method: 'GET',
            url: '/ajax/chat-more',
            data: {
                last_id: el.data('id')
            },
            success: (response) =>
            {
                $(response).insertBefore(el);
                btn.show();
            },
            error: (response) =>
            {
                console.log(response);
            }
        });

        return false;
    });

});

