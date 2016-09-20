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
