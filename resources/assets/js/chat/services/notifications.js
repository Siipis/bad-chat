app.factory('Notifications', function (Data) {
    var obj = {};

    function shouldNotify(note) {
        var config = Data.config();
        var notify = config.settings.notify;

        if (note.type == 'whisper' || note.type == 'highlight') {
            return notify.mentions == 'on';
        }

        if (note.type == 'invite' || note.type == 'new_vouch') {
            return notify.invites == 'on';
        }

        return notify.channel == 'on';
    }

    obj.send = function(note) {
        if (shouldNotify(note) === false) return;

        var title = "New event:";
        var message = note.message;
        var timeout = 4;

        if (note.type == 'whisper') {
            title = "New whisper:";
            message = note.name + ' whispers: "' + message + '"';
            timeout = 10;
        }

        if (note.type == 'highlight') {
            title = "You were mentioned!";
            message = note.name + ' says: "' + message + '"';
            timeout = 10;
        }

        if (note.type == 'invite') {
            title = "You have a new invite!";
            timeout = 10;
        }

        if (note.type == 'new_vouch') {
            title = "New vouch!";
            timeout = 10;
        }

        Push.create(title, {
            body: message,
            icon: 'icon.jpg',
            timeout: timeout * 1000,
            onClick: function() {
                window.focus();
                this.close();
            }
        });
    };

    return obj;
});