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