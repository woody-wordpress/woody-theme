!(function ($, undefined) {
    document.addEventListener('click', () => {
      const buttons = document.querySelectorAll('.acf-tooltip.acf-fc-popup>ul>li>a');
      for (const button of buttons) {
        if(document.querySelectorAll('.clones > .' + button.dataset.layout).length <= 0) {
            if (!button.getAttribute('hasListener')) {
              button.addEventListener('click', (e) => {
                  if (document.querySelectorAll('.clones > div[data-layout="' + button.dataset.layout + '"]').length <= 0) {
                      e.preventDefault();

                      $('.clones').each(function(){
                          let clone = $(this);
                          let name = clone.closest('.acf-flexible-content').find('input[type="hidden"]').attr('name');

                          $.ajax({
                              url: ajaxurl,
                              type: 'GET',
                              data: {
                                  action: 'generate_layout_acf_clone',
                                  layout: button.dataset.layout,
                                  post_id: $('#post_ID').val(),
                                  name: name
                              },
                              success: function(response) {
                                  clone.append(response);

                                  // TODO: trigger add block
                              },
                              error: function(error) {
                                console.warn(error);
                              }
                          });
                      });
                  }
              });
              button.setAttribute('hasListener', true);
            }
        }
      }
    });
})(jQuery);
