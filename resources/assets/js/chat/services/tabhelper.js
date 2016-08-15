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