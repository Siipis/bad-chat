app.factory('Selectors', function() {
    var obj = {};

    obj.fadeIn = $('.fade-in');
    obj.chat = $('#chat-container');
    obj.errors = $('#chat-errors');
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
            link: 'form#image-overlay__link',
            upload: 'form#image-overlay__upload'
        },
        input: {
            link: 'input#inputUrl',
            upload: 'input#inputUpload'
        },
        preview: $('img#image-preview')
    };

    obj.link = {
        overlay: $('#link-overlay'),
        form: 'form#link-overlay__form',
        input: $('input#inputLink')
    };

    obj.emojilist = $('#emojilist');
    obj.emojiSelectorClass = 'emojiSelector';

    return obj;
});