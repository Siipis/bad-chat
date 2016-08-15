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