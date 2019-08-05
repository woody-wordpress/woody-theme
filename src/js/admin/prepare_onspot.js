import $ from 'jquery';

var setChoicesLinkToPage = function(field){
    let page_type = $(this).prev().find('.prepare-onspot .acf-input ul li .selected');
    console.log(page_type);
    // $.ajax({
    //     type: 'POST',
    //     dataType: 'json',
    //     url: ajaxurl,
    //     data: {
    //         action: 'woody_get_available_link_page',
    //         params: ''
    //     },
    //     success: function(response){
    //         console.log(response);
    //     }
    // });
}


acf.addAction('load_field/name=linked_alternative_page', setChoicesLinkToPage);

$('.prepare-onspot').on('change', function(){
    console.log($(this).find('.acf-input ul li .selected'));
});
