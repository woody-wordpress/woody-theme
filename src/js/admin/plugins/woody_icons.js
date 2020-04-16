!(function($, window, document, undefined) {
    var icons_list = '';
    var icons_loaded = false;

    var editorWindowManager = function(editor) {
        tinymce.ScriptLoader.loadQueue();
        editor.windowManager.open({
            title: 'Ajouter un pictogramme',
            width: 600,
            height: 600,
            body: [{
                type: 'container',
                name: 'woody-icons-container',
                html: '<ul class="woody-icons-list" style="display:flex; flex-wrap:wrap; margin:0; height: 580px; overflow: scroll;">' + icons_list + '</ul>'
            }],
            onsubmit: function(e) {
                var selected_icon_class = $('.woody-icons-list').find('.selected').find('span').attr('class');
                if (typeof selected_icon_class != 'undefined') {
                    editor.insertContent('<span class="' + selected_icon_class + '"><span style="display:none">Icone</span></span>');
                }
            }
        });

        $('.woody-icon-list-el').click(function() {
            $('.woody-icon-list-el.selected').removeClass('selected').css({ 'color': 'black', 'background-color': 'white' });
            $(this).addClass('selected').css({ 'color': 'white', 'background-color': '#0085ba' });
        });
    }

    tinymce.create('tinymce.plugins.woody_icons', {
        init: function(editor, url) {
            editor.addButton('woody_icons', {
                image: url + '/star.svg',
                title: 'Pictogrammes',
                onclick: function() {
                    if (icons_loaded == false) {
                        // Appel ajax pour charger la liste des icones
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: ajaxurl,
                            data: {
                                action: 'woody_icons_list',
                            },
                            success: function(response) {
                                const list = {};
                                Object.keys(response).sort().forEach(function(key) {
                                    list[key] = response[key];
                                });

                                for (let [key, value] of Object.entries(list)) {
                                    icons_list += '<li class="woody-icon-list-el" style="width: 25%; padding: 20px; cursor:pointer; box-sizing:border-box; display:flex; flex-direction:column; align-items:center; justify-content:center"><span class="wicon ' + key + '" style="font-size:40px;"></span><span>' + value + '</span></li>';
                                }
                                icons_loaded = true;

                                editorWindowManager(editor);
                            },
                            error: function() {},
                        });

                    } else {
                        editorWindowManager(editor);
                    }

                },
            });
        }
    });
    tinymce.PluginManager.add('woody_icons', tinymce.plugins.woody_icons);

})(jQuery, window, document);
