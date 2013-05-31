/**
 * Bolido.js
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
var Bolido = {

    /**
     * Keeps a session alive by sending a pulse to the main/alive/
     * path.
     *
     * @return void
     */
    pulse: function () {
        var tmpi = new Image();
        tmpi.src = mainUrl + '/main/alive/?seed=' + Math.random();
        try { console.log('KeepAlive request Sent!'); } catch (e) {}
    },

    /**
     * A quick check if the browser has support for cookies.
     * Taken from https://github.com/Modernizr/Modernizr/commit/33f00fbbeb12e92bf24711ea386e722cce6f60cc
     *
     * @return bool
     */
    cookiesEnabled: function (){
        if (navigator.cookieEnabled)
            return true;

        document.cookie = "bolidoCookieTest=1"; /* Create a Cookie */
        var ret = document.cookie.indexOf("bolidoCookieTest=") != -1;
        document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT"; /* Delete the cookie*/

        return ret;
    },

    /**
     * Displays notifications.
     *
     * @return void.
     */
    notify: function (obj) {
        $.each(obj, function (index, value){
            if (typeof value.prepend == 'undefined' || $(value.prepend).length == 0)
                value.prepend = 'body';

            if (typeof value.delay == 'undefined' || isNaN(value.delay) || value.delay <= 100)
                value.delay = 0;

            var notificationDiv = $('<div />', {'text' : value.message, 'class': value.class});
            $(notificationDiv).prependTo(value.prepend);

            if (value.delay > 0)
                $(notificationDiv).delay(value.delay).animate({opacity: 0}, 'slow', function(){ $(this).remove(); });
        });
    },

    /**
     * Initializes this object
     *
     * @return void
     */
    init: function () {
        window.setInterval(this.pulse, 600000);
        try { console.log('Bolido.js was initialized'); } catch (e) {}
    }
};

Bolido.init();
