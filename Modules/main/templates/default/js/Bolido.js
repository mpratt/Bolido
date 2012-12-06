/**
 * Bolido.js
 * This object is important for the browser
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
var Bolido = {
    config : {
        sessionPingTime: 600000,
        nextSessionPing: new Date().getTime() + this.config.sessionPingTime
    },

    keepAlive : function () {
        if (this.config.nextSessionPing <= new Date().getTime()) {
            var tmpi = new Image();
            tmpi.src = mainUrl + '/main/alive/?seed=' + Math.random(); nextSessionPing = new Date().getTime() + sessionPingTime;
            try { console.log('KeepAlive request Sent!'); } catch (e) {}
        }
        window.setTimeout(this.keepAlive(), 120000);
    },

    notify: function (message, className, place, delay) {
        if (typeof place == 'undefined' || $(place).length == 0)
            place = 'body';

        if (typeof delay == 'undefined' || isNaN(delay) || delay <= 0)
            delay = 0;

        var notificationDiv = $('<div />', {'text' : message, 'class': className});
        $(notificationDiv).prependTo(place);

        if (delay >= 100)
            $(notificationDiv).delay(delay).animate({opacity: 0}, 'slow', function(){ $(this).remove(); });
    }
};

window.setTimeout(Bolid.keepAlive(), 300000);

