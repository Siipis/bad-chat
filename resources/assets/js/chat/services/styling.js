app.factory('Styling', function ($rootScope, Settings) {
    var obj = {};

    /**
     * Emojione config
     * @type {boolean}
     */
    emojione.unicodeAlt = false;
    emojione.ascii = true;

    var emoji = {
        'xD': ':laughing:',
        ':D': ':smile:',
        ':P': ':stuck_out_tongue_closed_eyes:',
        ':)': ':relaxed:',
        ';)': ':wink:',
        ':(': ':frowning2:',
        ':/': ':unamused:',
        ':|': ':expressionless:',
        ':O': ':astonished:',
        ':X': ':kissing_closed_eyes:',
        'B)': ':sunglasses:',
        'o:)': ':innocent:',
        '<3': ':heart:',
        '</3': ':broken_heart:'
    };

    var replaceEmoji = {
        'xD': ':laughing:',
        ':D': ':smile:',
        ':P': ':stuck_out_tongue_closed_eyes:',
        ':/': ':unamused:',
        ':|': ':expressionless:',
        ':O': ':astonished:',
        ':X': ':kissing_closed_eyes:',
        'o:)': ':innocent:',
        ':3': ':fox:'
    };

    var codes = {
        '(^| )(http[s]?://[^\\\s]+)': '$1<a href="$2" target="_blank">$2</a>',
        '\\[b\\](.*)\\[\/b\\]': '<b>$1</b>',
        '\\[i\\](.*)\\[\/i\\]': '<em>$1</em>',
        '\\[u\\](.*)\\[\/u\\]': '<u>$1</u>',
        '\\[s\\](.*)\\[\/s\\]': '<s>$1</s>',
        '\\[url=(.*)\\](.*)\\[\/url\\]': '<a href="$1" target="_blank">$2</a>',
        '\\[url\\](.*)\\[\/url\\]': '<a href="$1" target="_blank">$1</a>',
        '\\[img\\](.*)\\[\/img\\]': '<a href="$1" target="_blank"><img src="$1" class="embed-image" alt="$1" /></a>'
    };


    $rootScope.emojis = [];

    $.each(emoji, function (emoji, shortname) {
        $rootScope.emojis.push({
            'code': emoji,
            'img': parseEmoji(shortname)
        });
    });

    /*
     |--------------------------------------------------------------------------
     | Methods
     |--------------------------------------------------------------------------
     |
     | Library methods
     |
     */

    /**
     * Adds styles to the input
     *
     * @param {string} input
     * @returns {*}
     */
    obj.addStyles = function (input) {
        input = parseEmoji(input);
        input = parseCode(input);

        return input;
    };

    /**
     * Adds emojis to the input
     *
     * @param {string} input
     * @returns {*}
     */
    function parseEmoji(input) {
        function escape(input) {
            return input.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
        }

        $.each(replaceEmoji, function (search, replace) {
            var pattern = '(^| )(' + escape(search) + ')( |$)';

            try {
                input = input.replace(new RegExp('^(' + escape(search) + ')$', 'mg'), replace);
                input = input.replace(new RegExp('(^| )(' + escape(search) + ')( |$)', 'mg'), ' ' + replace + ' ').trim();
            } catch (e) {
                console.log('Could not parse pattern ', pattern);
            }
        });

        return emojione.shortnameToImage(input);
    }

    /**
     * Parses BBCode to HTML
     *
     * @param {string} input
     * @returns {*}
     */
    function parseCode(input) {
        $.each(codes, function (code, html) {
            input = input.replace(new RegExp(code, 'i'), html);
        });

        return input;
    }

    /*
     |--------------------------------------------------------------------------
     | DOM helpers
     |--------------------------------------------------------------------------
     |
     | Various DOM helpers
     |
     */

    $rootScope.currentColor = function () {
        var color = Settings.get('color');

        if (color >= 0) {
            return 'color-' + color;
        }
    };

    $rootScope.selectedColor = function (index) {
        return index == Settings.get('color');
    };

    return obj;
});