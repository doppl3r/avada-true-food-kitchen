(function ($) {
    'use strict';
    if (momentFeedID != null) {
        $(document).ready(function () {
            $('[location_id]').attr('location_id', momentFeedID);
            $('[location_id]').each(function () {
                var item = $(this);
                var card_id = item.attr('card_id');
                var location_id = item.attr('location_id');
                $.ajax({
                    url: 'https://partner-api.momentfeed.com/locations/cards?location_id=' + location_id + '&card_id=' + card_id,
                    type: 'GET',
                    headers: {
                        token: 'IFWKRODYUFWLASDC'
                    },
                    success: function (result) {
                        var result = $(result);
                        if (card_id == 1) {
                            $('.address-phone').html(result.find('[itemprop="telephone"]').html());
                            result.find('.mf_card > [itemprop="streetAddress"] > span > [itemprop="streetAddress"] > span > h4:last-of-type').remove();
                            result.find('.mf_card > [itemprop="streetAddress"] > span > [itemprop="streetAddress"] > span > h4:last-of-type').remove();
                            $('.address-card').html(result.find('.mf_card > [itemprop="streetAddress"]').text());
                        } else if (card_id == 7) {
                            item.html(result.html());
                        }
                    }
                });
            });
        });
        $(window).load(function () {
            $('.address-phone').html($('[itemprop="telephone"]').html());
            $('.address-card').html($('[itemprop="streetAddress"]').html());
        });
    }
})(jQuery);