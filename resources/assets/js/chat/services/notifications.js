app.factory('Notifications', function() {
    var obj = {};

    obj.notifications = 0;

    obj.set = function(value) {
        obj.notifications = value;
    };

    obj.get = function() {
        return obj.notifications;
    };

    obj.exists = function() {
        return obj.notifications > 0;
    };

    return obj;
});