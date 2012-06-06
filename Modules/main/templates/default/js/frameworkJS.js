// Html Notifications
function BolidoDisplayNotifications(message, className, place)
{
    if (typeof place == 'undefined' || $(place).length == 0)
    {
        place = 'body';
    }

    var notificationDiv = $('<div />', {'text' : message, 'class': className});

    $(notificationDiv).prependTo(place);
    $(notificationDiv).delay(5000).animate({opacity: 0}, 'slow', function(){ $(this).remove(); });
}