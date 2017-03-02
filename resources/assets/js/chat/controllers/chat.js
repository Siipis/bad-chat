app.controller('chatController', function ($compile, $scope, $rootScope, $sce, Ajax, Audio, Data, Selectors, Styling, Settings) {
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
            Selectors.join.modal('hide');

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

    var scrollTop = 0;

    $rootScope.scroll = function () {
        if (Settings.get('scroll')) {
            var chatWindow = $(Selectors.chatWindowSelector);

            chatWindow.finish();

            try {
                scrollTop = chatWindow[0].scrollHeight;
            } catch (e) {
                console.log(e);
            }

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

            // Scroll after the refresh has finished
            var offRefresh = $scope.$on('refreshed', function () {
                $rootScope.scroll();

                offRefresh();
            });

            $scope.$broadcast('focusInput');
        } catch (e) {
            console.log(e);

            alert(e);
        }
    };

    $scope.joinChannel = function (channel) {
        if (channel === undefined) {
            Ajax.joinable();

            Selectors.join.modal('show');

            return;
        }

        Selectors.join.modal('hide');

        Ajax.send('/join ' + channel);
    };

    $scope.joinable = function () {
        return Data.joinable();
    };

    $scope.channels = function () {
        return Data.channelList();
    };

    $scope.activeChannel = function () {
        return Data.channelName();
    };

    $scope.channelTopic = function () {
        return Styling.addStyles(Data.topic());
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

    $scope.$on('scroll', function (e) {
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
    $scope.$watch(function () {
        return $(Selectors.chatWindowSelector).children().last().data('id');
    }, function (newValue, oldValue) {
        if (newValue !== oldValue) {
            $('.embed-image').one('load', function () {
                $rootScope.scroll();
            });
        }
    });

    /*
     |--------------------------------------------------------------------------
     | jQuery Events
     |--------------------------------------------------------------------------
     |
     | Various jQuery events
     |
     */

    $(document).on('click', '#topic', function () {
        var oldTopic = $(this).text();
        var topic = prompt('Channel topic:', oldTopic);

        if (topic !== null && topic !== oldTopic) {
            Ajax.send('/topic ' + topic);
        }

        topic = oldTopic = null;
    });

    $(document).on('submit', '#join-form', function (e) {
        e.preventDefault();

        var input = $("input[name='channel']", this);
        var channel = input.val();

        input.val(null);

        if (channel.length > 0) {
            Ajax.send('/join ' + channel);
        }

        Selectors.join.modal('hide');
    });

    $(document).on('mouseover', 'a.hoverable', function () {
        if ($(window).width() < 600 || $(window).height() < 600) {
            return;
        }

        var link = this;

        function popover(meta) {
            if (meta.title === undefined) {
                meta.title = '[no title found]';
            }

            if (meta.description === undefined) {
                meta.description = '[no description found]';
            }

            var template = '<div class="popover" role="tooltip">' +
                '<div class="arrow"></div>' +
                '<h3 class="popover-title">' + meta.title + '</h3>' +
                '<div class="popover-content">' + meta.description + '</div>' +
                '</div>';

            $(link).webuiPopover({
                container: Selectors.chatWindowSelector,
                title: meta.title,
                content: meta.description,
                trigger: 'hover',
                template: template
            });

           $(link).webuiPopover('show');
        }

        var url = $(link).attr('href');

        var meta = Data.meta(url);

        if (meta) {
            popover(meta);
        } else {
            meta = Ajax.meta(url);

            meta.done(function (response) {
                Data.meta(url, response.meta);

                popover(response.meta);
            });
        }
    });

    $(document).on('click', 'a.joinable', function(e) {
        e.preventDefault();

        var channel = $(this).text();

        $rootScope.joinChannel(channel);
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

        Selectors.join.html(
            $compile(Selectors.join.html())($scope)
        );

        Ajax.start();

        Ajax.joinable(); // pre-load available channels
    });

    $(window).unload(function () {
        $rootScope.disable();
    });
});