(function ($) {
    'use strict';
    if (momentFeedID != null) {
        $(document).ready(function () {
            // Update special hours
            $.ajax({
                url: 'https://momentfeed-prod.apigee.net/lf/location/store-info/' + momentFeedID,
                type: 'GET',
                success: function (data) {
                    // Update phone and address
                    var address = data.address + ", " + data.locality + ", " + data.region + " " + data.postcode;
                    var addressFull = data.address + ", " + data.addressExtended + " " + data.locality + ", " + data.region + " " + data.postcode;
                    $('.address-phone').html(data.phone);
                    $('.address-card').html(addressFull);
                    $('.address-card').attr('href', 'https://www.google.com/maps/place/' + address);

                    // Update hours if not empty
                    var hoursHTML = '';
                    var hours = data.hours.split(";");
                    var days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    for (var i = 0; i < days.length; i++) {
                        var dayHTML = '<span class="day">' + days[i] + '</span>';
                        var open = '';
                        var close = '';
                        var openHTML = '<span class="hour-open">Closed</span>'; // Default closed
                        var closeHTML = '';

                        // Check if hours exist from MomentFeed
                        if (data.hours.length > 0) {
                            var day = hours[i].split(',');
                            open = convertMilitaryTime(day[1]);
                            close = convertMilitaryTime(day[2]);
                            dayHTML = '<span class="day">' + days[i] + '</span>';
                            openHTML = '<span class="hour-open">' + open + '</span>';
                            closeHTML = '<span class="hour-close">' + close + '</span>';
                        }
                        hoursHTML += '<div class="day-row">' + dayHTML + openHTML + closeHTML + '</div>';
                    }
                    $('.hours-card').html(hoursHTML);

                    // Add special hours
                    if (data.specialHours.length > 0) {
                        var specialtyHoursHTML = '';
                        var specialtyHours = data.specialHours.split(';');
                        $.each(specialtyHours, function (i, e) {
                            var specialtyItem = specialtyHours[i].split(',');
                            var day = specialtyItem[0], dayHTML = '';
                            var open = specialtyItem[1], openHTML = '';
                            var close = specialtyItem[2], closeHTML = '';
                            if (day.length > 0) {
                                day = day.substring(day.indexOf('-') + 1);
                                if (day != null) dayHTML = '<span class="day">' + day + '</span>';
                                if (open != null) openHTML = '<span class="hour-open">' + open + '</span>';
                                if (close != null) closeHTML = '<span class="hour-close">' + close + '</span>';
                                specialtyHoursHTML += '<div class="day-row">' + dayHTML + openHTML + closeHTML + '</div>';
                            }
                        });
                        $(".special-hours-card").html('<strong>Specialty Hours:</strong>');
                        $(".special-hours-card").append(specialtyHoursHTML);
                    }
                    /* if (!data.specialHours) return false;
                    var specialHours = data.specialHours.split(';');
                    $.each(specialHours, function (i, e) {
                        var specialHoursItem = specialHours[i].split(',');
                        if (specialHoursItem.length === 1 && specialHoursItem[0] == "") return;
                        var itemDate = specialHoursItem[0].split('-');
                        var itemMonthDay = itemDate[1] + "/" + itemDate[2];
                        if (specialHoursItem.length > 2) {
                            var timeOfDayStart = "am", timeOfDayEnd = "pm";
                            var itemDateHourStart = specialHoursItem[1].substr(0, 2);
                            if (itemDateHourStart >= 12) { itemDateHourStart -= 12; timeOfDayStart = "pm"; }
                            var itemDateMinStart = specialHoursItem[1].substr(3, 2);
                            if (itemDateMinStart == "0") { itemDateMinStart = "00"; }
                            var timeOfDayStart = "am";
                            var itemDateHourEnd = specialHoursItem[2].substr(0, 2);
                            if (itemDateHourEnd >= 12) { itemDateHourEnd -= 12; timeOfDayEnd = "pm"; }
                            var itemDateMinEnd = specialHoursItem[2].substr(3, 2);
                            if (itemDateMinEnd == "0") { itemDateMinEnd = "00"; }
                            $(".special-hours-card dl").append("<dt><span>" + itemMonthDay + "</span></dt><dd><span><span>" + itemDateHourStart + ":" + itemDateMinStart + timeOfDayStart + " -</span><span>" + itemDateHourEnd + ":" + itemDateMinEnd + timeOfDayEnd + "</span></span></dd>");
                        }
                        else { $(".special-hours-card dl").append("<dt><span>" + itemMonthDay + "</span></dt><dd><span><span>" + specialHoursItem[1] + "</span></span></dd>"); }
                        $(".special-hours-card").show();
                    }); */
                }
            });
        });
    }
    function convertMilitaryTime(time) {
        if (time != null) {
            var period = parseInt(time) < 1200 ? 'am' : 'pm';
            var hour = parseInt(time.substring(0, 2)) % 12;
            var min = ("0" + parseInt(time.substring(2))).slice(-2);
            return hour + ":" + min + period;
        }
    }
})(jQuery);