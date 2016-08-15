app.factory('Selectors', function() {
    var obj = {};

    obj.fadeIn = $('.fade-in');
    obj.chat = $('#chat-container');
    obj.overlay = $('#overlay');
    obj.commands = $('#commands-overlay');
    obj.chatWindowSelector = '#chat-window';
    obj.chatWindow = $(obj.chatWindowSelector);
    obj.form = $('#chat-input form');
    obj.textarea = $('textarea', obj.form);

    obj.emojilist = $('#emojilist');
    obj.emojiSelectorClass = 'emojiSelector';

    return obj;
});