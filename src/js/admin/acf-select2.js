if (typeof(acf) !== 'undefined') {
    acf.add_filter('select2_escape_markup', function( escaped_value, original_value, $select, settings, field, instance ){

        // do something to the original_value to override the default escaping, then return it.
        // this value should still have some kind of escaping for security, but you may wish to allow specific HTML.
        if (field.data( 'name' ) == "select2_with_html") {
            return my_custom_escaping_method( original_value );
        }

        // return
        return escaped_value;

    });
}