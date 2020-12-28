import $ from 'jquery';

$('.acf-field-select[data-name="infolive_block_select_resort"] select').change(function(e) { 
    if (typeof acf == 'undefined') {
        return;
    }
    update_zones_on_station_change(e, $, $(this));
    $(this).trigger('ready');
});

function update_zones_on_station_change(e, $, select) {
    var newStation = select.val();
    var zonesList = select.closest('.acf-fields').find('.acf-field-checkbox[data-name="infolive_list_select_zones"] .acf-checkbox-list');
    var acfkeying_name = zonesList.closest('.acf-field-checkbox').find('.acf-input').children('input').attr('name');
    var acfkeying_id = acfkeying_name.replace(/[[]/g, '-').replace(/(])/g, '')
    acfkeying_name += '[]';
    zonesList.empty();

    if (!newStation) {
        return;
    }
    var data = {
        action: 'load_zones_field_choices',
        station: newStation
    }
    data = acf.prepareForAjax(data);
    var request = $.ajax({
        url: acf.get('ajaxurl'),
        data: data,
        type: 'post',
        dataType: 'json',
        success: function(json) {
            if (!json) {
                return;
            }            
            for (var i=0;i<json.length;i++) {
                var id = acfkeying_id + '-' + json[i]['value'];                
                var zone_item = `<li><label><input id="${id}" name="${acfkeying_name}" type="checkbox" value="${json[i]['value']}"></input>${json[i]['label']}</label></li>`;
                zonesList.append(zone_item);
            }
            
        },
        error: function(xhr, status, error) {
            console.log('Error..', xhr, status, error);
        }
    })    
}