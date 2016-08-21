$(document).ready(function() {
    var alertTimeout = window.setTimeout(function() {
        $(".alert.fade-out").fadeOut('slow');
    }, 10000);
});
/*!
 * JavaScript Cookie v2.1.1
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
;(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(factory);
	} else if (typeof exports === 'object') {
		module.exports = factory();
	} else {
		var OldCookies = window.Cookies;
		var api = window.Cookies = factory();
		api.noConflict = function () {
			window.Cookies = OldCookies;
			return api;
		};
	}
}(function () {
	function extend () {
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			var attributes = arguments[ i ];
			for (var key in attributes) {
				result[key] = attributes[key];
			}
		}
		return result;
	}

	function init (converter) {
		function api (key, value, attributes) {
			var result;
			if (typeof document === 'undefined') {
				return;
			}

			// Write

			if (arguments.length > 1) {
				attributes = extend({
					path: '/'
				}, api.defaults, attributes);

				if (typeof attributes.expires === 'number') {
					var expires = new Date();
					expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
					attributes.expires = expires;
				}

				try {
					result = JSON.stringify(value);
					if (/^[\{\[]/.test(result)) {
						value = result;
					}
				} catch (e) {}

				if (!converter.write) {
					value = encodeURIComponent(String(value))
						.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
				} else {
					value = converter.write(value, key);
				}

				key = encodeURIComponent(String(key));
				key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
				key = key.replace(/[\(\)]/g, escape);

				return (document.cookie = [
					key, '=', value,
					attributes.expires && '; expires=' + attributes.expires.toUTCString(), // use expires attribute, max-age is not supported by IE
					attributes.path    && '; path=' + attributes.path,
					attributes.domain  && '; domain=' + attributes.domain,
					attributes.secure ? '; secure' : ''
				].join(''));
			}

			// Read

			if (!key) {
				result = {};
			}

			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling "get()"
			var cookies = document.cookie ? document.cookie.split('; ') : [];
			var rdecode = /(%[0-9A-Z]{2})+/g;
			var i = 0;

			for (; i < cookies.length; i++) {
				var parts = cookies[i].split('=');
				var name = parts[0].replace(rdecode, decodeURIComponent);
				var cookie = parts.slice(1).join('=');

				if (cookie.charAt(0) === '"') {
					cookie = cookie.slice(1, -1);
				}

				try {
					cookie = converter.read ?
						converter.read(cookie, name) : converter(cookie, name) ||
						cookie.replace(rdecode, decodeURIComponent);

					if (this.json) {
						try {
							cookie = JSON.parse(cookie);
						} catch (e) {}
					}

					if (key === name) {
						result = cookie;
						break;
					}

					if (!key) {
						result[name] = cookie;
					}
				} catch (e) {}
			}

			return result;
		}

		api.set = api;
		api.get = function (key) {
			return api(key);
		};
		api.getJSON = function () {
			return api.apply({
				json: true
			}, [].slice.call(arguments));
		};
		api.defaults = {};

		api.remove = function (key, attributes) {
			api(key, '', extend(attributes, {
				expires: -1
			}));
		};

		api.withConverter = init;

		return api;
	}

	return init(function () {});
}));

/**
 * Angular JS
 *
 * @type {IModule}
 */

