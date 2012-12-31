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
    keepAlive : function () {
        var tmpi = new Image();
        tmpi.src = mainUrl + '/main/alive/?seed=' + Math.random();
        try { console.log('KeepAlive request Sent!'); } catch (e) {}
    },

    notify: function (message, className, place, delay) {
        if (typeof place == 'undefined' || $(place).length == 0)
            place = 'body';

        if (typeof delay == 'undefined' || isNaN(delay) || delay <= 100)
            delay = 0;

        var notificationDiv = $('<div />', {'text' : message, 'class': className});
        $(notificationDiv).prependTo(place);

        if (delay > 0)
            $(notificationDiv).delay(delay).animate({opacity: 0}, 'slow', function(){ $(this).remove(); });
    }
};

var BolidoSessionPulse = window.setInterval(Bolido.keepAlive, 600000);
