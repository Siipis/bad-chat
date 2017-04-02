app.factory('Ajax', function ($q, $rootScope, $interval, $timeout, $http, Data, Settings) {
    var obj = {};

    var disableAjax = false;

    var ajaxTimeout = 10000;
    var maxTimeouts = 10;

    var connectionAttempts = 0;
    var previousResponseStatus = null;

    var refreshTimer = null;
    var refreshInterval = null;
    var refreshPromise = null;

    var notificationTimer = null;
    var notificationInterval = null;

    $(window).unload(function () {
        disableAjax = true;
    });

    /*
     |--------------------------------------------------------------------------
     | Timeouts and disconnects
     |--------------------------------------------------------------------------
     |
     | Handlers for cancelling AJAX
     |
     */

    var xhrPool = [];
    $(document).ajaxSend(function (e, jqXHR, options) {
        xhrPool.push(jqXHR);
    });
    $(document).ajaxComplete(function (e, jqXHR, options) {
        xhrPool = $.grep(xhrPool, function (x) {
            return x != jqXHR
        });
    });

    obj.abortRefresh = function () {
        if (refreshPromise) {
            refreshPromise.resolve();
        }
    };

    obj.abortAllRequests = function () {
        disableAjax = true;

        $.each(xhrPool, function (idx, jqXHR) {
            jqXHR.abort();
        });
    };

    $(window).unload(function () {
        obj.abortAllRequests();
    });


    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     |
     | Helper methods
     |
     */

    function storeResponseStatus(status, disable) {
        if (status == -1) {
            connectionAttempts++;

            if (disable || disable === undefined) {
                $rootScope.$broadcast('disable');
            }
        } else {
            connectionAttempts = 0;
            $rootScope.$broadcast('enable');
        }

        previousResponseStatus = status;
    }

    /**
     * Handles HTTP responses
     *
     * @param {int} response
     */
    function handleError(response) {
        console.log('HTTP error: '+ response.status);

        if (response.status == -1) {
            if (connectionAttempts >= maxTimeouts) {
                $rootScope.$broadcast('reload');
            } else if (refreshInterval) {
                obj.startAjax();
            }

            return;
        }

        obj.stopAjax();

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
        if (disableAjax) {
            return;
        }

        obj.refresh();
        obj.notifications();
    };

    obj.startRefresh = function () {
        if (disableAjax) {
            return;
        }

        obj.stopRefresh();

        refreshInterval = $interval(function () {
            obj.refresh();
        }, refreshTimer);
    };

    obj.startNotifications = function () {
        if (disableAjax) {
            return;
        }

        obj.stopNotifications();

        notificationInterval = $interval(function () {
            obj.notifications();
        }, notificationTimer);
    };

    obj.stopAjax = function () {
        obj.stopRefresh();
        obj.stopNotifications();
    };

    obj.stopRefresh = function () {
        $interval.cancel(refreshInterval);
    };

    obj.stopNotifications = function () {
        $interval.cancel(notificationInterval);
    };

    /*
     |--------------------------------------------------------------------------
     | Accessors
     |--------------------------------------------------------------------------
     |
     | Setters and getters
     |
     */

    obj.refreshTimer = function (newTimer) {
        if (newTimer !== undefined) {
            refreshTimer = newTimer;
        }

        return refreshTimer;
    };

    obj.notificationTimer = function (newTimer) {
        if (newTimer !== undefined) {
            notificationTimer = newTimer;
        }

        return notificationTimer;
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
            storeResponseStatus(response.status);

            Data.storeLoginResponse(response.data);

            var config = response.data.config.interval;

            // Store the AJAX config
            obj.refreshTimer(config.messages);
            obj.notificationTimer(config.notifications);

            // Init the application
            $rootScope.$broadcast('loggedIn');
            $rootScope.$broadcast('setInputLength', response.data.config.maxLength);

            obj.startAjax();

        }, function (response) {
            storeResponseStatus(response.status);

            handleError(response);
        });
    };

    /**
     * Updates the chat
     */
    obj.refresh = function () {
        obj.stopRefresh();

        var canceller = $q.defer();

        var request = $http.post('/chat/update', {
            channel: Data.channel(),
            channels: Data.channelList()
        }, {
            async: false,
            timeout: canceller.promise
        });

        var requestTimeout = $timeout(function () {
            canceller.resolve();
        }, ajaxTimeout);

        request.then(function (response) {
            storeResponseStatus(response.status, false);

            if (response.status != 200) {
                handleError(response);

                return;
            }

            Data.storeRefreshResponse(response.data);

            $rootScope.$broadcast('refreshed');

            obj.startRefresh();
        }, function (response) {
            storeResponseStatus(response.status, false);

            handleError(response);
        });

        request.finally(function () {
            $timeout.cancel(requestTimeout);
        });

        refreshPromise = canceller;
    };

    obj.notifications = function () {
        obj.stopNotifications();

        $http.post('/chat/notifications', null, {
            timeout: ajaxTimeout
        }).then(function (response) {
            storeResponseStatus(response.status, false);

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
            storeResponseStatus(response.status);

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

    var returnPreviousJoinable = false;

    obj.joinable = function () {
        if (returnPreviousJoinable) {
            return;
        }

        $http.get('/chat/joinable')
            .then(function (response) {
                if (response.status != 200) {
                    handleError(response);

                    return;
                }

                Data.joinable(response.data);

                window.setTimeout(function () {
                    returnPreviousJoinable = true;
                }, 60);
            }, function (response) {
                handleError(response);
            });
    };

    obj.meta = function (url) {
        return $.ajax({
            method: 'post',
            url: 'https://api.urlmeta.org/?url='+ url,
            crossDomain: true
        });
    };

    return obj;
});