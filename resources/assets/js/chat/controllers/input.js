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

        var value = getInput();

        if (value.slice(-1) === ' ') { // prevent duplicate spaces
            withSpace = false;
        }

        value += (withSpace ? ' ' + input : input);

        setInput(value);

        focus();
    };

    $rootScope.addCode = function (code, url) {
        try {
            if (url == undefined) {
                url = false;
            }

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

            if (selectedText.length > 0) {
                url = selectedText;
            }

            if (code == 'url' && selectedText.length == 0 && !url) {
                Selectors.link.overlay.modal('show');

                return;
            }

            if (code == 'img' && selectedText.length == 0 && !url) {
                Selectors.image.overlay.modal('show');

                return;
            }

            var wrapText;
            var appendCode;
            var prependCode;
            var goIntoCode = false;

            if (needsUrl(code)) {
                if (code === 'url') {
                    appendCode = '[' + code + '=' + url + ']';
                } else if (code === 'img') {
                    appendCode = '[' + code + ']';
                }
            }

            if (!appendCode) {
                appendCode = '[' + code + ']';
            }

            if (!prependCode) {
                prependCode = '[/' + code + ']';
            }

            if (selectedText.length == 0) {
                if (hasUrl(url)) {
                    wrapText = url;
                } else {
                    goIntoCode = true;
                    wrapText = '';
                }
            } else {
                wrapText = selectedText;
            }

            value = beforeText + appendCode + wrapText + prependCode + afterText;

            setInput(value);

            // Set caret position
            focus();

            var caretStart = value.length;
            var caretStop = caretStart;

            if (goIntoCode) {
                caretStart = beforeText.length + appendCode.length;
                caretStop = caretStart + wrapText.length;
            }

            textarea.setSelectionRange(caretStart, caretStop);
        } catch (error) {
            console.log(error);
        }
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

    $scope.$on('sent', function () {
        focus();
    });

    $scope.$on('setInputLength', function (event, maxLength) {
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

    Selectors.textarea.keydown(function (e) {
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

    // Modal form event handling for image URL's
    $(document).on('submit', Selectors.image.form.url, function (e) {
        e.preventDefault();

        var input = $(this).serializeArray();

        var url = input[1].value;

        $rootScope.addCode('img', url);

        $(this)[0].reset();

        Selectors.image.overlay.modal('hide');

        Selectors.textarea.focus();
    });

    // Modal form event handling for regular URL's
    $(document).on('submit', Selectors.link.form, function (e) {
        e.preventDefault();

        var input = $(this).serializeArray();

        var url = input[1].value;

        $rootScope.addCode('url', url);

        $(this)[0].reset();

        Selectors.link.overlay.modal('hide');

        Selectors.textarea.focus();
    });

});