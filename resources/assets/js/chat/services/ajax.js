app.factory('Ajax', function ($rootScope, $interval, $http, Data, Settings) {
    var obj = {};

    var refreshInterval = null;
    var refreshPromise = null;
    var cancelRefresh = null;

    var notificationInterval = null;
    var notificationPromise = null;

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     |
     | Helper methods
     |
     */

    /**
     * Handles HTTP responses
     *
     * @param {int} response
     */
    function handleError(response) {
        obj.stopAjax();

        if (response.status == -1) {
            $rootScope.$broadcast('error', 'Timeout', 'The connection was closed or the server didn\'t respond.');

            return;
        }

        if (response.status == 401) {
            $http.get('/chat/logout').then(function (response) {
                $rootScope.$broadcast('reload');
            }, function (response) {
                $rootScope.$broadcast('reload');
            });

            return;
        }

        if (response.status == 307 || response.status == 308) {
            $rootScope.$broadcast('reload');

            return;
        }

        if (response.status == 409) {
            $rootScope.$broadcast('error', 'Kicked', 'You have been kicked from the channel.');

            return;
        }

        if (response.status == 500) {
            $rootScope.$broadcast('error', 'Server error', 'A server error occurred!');

            return;
        }

        $rootScope.$broadcast('error', response.status + ' Error', 'An unexpected error occurred. Try reloading the page.');
    }

    /*
     |--------------------------------------------------------------------------
     | Intervals
     |--------------------------------------------------------------------------
     |
     | Interval functions
     |
     */

    obj.startAjax = function () {
        obj.refresh();
        obj.notifications();
    };

    obj.startRefresh = function () {
        obj.stopRefresh();

        refreshPromise = $interval(function () {
            obj.refresh();
        }, refreshInterval);
    };

    obj.startNotifications = function () {
        obj.stopNotifications();

        notificationPromise = $interval(function () {
            obj.notifications();
        }, notificationInterval);
    };

    obj.stopAjax = function () {
        obj.stopRefresh();
        obj.stopNotifications();
    };

    obj.stopRefresh = function () {
        $interval.cancel(refreshPromise);
    };

    obj.stopNotifications = function () {
        $interval.cancel(notificationPromise);
    };

    /*
     |--------------------------------------------------------------------------
     | Accessors
     |--------------------------------------------------------------------------
     |
     | Setters and getters
     |
     */

    obj.refreshInterval = function (newInterval) {
        if (newInterval !== undefined) {
            refreshInterval = newInterval;
        }

        return refreshInterval;
    };

    obj.notificationInterval = function (newInterval) {
        if (newInterval !== undefined) {
            notificationInterval = newInterval;
        }

        return notificationInterval;
    };

    /*
     |--------------------------------------------------------------------------
     | Init
     |--------------------------------------------------------------------------
     |
     | Initialization
     |
     */

    obj.start = function () {
        obj.login();
    };


    /*
     |--------------------------------------------------------------------------
     | AJAX
     |--------------------------------------------------------------------------
     |
     | Various AJAX methods
     |
     */

    /**
     * Sends a login request
     */
    obj.login = function () {
        $http.post('/chat/login').then(function (response) {
            Data.storeLoginResponse(response.data);

            var config = response.data.config.interval;

            // Store the AJAX config
            obj.refreshInterval(config.messages);
            obj.notificationInterval(config.notifications);

            // Init the application
            $rootScope.$broadcast('loggedIn');
            $rootScope.$broadcast('setInputLength', response.data.config.maxLength);

            obj.startAjax();

        }, function (response) {
            handleError(response);
        });
    };

    /**
     * Updates the chat
     */
    obj.refresh = function () {
        obj.stopRefresh();

        var request = $http.post('/chat/update', {
            channel: Data.channel(),
            channels: Data.channelList(),
            async: false,
            timeout: 10000
        });

        request.then(function (response) {
            if (response.status != 200) {
                handleError(response);

                return;
            }

            Data.storeRefreshResponse(response.data);

            obj.startRefresh();
        }, function (response) {
            handleError(response);
        });
    };

    obj.notifications = function () {
        obj.stopNotifications();

        $http.post('/chat/notifications').then(function (response) {
            if (response.status != 200) {
                handleError(response);
            }

            Data.notifications(response.data);

            obj.startNotifications();
        });
    };

    obj.abortNotifications = function () {
        obj.stopNotifications();
    };

    /**
     * Sends the user input to the server
     *
     * @param {string} message
     */
    obj.send = function (message) {
        if (typeof message !== 'string') {
            throw new Error('Message must be a string.');
        }

        obj.stopRefresh();

        $http.post('/chat/send', {
            channel: Data.channel(),
            message: message,
            color: Settings.get('color')
        }).then(function (response) {
            if (response.status != 200) {
                handleError(response);

                return;
            }

            if (response.data.channel !== undefined) {
                Data.channel(response.data.channel);
            }

            obj.refresh();

            $rootScope.$broadcast('sent');
        }, function (response) {
            handleError(response);
        });
    };

    obj.remove = function (row) {
        $http.post('/chat/delete', {
            channel: Data.channel(),
            id: row.id
        }).then(function (response) {
            if (response.status != 200) {
                handleError(response);
            }
        }, function (response) {
            handleError(response);
        });
    };

    return obj;
});