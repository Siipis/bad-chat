app.controller('messageController', function($rootScope, $scope, Data, Styling) {
    function hasHighlight(row) {
        var config = Data.config();

        if (row.isOwnMessage) {
            return false;
        }

        if (config.settings.highlight === undefined) {
            return false;
        }

        var highlights = 0;

        $.each(config.settings.highlight, function (key, value) {
            if (row.message.indexOf(value.trim()) >= 0) {
                highlights++;
            }
        });

        return highlights > 0;
    }

    $scope.addClasses = function (row) {
        var classes = '';

        if (row.hidden !== undefined) {
            classes += ' hidden';
        }

        if (row.whisperDirection !== undefined) {
            classes += ' ' + row.whisperDirection;
        }

        if (row.type == 'post' && hasHighlight(row)) {
            classes += ' highlight';
        }

        return classes.trim();
    };

    $scope.dropdown = function (row) {
        return row.type == 'post' || row.type == 'whisper' || row.type == 'emote';
    };

    $scope.colon = function (row) {
        return row.type == 'post' || row.type == 'whisper';
    };

    $scope.name = function (row) {
        return row.type == 'whisper' && row.whisperDirection == 'from' ? row.receiver : row.name;
    };

    $scope.color = function (row) {
        if (row.color !== undefined && row.color > 0) {
            return 'color-' + row.color;
        }
    };

    $scope.stylize = function(row) {
        return Styling.addStyles(row.message);
    };
});