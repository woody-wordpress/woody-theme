if (typeof(acf) !== 'undefined') {
    acf.addFilter('wysiwyg_tinymce_settings', function(mceInit, id, el) {
        mceInit.paste_as_text = true;
        if (el.data.key == 'field_5b041dbfadb74' && el.$el.closest('[data-name="content_selection"]').length) {
            mceInit.toolbar1 = 'bold,italic,underline,undo,redo,strikethrough';
        }
        return mceInit;
    })
}

