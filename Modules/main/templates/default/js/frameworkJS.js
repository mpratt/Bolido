// Html Notifications
function BolidoDisplayNotifications(message, className, place, delay)
{
    if (typeof place == 'undefined' || $(place).length == 0)
        place = 'body';

    if (typeof delay == 'undefined' || isNaN(delay) || delay <= 0)
        delay = 0;

    var notificationDiv = $('<div />', {'text' : message, 'class': className});
    $(notificationDiv).prependTo(place);

    if (delay >= 100)
        $(notificationDiv).delay(delay).animate({opacity: 0}, 'slow', function(){ $(this).remove(); });
}
