if (typeof(acf) !== 'undefined') {
    acf.add_filter('select2_escape_markup', function( escaped_value, original_value, $select, settings, field, instance ){
        // Remove HTML escaping to display html in select fields
        return original_value;
    });
}