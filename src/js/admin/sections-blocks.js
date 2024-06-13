!(function ($, undefined) {
    $('#post').each(function () {
        function blockListFilter(field) {
            field.$el.find('.button[data-name="add-layout"]').on('click', function () {
                var getfcTooltip = setInterval(function () {
                    var $acfFcPopupEl = $('.acf-tooltip.acf-fc-popup>ul>li>a');
                    if ($acfFcPopupEl.length > 0) {
                        clearInterval(getfcTooltip);
                        $acfFcPopupEl.each(function () {
                            if ($(this).data('layout') == 'manual_focus_minisheet' || $(this).data('layout') == 'story' || $(this).data('layout') == 'testimonials' || $(this).data('layout') == 'feature' || $(this).data('layout') == 'snowflake_weather' || ($(this).data('layout') == 'auto_focus_leaflets' && !$('body').hasClass('post-type-woody_rdbk_leaflets')) || ($(this).data('layout') == 'shared_leaflets' && !$('body').hasClass('post-type-woody_rdbk_leaflets'))) {
                                $(this).parent('li').css('display', 'none');
                            }
                        });
                    }
                }, 100);
            });
        }

        if (acf !== undefined && acf !== null) {
            acf.addAction('load_field/name=section_content', blockListFilter);
            acf.addAction('append_field/name=section_content', blockListFilter);
        }
    });
})(jQuery);
