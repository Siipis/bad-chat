app.controller('chatController', function ($scope, $rootScope, $sce, Ajax, Audio, Data, Selectors, Settings) {
    var isUnloading = false; // Track the unload event
    var isTitleBlinking = false; // Track title blinking

    $scope.isEnabled = false;

    $scope.error = {
        'title': null,
        'message': null
    };

    /**
     * Storage for the original page title
     *
     * @type {string}
     */
    var pageTitle;

    /*
     |--------------------------------------------------------------------------
     | DOM methods
     |--------------------------------------------------------------------------
     |
     | Methods used in the DOM
     |
     */

    $rootScope.enable = function () {
        if (!$scope.isEnabled) {
            $scope.isEnabled = true;

            Selectors.fadeIn.fadeIn('slow', function () {
                $rootScope.scroll();

                $scope.$broadcast('focusInput');
            });

            Selectors.overlay.fadeOut('slow');
        }
    };

    $rootScope.disable = function () {
        if ($scope.isEnabled) {
            $scope.isEnabled = false;

            Selectors.overlay.fadeIn('slow');

            Selectors.fadeIn.fadeOut('slow');
        }
    };

    $rootScope.reload = function () {
        $rootScope.disable();

        document.location.reload(true);
    };

    $rootScope.error = function (title, message) {
        if (isUnloading) {
            // Ignore errors on unload
            return;
        }

        if ($scope.isEnabled) {
            $rootScope.disable();

            $scope.error.title = title;
            $scope.error.message = $sce.trustAsHtml(message);
        } else {
            console.log('Error: ', message);
        }
    };

    $rootScope.flashTitle = function () {
        if (document.hasFocus() || isTitleBlinking) {
            return;
        }

        var flash = true;
        var flashTitle = '** ' + pageTitle + ' **';
        var interval = null;

        isTitleBlinking = true;

        function changeTitle() {
            document.title = flash ? pageTitle : flashTitle;
            flash = !flash;
        }

        interval = setInterval(changeTitle, 1000);

        $(window).focus(function () {
            isTitleBlinking = false;
            clearInterval(interval);
            document.title = pageTitle;
        });
    };

    $rootScope.scroll = function () {
        if (Settings.get('scroll')) {
            var chatWindow = $(Selectors.chatWindowSelector);

            chatWindow.finish();

            var scrollTop = chatWindow[0].scrollHeight;

            chatWindow.animate({
                scrollTop: scrollTop
            });
        }
    };

    $rootScope.deleteMessage = function (row) {
        row.hidden = true; // Assume the row will be deleted
        $('[data-id="' + row.id + '"]').detach();

        Ajax.remove(row);
    };

    $scope.openChannel = function (channel) {
        $(':focus').blur();

        try {
            if (Data.channelName() == channel.name) {
                return;
            }

            Data.channel(channel);

            Ajax.abortRefresh();

            Ajax.refresh();

            $scope.$broadcast('focusInput');
        } catch (e) {
            console.log(e);

            alert(e);
        }
    };

    $scope.joinChannel = function () {
        $(':focus').blur();

        var channel = prompt('Enter channel name:', '#');

        if (channel !== null && channel.length > 0) {
            Ajax.send('/join ' + channel);
        }
    };

    $scope.channels = function () {
        return Data.channelList();
    };

    $scope.activeChannel = function () {
        return Data.channelName();
    };

    $scope.channelTopic = function () {
        return Data.topic();
    };

    $scope.rows = function () {
        return Data.rowList();
    };

    $scope.users = function () {
        return Data.userList();
    };

    $scope.hasPublicRole = function (user) {
        return angular.isObject(user.publicRole);
    };

    $scope.publicRole = function (user) {
        return user.publicRole;
    };

    $scope.displayFormatting = function () {
        return Settings.get('formatting');
    };

    /*
     |--------------------------------------------------------------------------
     | Angular JS Events
     |--------------------------------------------------------------------------
     |
     | Angular event listeners
     |
     */

    $scope.$on('enable', function (e) {
        $rootScope.enable();
    });

    $scope.$on('disable', function (e) {
        $rootScope.disable();
    });

    $scope.$on('error', function (e, title, message) {
        $rootScope.error(title, message);
    });

    $scope.$on('reload', function (e) {
        $rootScope.reload();
    });

    $scope.$on('loggedIn', function (e) {
        $rootScope.enable();
    });

    $scope.$on('clearWindow', function (e) {
        Data.clearRows();
    });

    $scope.$on('submit', function (e, message) {
        Ajax.send(message);
    });

    $scope.$on('newMessages', function (e) {
        $rootScope.flashTitle();

        Audio.playDing();
    });

    $scope.$on('scroll', function(e) {
        $rootScope.scroll();
    });

    /*
    |--------------------------------------------------------------------------
    | Watchers
    |--------------------------------------------------------------------------
    |
    | Scope watchers
    |
    */

    // Fix faulty scrolling when image is loading
    $scope.$watch(function() {
        return $(Selectors.chatWindowSelector).children().last().data('id');
    }, function(newValue, oldValue) {
        if (newValue !== oldValue) {
            $('.embed-image').one('load', function() {
                $rootScope.scroll();
            });
        }
    });

    /*
     |--------------------------------------------------------------------------
     | Init
     |--------------------------------------------------------------------------
     |
     | Initializes the application
     |
     */

    $(document).ready(function () {
        pageTitle = document.title;

        Ajax.start();
    });

    $(window).unload(function () {
        $rootScope.disable();
    });
});