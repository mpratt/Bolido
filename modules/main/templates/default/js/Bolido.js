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
     * Displays notifications.
     *
     * @return void.
     */
    notify: function (obj) {
        $.each(obj, function (index, value){
            if (typeof value.place == 'undefined' || $(value.place).length == 0)
                value.place = 'body';

            if (typeof value.delay == 'undefined' || isNaN(value.delay) || value.delay <= 100)
                value.delay = 0;

            var notificationDiv = $('<div />', {'text' : value.message, 'class': value.class});
            $(notificationDiv).prependTo(value.place);

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