var app = angular.module('chat', ['ngSanitize']);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);
app.factory('TabHelper', function (Data) {
    var obj = {};

    var isActive = false;
    var origInput = null;
    var tabIndex = -1;
    var tabPattern = null;
    var tabRemnant = null;
    var tabList = [];

    function getPattern(input) {
        var split = input.split(' ');

        return split[split.length - 1];
    }

    function getRemnant(input, pattern) {
        return input.substr(0, input.length - pattern.length);
    }

    obj.init = function(input) {
        if (isActive) {
            return;
        }

        origInput = input;
        tabIndex = -1;

        tabPattern = getPattern(input);

        tabList = obj.userList();
        tabRemnant = getRemnant(input, tabPattern);

        obj.setActive(true);
    };

    obj.next = function() {
        if (!isActive) {
            return;
        }

        if (tabList.length == 0) {
            return origInput;
        }

        tabIndex++;

        if (tabIndex == tabList.length) {
            tabIndex = 0;
        }

        return tabRemnant + tabList[tabIndex];
    };

    obj.setActive = function(active) {
        isActive = active;
    };

    obj.tabList = function() {
        return tabList;
    };

    obj.userList = function() {
        var array = [];

        if (tabPattern == null) {
            return array;
        }

        var pattern = new RegExp('^' + tabPattern, 'i');

        $.each(Data.userList(), function(i, user) {
            if (tabPattern.length == 0 || pattern.test(user.name)) {
                array.push(user.name);
            }
        });

        return array;
    };

    return obj;
});
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
        console.log('Exiting...');
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

    obj.abortRefresh = function() {
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

    $(window).unload(function() {
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

    function storeResponseStatus(status) {
        if (status == -1) {
            connectionAttempts++;
            $rootScope.$broadcast('disable');
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

        var requestTimeout = $timeout(function() {
            canceller.resolve();
        }, ajaxTimeout);

        request.then(function (response) {
            storeResponseStatus(response.status);

            if (response.status != 200) {
                handleError(response);

                return;
            }

            Data.storeRefreshResponse(response.data);

            obj.startRefresh();
        }, function (response) {
            storeResponseStatus(response.status);

            handleError(response);
        });

        request.finally(function() {
            $timeout.cancel(requestTimeout);
        });

        refreshPromise = canceller;
    };

    obj.notifications = function () {
        obj.stopNotifications();

        $http.post('/chat/notifications', null, {
            timeout: ajaxTimeout
        }).then(function (response) {
            storeResponseStatus(response.status);

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

    return obj;
});
app.factory('Audio', function ($rootScope, Settings) {
    var audio = false;
    var useAudio = false;

    $(window).focus(function () {
        useAudio = false;
    }).blur(function () {
        useAudio = true;
    });

    function initAudio() {
        if (!audio) {
            audio = $('audio')[0];
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Init
     |--------------------------------------------------------------------------
     |
     | Init the object
     |
     */

    var obj = {};

    obj.playDing = function () {
        initAudio();

        if (useAudio && Settings.get('sound')) {
            audio.play();
        }
    };

    return obj;
});
app.factory('Data', function ($rootScope) {
    var config = {};
    var notifications = 0;
    var user = {};
    var channel = {};
    var channelList = {};
    var topicList = {};
    var userList = {};
    var rowList = {};

    var obj = {};

    /*
     |--------------------------------------------------------------------------
     | Accessors
     |--------------------------------------------------------------------------
     |
     | Getters and setters
     |
     */

    obj.config = function (newConfig) {
        if (newConfig !== undefined) {
            config = newConfig;
        }

        return config;
    };

    obj.notifications = function (newNotifications) {
        if (newNotifications !== undefined) {
            if (!angular.isNumber(newNotifications)) {
                throw "Notification count must be an integer.";
            }

            notifications = newNotifications;
        }

        return notifications;
    };

    obj.user = function (newUser) {
        if (newUser !== undefined) {
            user = newUser;
        }

        return user;
    };

    obj.channel = function (newChannel) {
        if (newChannel !== undefined) {
            channel = newChannel;
        }

        return channel;
    };

    obj.channelName = function () {
        if (channel === undefined) {
            return null;
        }

        return channel.name;
    };

    obj.channelTopic = function () {
        if (channel === undefined) {
            return null;
        }

        return channel.topic;
    };

    obj.channelList = function (newChannelList) {
        if (newChannelList !== undefined) {
            channelList = newChannelList;
        }

        return channelList;
    };

    obj.topic = function (channelKey, newTopic) {
        if (channelKey === undefined) {
            channelKey = obj.channelName();
        }

        if (newTopic !== undefined) {
            topicList[channelKey] = newTopic;
        }

        var topic = topicList[channelKey];

        return topic === undefined ? null : topic;
    };

    obj.userList = function (newUserList) {
        if (newUserList !== undefined) {
            userList = newUserList;
        }

        return userList;
    };

    obj.rowList = function (channelKey) {
        if (channelKey === undefined) {
            channelKey = obj.channelName();
        }

        return rowList[channelKey];
    };

    obj.clearRows = function(channelKey) {
        if (channelKey === undefined) {
            channelKey = obj.channelName();
        }

        rowList[channelKey] = [];
    };

    obj.addRow = function (channelKey, row) {
        var rows = rowList[channelKey] === undefined ? [] : rowList[channelKey];
        var maxMessages = config.settings.maxMessages;

        var previousRow = rows[rows.length - 1];

        if (previousRow === undefined || previousRow.id != row.id) { // prevent duplicates
            rows.push(row);
        }

        if (rows.length > maxMessages) {
            rows = rows.slice(-maxMessages);
        }

        rowList[channelKey] = rows;
    };

    obj.addRows = function (channelKey, rows) {
        var soundWasPlayed = false;

        $.each(rows, function (i, row) {
            // Handle special rows
            if (row.type == 'system') {
                switch (row.name) {
                    case 'delete_row':
                        var index = -1;

                        $.each(rowList[channelKey], function(i, item) {
                            if (item.id == row.message.id) {
                                index = i;
                                return true;
                            }
                        });

                        if (index != -1) {
                            rowList[channelKey].splice(index, 1);
                        }

                        return;
                }
            }

            // Play notification
            if (!soundWasPlayed && !row.isOwnMessage) {
                $rootScope.$broadcast('newMessages');
            }

            // Add the row
            obj.addRow(channelKey, row);
        });
    };

    /*
     |--------------------------------------------------------------------------
     | Ajax handlers
     |--------------------------------------------------------------------------
     |
     | Store the raw Ajax output
     |
     */

    obj.storeLoginResponse = function (data) {
        obj.user(data.user);
        obj.channel(data.channel);
        obj.topic(data.channel.name, data.channel.topic);

        obj.config(data.config);
    };

    obj.storeRefreshResponse = function (data) {
        obj.channel(data.channel);
        obj.topic(data.channel.name, data.channel.topic);
        obj.channelList(data.channels);
        obj.userList(data.users);

        obj.addRows(data.channel.name, data.rows);
    };

    return obj;
});
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
app.factory('Settings', function ($rootScope) {
    var obj = {};

    obj.get = function (key) {
        var settings;

        if (!angular.isObject(obj.settings)) {
            try {
                settings = JSON.parse(Cookies.get('settings'));
            } catch (e) {
            }

            obj.settings = settings;
        } else {
            settings = obj.settings;
        }

        if (!angular.isObject(settings)) {
            settings = {
                'color': 0,
                'scroll': true,
                'formatting': true,
                'sound': true
            };
        }

        if (key === undefined) {
            return settings;
        }

        return settings[key];
    };

    obj.set = function (key, value) {
        var settings = obj.get();

        if (settings === undefined) {
            settings = {};
        }

        settings[key] = value;

        if (!angular.isObject(settings)) {
            throw 'Settings is not an object! Given: ' + settings;
        }

        obj.settings = settings;

        Cookies.set('settings', JSON.stringify(settings));

        console.log('Settings are now ', obj.settings);
    };

    obj.toggle = function (setting) {
        if (obj.get(setting)) {
            obj.set(setting, false);
        } else {
            obj.set(setting, true);
        }
    };

    $rootScope.toggleSetting = function (setting) {
        $(':focus').blur();

        obj.toggle(setting);
    };

    $rootScope.selectSetting = function (setting, value) {
        $(':focus').blur();

        obj.set(setting, value);
    };

    return obj;
});

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

    /*
     |--------------------------------------------------------------------------
     | Watchers
     |--------------------------------------------------------------------------
     |
     | Angular watchers
     |
     */

    $scope.$watch(function () {
        var chatWindow = $(Selectors.chatWindowSelector);

        if (chatWindow.length) {
            return chatWindow.children().length;
        }

        return 0;
    }, function (newCount, oldCount) {
        var chatWindow = $(Selectors.chatWindowSelector);

        if (newCount > 0 && newCount != oldCount) {
            chatWindow.finish();

            if (Settings.get('scroll')) {
                var scrollTop = chatWindow[0].scrollHeight * 1.2;

                chatWindow.animate({
                    scrollTop: scrollTop
                });
            }
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
        try {
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
app.controller('menuController', function ($scope, Data, Selectors, Settings) {
    $scope.hasNotifications = function () {
        return Data.notifications() > 0;
    };

    $scope.notificationCount = function() {
        return Data.notifications();
    };

    $scope.formattingIcon = function () {
        return 'glyphicon-heart' + (Settings.get('formatting') ? '' : '-empty');
    };

    $scope.soundIcon = function () {
        return 'glyphicon-volume-' + (Settings.get('sound') ? 'up' : 'off');
    };

    $scope.scrollIcon = function () {
        return 'glyphicon-' + (Settings.get('scroll') ? 'pause' : 'play');
    };

    $scope.showCommands = function() {
        Selectors.commands.modal();
    };
});
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
        if (row.type == 'post' || row.type == 'whisper' || row.type == 'emote') {
            return Styling.addStyles(row.message);
        }

        return row.message;
    };
});
//# sourceMappingURL=chat.js.map
