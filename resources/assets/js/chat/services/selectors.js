app.factory('Selectors', function() {
    var obj = {};

    obj.fadeIn = $('.fade-in');
    obj.chat = $('#chat-container');
    obj.overlay = $('#overlay');
    obj.commands = $('#commands-overlay');
    obj.join = $('#join-overlay');
    obj.chatWindowSelector = '#chat-window';
    obj.chatWindow = $(obj.chatWindowSelector);
    obj.form = $('#chat-input form');
    obj.textarea = $('textarea', obj.form);

    obj.image = {
        overlay: $('#image-overlay'),
        form: {
            url: 'form#image-overlay-link',
            upload: 'form#image-overlay-upload'
        },
        input: {
            url: $('#image-overlay-link input'),
            upload: $('#image-overlay-upload input')
        }
    };

    obj.link = {
        overlay: $('#link-overlay'),
        form: 'form#link-overlay-form',
        input: $('#link-overlay-form input')
    };

    obj.emojilist = $('#emojilist');
    obj.emojiSelectorClass = 'emojiSelector';

    return obj;
});