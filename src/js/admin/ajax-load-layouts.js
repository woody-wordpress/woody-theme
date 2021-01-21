!(function ($, undefined) {
    var addBlock = function ( args ) {
    };
    var name = "";
    var clone;

    $(document).on('click', '.acf-button[data-name="add-layout"]', function() {
        addlayoutbutton = $(this);
        name = addlayoutbutton.closest('.acf-flexible-content').find('input[type="hidden"]').attr('name');
        clone = addlayoutbutton.closest('.acf-flexible-content').find('.clones');
    });

    document.addEventListener('click', () => {
      const buttons = document.querySelectorAll('.acf-tooltip.acf-fc-popup>ul>li>a');

      for (const button of buttons) {

        if (document.querySelectorAll('.clones > .' + button.dataset.layout).length <= 0) {

            if (!button.getAttribute('hasListener')) {

              button.addEventListener('click', (e) => {

                  if (clone.find('div[data-layout="' + button.dataset.layout + '"]').length <= 0) {

                    $.ajax({
                        url: ajaxurl,
                        type: 'GET',
                        data: {
                            action: 'generate_layout_acf_clone',
                            layout: button.dataset.layout,
                            name: name
                        },
                        success: function(response) {
                            // Add clone
                            clone.append(response);

                            let fields = acf.getFields({
                                key: 'field_5b043f0525968'
                            });
                            let field;
                            fields.forEach(element => {
                                if (element.$el ==  clone.closest()){

                                }
                            });

                            // Add Block
                            addBlock({
                                layout: button.dataset.layout,
                            });
                        },
                        error: function(error) {

                          console.warn(error);

                        }
                    });

                  }

              });

              button.setAttribute('hasListener', true);
            }
        }
      }
    });
})(jQuery);
