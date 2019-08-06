import $ from 'jquery';

var setChoicesLinkToPage = function(value){
    let page_type = '';
    let post_id = $('#post_ID').val();
    if (value == "spot" || value == "prepare") {
        page_type = value;
    } else {
        page_type = value.$el.prev().find('.acf-input .selected input').val();
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajaxurl,
        data: {
            action: 'woody_get_available_link_page',
            params: page_type,
            id: post_id
        },
        success: function(response){
            $('#acf-field_5d47d332df765 option').each(function(){
                $(this).remove();
            });
            response.forEach(function(element){
                if(element['selected'] == true){
                    var option = '<option value="'+ element['id'] +'" selected="selected">'+ element['title'] +'</option>';
                }else{
                    var option = '<option value="'+ element['id'] +'">'+ element['title'] +'</option>';
                }
                $('#acf-field_5d47d332df765').append(option);
            });
        }
    });
}

acf.addAction('load_field/name=linked_alternative_page', setChoicesLinkToPage);

$('.prepare-onspot').on('change', function(){
    let page_type = $(this).find('.acf-input .selected input').val();
    setChoicesLinkToPage(page_type);
});
