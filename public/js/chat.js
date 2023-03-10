$(document).ready(function() {
    var alertTimeout = window.setTimeout(function() {
        $(".alert.fade-out").fadeOut('slow');
    }, 10000);
});
/*!
 * JavaScript Cookie v2.2.0
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
;(function (factory) {
	var registeredInModuleLoader = false;
	if (typeof define === 'function' && define.amd) {
		define(factory);
		registeredInModuleLoader = true;
	}
	if (typeof exports === 'object') {
		module.exports = factory();
		registeredInModuleLoader = true;
	}
	if (!registeredInModuleLoader) {
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

				// We're using "expires" because "max-age" is not supported by IE
				attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';

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

				var stringifiedAttributes = '';

				for (var attributeName in attributes) {
					if (!attributes[attributeName]) {
						continue;
					}
					stringifiedAttributes += '; ' + attributeName;
					if (attributes[attributeName] === true) {
						continue;
					}
					stringifiedAttributes += '=' + attributes[attributeName];
				}
				return (document.cookie = key + '=' + value + stringifiedAttributes);
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
				var cookie = parts.slice(1).join('=');

				if (!this.json && cookie.charAt(0) === '"') {
					cookie = cookie.slice(1, -1);
				}

				try {
					var name = parts[0].replace(rdecode, decodeURIComponent);
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
			return api.call(api, key);
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
        console.log('HTTP error: ' + response.status);

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

        if (response.status == 422) {
            $rootScope.$broadcast('error', 'Say what?', 'The information you sent couldn\'t be read by the server.');

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

    obj.upload = function (data) {
        $rootScope.$broadcast('disable');

        $http({
            url: '/chat/upload',
            method: 'post',
            data: data,
            headers: {'Content-Type': undefined}
        }).then(function (response) {
            if (response.status != 200) {
                handleError(response);

                return;
            }

            $rootScope.$broadcast('enable');

            $rootScope.addCode('img', response.data.image);
        }, function (response) {
            $rootScope.$broadcast('enable');

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
            url: 'https://api.urlmeta.org/?url=' + url,
            crossDomain: true
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
    var joinable = {};
    var channel = {};
    var channelList = {};
    var topicList = {};
    var userList = {};
    var rowList = {};

    var meta = {};

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

    obj.joinable = function (newJoinable) {
        if (newJoinable !== undefined) {
            joinable = newJoinable;
        }

        return joinable;
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

        // Show notification
        if (!row.isOwnMessage) {
            if (row.notify !== false) {
                $rootScope.$broadcast('notify', row.notify);
            } else {
                $.each(config.settings.highlight, function (key, value) {
                    if (row.message.indexOf(value.trim()) >= 0) {
                        $rootScope.$broadcast('notify', {
                            type: 'highlight',
                            name: row.name,
                            message: row.message
                        });

                        return false;
                    }
                });
            }
        }

        rows = null; // free up memory
    };

    obj.addRows = function (channelKey, rows) {
        var soundWasPlayed = false;
        var addedRows = 0;

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
            addedRows++;
        });

        if (addedRows > 0) {
            $rootScope.$broadcast('scroll');
        }
    };

    obj.meta = function (url, newMeta) {
        if (newMeta === undefined) {
            return meta[url];
        }

        meta[url] = newMeta;
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

    obj.storeJoinableResponse = function(data) {
        obj.joinable(data);
    };

    return obj;
});
app.factory('Notifications', function (Data) {
    var obj = {};

    function shouldNotify(note) {
        var config = Data.config();
        var notify = config.settings.notify;

        if (note.type == 'whisper' || note.type == 'highlight') {
            return notify.mentions == 'on';
        }

        if (note.type == 'invite' || note.type == 'new_vouch') {
            return notify.invites == 'on';
        }

        return notify.channel == 'on';
    }

    obj.send = function(note) {
        if (shouldNotify(note) === false) return;

        var title = "New event:";
        var message = note.message;
        var timeout = 4;

        if (note.type == 'whisper') {
            title = "New whisper:";
            message = note.name + ' whispers: "' + message + '"';
            timeout = 10;
        }

        if (note.type == 'highlight') {
            title = "You were mentioned!";
            message = note.name + ' says: "' + message + '"';
            timeout = 10;
        }

        if (note.type == 'invite') {
            title = "You have a new invite!";
            timeout = 10;
        }

        if (note.type == 'new_vouch') {
            title = "New vouch!";
            timeout = 10;
        }

        Push.create(title, {
            body: message,
            icon: 'icon.jpg',
            timeout: timeout * 1000,
            onClick: function() {
                window.focus();
                this.close();
            }
        });
    };

    return obj;
});
app.factory('Selectors', function() {
    var obj = {};

    obj.fadeIn = $('.fade-in');
    obj.chat = $('#chat-container');
    obj.errors = $('#chat-errors');
    obj.overlay = $('#overlay');
    obj.commands = $('#commands-overlay');
    obj.join = $('#join-overlay');
    obj.chatWindowSelector = '#chat-window';
    obj.chatWindow = $(obj.chatWindowSelector);
    obj.form = $('#chat-input form');
    obj.textarea = $('textarea', obj.form);

    obj.image = {
        overlay: $('#image-overlay'),
        form: {
            link: 'form#image-overlay__link',
            upload: 'form#image-overlay__upload'
        },
        input: {
            link: 'input#inputUrl',
            upload: 'input#inputUpload'
        },
        preview: $('img#image-preview')
    };

    obj.link = {
        overlay: $('#link-overlay'),
        form: 'form#link-overlay__form',
        input: $('input#inputLink')
    };

    obj.emojilist = $('#emojilist');
    obj.emojiSelectorClass = 'emojiSelector';

    return obj;
});
app.factory('Settings', function ($rootScope) {
    var default_color = 6;

    var obj = {};

    obj.get = function (key) {
        var settings;

        if (!angular.isObject(obj.settings)) {
            try {
                try {
                    settings = localStorage.getItem('settings');
                } catch (e) {
                    console.log('Local storage is not available.');
                }

                if (settings === undefined) {
                    settings = Cookies.get('settings');
                }

                settings = JSON.parse(settings);
            } catch (e) {
                console.log('Could not parse settings!');
            }

            obj.settings = settings;
        } else {
            settings = obj.settings;
        }

        if (!angular.isObject(settings)) {
            settings = {
                'color': default_color,
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

        try {
            localStorage.setItem('settings', JSON.stringify(settings));
        } catch (e) {
            console.log('Local storage is not available.');
        }

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
        ':3': ':fox:',
        '(y)': ':thumbsup:'
    };

    var codes = {
        '(^| )(http[s]?://[^\\\s]+)': '$1<a href="$2" target="_blank" class="hoverable">$2</a>',
        '\\[b\\](.*)\\[\/b\\]': '<b>$1</b>',
        '\\[i\\](.*)\\[\/i\\]': '<em>$1</em>',
        '\\[u\\](.*)\\[\/u\\]': '<u>$1</u>',
        '\\[s\\](.*)\\[\/s\\]': '<s>$1</s>',
        '\\[quote\\](.*)\\[\/quote\\]': '<blockquote>$1</blockquote>',
        '\\[quote=(.*)\\](.*)\\[\/quote\\]': '<blockquote>$2<footer>$1</footer></blockquote>',
        '\\[url=(.*)\\](.*)\\[\/url\\]': '<a href="$1" target="_blank" class="hoverable">$2</a>',
        '\\[url\\](.*)\\[\/url\\]': '<a href="$1" target="_blank" class="hoverable">$1</a>',
        '\\[img\\](.*)\\[\/img\\]': '<a href="$1" target="_blank"><img src="$1" class="embed-image" alt="$1" /></a>',
        '(^| )(#[a-zA-Z_-]+)': '$1<a href="javascript:void(0);" class="joinable" title="Click to join $2!">$2</a>'
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
        try {
            input = parseEmoji(input);
            input = parseCode(input);
        } catch (e) {
            console.log(e);
        }

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
app.controller('chatController', function ($compile, $scope, $rootScope, $sce, Ajax, Audio, Data, Notifications, Selectors, Styling, Settings) {
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

            Selectors.errors.hide();

            Selectors.overlay.fadeOut('slow');
        }
    };

    $rootScope.disable = function () {
        if ($scope.isEnabled) {
            $scope.isEnabled = false;

            $('.modal').modal('hide'); // Hide all modals

            Selectors.errors.hide();

            Selectors.overlay.fadeIn('slow');

            Selectors.fadeIn.fadeOut('slow');
        }
    };

    $rootScope.stop = function () {
        if ($scope.isEnabled) {
            $scope.isEnabled = false;

            $('.modal').modal('hide'); // Hide all modals

            Selectors.overlay.fadeOut(100);

            Selectors.fadeIn.fadeIn(100);

            Selectors.errors.fadeIn('slow');
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
            $rootScope.stop();

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

    $scope.$on('notify', function (e, note) {
        Notifications.send(note);
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

    $(document).on('click', '#topic', function (e) {
        if (e.target !== this) return; // ignore topic children

        var oldTopic = Data.topic();
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

    // @url https://github.com/sandywalker/webui-popover
    $(document).on('mouseover', 'a.hoverable', function () {
        if ($(window).width() < 600 || $(window).height() < 600) {
            return;
        }

        var link = this;

        function popover(meta) {
            if (meta.title.length == 0 && meta.description.length == 0) {
                return;
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
                cache: false,
                delay: {
                    show: 500,
                    hide: null
                },
                autoHide: 5000,
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

        $scope.joinChannel(channel);
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

    // Init modals
    $(document).ready (function() {
        var modals = $('modal');

        modals.modal('hide');

        modals.on('shown.bs.modal', function() {
            $(this).find("input:visible:first").focus();
        });
    });


    $(window).unload(function () {
        $rootScope.disable();
    });
});
app.controller('inputController', function ($scope, $rootScope, Ajax, Data, TabHelper, Selectors) {
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
            if (e.which == 9 && !e.ctrlKey || e.keyCode == 9 && !e.ctrlKey) {
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
    $(document).on('submit', Selectors.image.form.link, function (e) {
        e.preventDefault();

        var input = $(this).serializeArray();

        var url = input[1].value;

        $rootScope.addCode('img', url);

        $(this)[0].reset();

        Selectors.image.overlay.modal('hide');

        Selectors.textarea.focus();
    });


    // Modal form event handling for image uploads
    $(document).on('submit', Selectors.image.form.upload, function (e) {
        e.preventDefault();

        var data = new FormData($(Selectors.image.form.upload)[0]);

        $(this)[0].reset();

        Selectors.image.overlay.modal('hide');

        Selectors.textarea.focus();

        Ajax.upload(data);
    });

    // Image upload preview
    $(document).on('change', Selectors.image.input.upload, function (e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                Selectors.image.preview.attr('src', e.target.result);
            };

            reader.readAsDataURL(this.files[0]);
        }
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

    // Reset all modal forms on close
    $('.modal').on('hidden.bs.modal', function (e) {
        $('form', this).each(function () {
            this.reset();
        });

        Selectors.image.preview.attr('src', '');
    })
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
        return Styling.addStyles(row.message);
    };
});
//# sourceMappingURL=chat.js.map
