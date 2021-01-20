!(function ($, undefined) {
    document.addEventListener('click', () => {
      const buttons = document.querySelectorAll('.acf-tooltip.acf-fc-popup>ul>li>a');
      for (const button of buttons) {
        if(document.querySelectorAll('.clones > .' + button.dataset.layout).length <= 0) {
            if (!button.getAttribute('hasListener')) {
                button.addEventListener('click', (e) => {
                    if (document.querySelectorAll('.clones > div[data-layout="' + button.dataset.layout + '"]').length <= 0) {
                        e.preventDefault();

                        $.ajax({
                            url: ajaxurl,
                            type: 'GET',
                            data: {
                                action: 'generate_layout_acf_clone',
                                layout: button.dataset.layout,
                                post_id: $('#post_ID').val()
                            },
                            success: function(response) {
                                $('.clones').each(function() {
                                    $(this).append(response);
                                });
                            },
                            error: function(error) {
                                console.warn(error);
                            }
                        });
                    } else {
                        console.log('add block');
                    }
                });
                button.setAttribute('hasListener', true);
              }
        }
      }
    });
})(jQuery);
