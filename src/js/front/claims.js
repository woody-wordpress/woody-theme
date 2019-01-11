import $ from 'jquery';

$('.claims-blocks-wrapper').each(function() {
    var $this = $(this),
        the_cookie = Cookies.getJSON('HideWoodyClaim'),
        url = window.location.href,
        timeStamp = new Date().getTime();

    if (typeof the_cookie == 'undefined') {
        the_cookie = [];
    }

    $.ajax({
        type: 'POST',
        url: '/wp-json/woody/claims-blocks',
        data: url,
        success: function(items) {
            for (var item_i = 0; item_i < items.length; item_i++) {
                for (var i = 0; i < the_cookie.length; i++) {
                    if (the_cookie[i].id === $(items[item_i]).data('blockid') && the_cookie[i].expire > timeStamp) {
                        items.splice(items[item_i], 1);
                    }
                }
            };
            var item = items[Math.floor(Math.random() * items.length)];
            $this.append(item);
            var swiper_options = $this.find('.claims-swiper').data('options');
            var closeClaim = $this.find('.claim-close-button');
            new Swiper('.claims-swiper', swiper_options);

            closeClaim.click(function() {
                $this.remove();
                HideWoodyClaimCookie();
            });
        },
        error: function(data) {
            console.error('claim', data);
        },
    });

    var HideWoodyClaimCookie = function() {
        var claimBlockId = $this.find('.woody-component-claims-block').data('blockid'),
            time = new Date(),
            timeStamp2hLater = time.setHours(time.getHours() + 2);
        the_cookie.push({ id: claimBlockId, expire: timeStamp2hLater });
        Cookies.set('HideWoodyClaim', the_cookie, { expires: 1 });
    }
});
