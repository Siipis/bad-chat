app.controller('inputController', function ($scope, $rootScope, Data, TabHelper, Selectors) {
    /**
     * String to append in beginning of all inputs
     * @type {string}
     */
    var preFill = null;

    /**
     * Focuses the input
     */
    function focus() {
        Selectors.textarea.focus();
    }

    /**
     * Empties the input
     */
    function clearInput() {
        Selectors.textarea.val(preFill);

        focus();
    }

    /**
     * Returns the input value
     * @returns {string}
     */
    function getInput() {
        return Selectors.textarea.val();
    }

    /**
     * Sets the input value
     * @param value
     */
    function setInput(value) {
        Selectors.textarea.val(value);
    }

    /**
     * Submits the form
     */
    function submit() {
        var input = getInput().trim();

        if (input.length > 0) {
            $scope.$emit('submit', input);
        }

        clearInput();
    }


    $rootScope.addInput = function (input, withSpace) {
        if (withSpace === undefined) {
            withSpace = false;
        }

        var value = getInput() + (withSpace ? ' ' + input : input);

        setInput(value.trim());

        focus();
    };

    $rootScope.addCode = function (code) {
        var url;

        function hasUrl(url) {
            if (url === undefined || url == null) {
                return false;
            }

            return url.length > 0;
        }

        function needsUrl(code) {
            return code === 'url' || code === 'img';
        }

        var textarea = Selectors.textarea[0];

        var value = getInput();
        var selectionStart = textarea.selectionStart;
        var selectionEnd = textarea.selectionEnd;

        var selectedText = value.substring(selectionStart, selectionEnd);
        var beforeText = value.substring(0, selectionStart);
        var afterText = value.substring(selectionEnd, value.length);

        if (code === 'url' || code === 'img' && selectedText.length == 0) {
            url = prompt('Where do you want to link to?');
        }

        if (needsUrl(code)) {
            if (hasUrl(url)) {
                if (code === 'url') {
                    value = beforeText + '[' + code + '=' + url + ']' + (selectedText.length > 0 ? selectedText : url) + '[/' + code + ']' + afterText;
                } else if (code == 'img') {
                    value = beforeText + '[' + code + ']' + (selectedText.length > 0 ? selectedText : url) + '[/' + code + ']' + afterText;
                }
            }
        } else {
            value = beforeText + '[' + code + ']' + selectedText + '[/' + code + ']' + afterText;
        }

        setInput(value);

        var unbindWatch = $scope.$watch('input', function (input) {
            focus();

            var caretStart = input.length;
            var caretStop = caretStart;

            if (code === 'url' && hasUrl(url)) {
                if (selectedText.length === 0) {
                    caretStart = selectionStart + 6 + url.length;
                    caretStop = caretStart + url.length;
                }
            }
            if (code === 'img' && hasUrl(url)) {
                caretStart = selectionEnd + selectedText.length + url.length + 5 + (code.length * 2);
                caretStop = caretStart;
            } else {
                if (selectedText.length > 0) {
                    caretStart = selectionStart + selectedText.length + 5 + (code.length * 2);
                } else {
                    caretStart = selectionStart + 2 + code.length;
                }

                caretStop = caretStart;
            }

            textarea.setSelectionRange(caretStart, caretStop);

            unbindWatch();
        });

    };

    $rootScope.whisperTo = function (username) {
        clearInput();

        $rootScope.addInput('/whisper ' + username + ' ');
    };

    /*
     |--------------------------------------------------------------------------
     | Angular Events
     |--------------------------------------------------------------------------
     |
     | Angular JS events
     |
     */

    $scope.$on('clearInput', function () {
        clearInput();
    });

    $scope.$on('focusInput', function () {
        focus();
    });

    $scope.$on('sent', function() {
        focus();
    });

    $scope.$on('setInputLength', function(event, maxLength) {
        Selectors.textarea.attr('maxlength', maxLength);
    });

    /*
     |--------------------------------------------------------------------------
     | Events
     |--------------------------------------------------------------------------
     |
     | jQuery event listeners
     |
     */

    Selectors.textarea.keypress(function (e) {
        try {
            // Enter
            if (e.which == 13 && !e.shiftKey || e.keyCode == 13 && !e.shiftKey) {
                e.preventDefault();

                var input = getInput();

                if (input.length == 0) {
                    return;
                }

                if (input == '/clear') {
                    $rootScope.$broadcast('clearWindow');

                    clearInput();

                    return;
                }

                var persistCommand = new RegExp('^\/(persist|p) ([0-9a-z_-]+)$', 'i');
                var whisperCommand = new RegExp('^\/(whisper|w|msg) ([0-9a-z_-]+)', 'i');

                if (persistCommand.test(input)) {
                    var nick = input.split(' ')[1];

                    preFill = '/whisper ' + nick + ' ';

                    clearInput();

                    return;
                } else if (whisperCommand.test(input) == false) {
                    preFill = null;
                }

                submit();
                return;
            }

            // Tab
            if (e.which == 9 || e.keyCode == 9) {
                e.preventDefault();

                TabHelper.init(getInput());

                setInput(TabHelper.next());
            } else {
                TabHelper.setActive(false);
            }
        } catch (error) {
            $rootScope.$broadcast('error', 'An input error occurred', error);
        }
    });

    Selectors.form.submit(function (e) {
        e.preventDefault();

        submit();
    });

});