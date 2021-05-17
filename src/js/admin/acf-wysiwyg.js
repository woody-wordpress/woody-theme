if (typeof(acf) !== 'undefined') {
    acf.addFilter('wysiwyg_tinymce_settings', function(mceInit, id) {
        mceInit.paste_as_text = true;
        return mceInit;
    })
}

