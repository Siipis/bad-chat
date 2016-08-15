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