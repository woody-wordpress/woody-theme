tinymce.create('tinymce.plugins.woody_anchor', {
    init: function (editor, url) {
        editor.addButton('woody_anchor', {
            image: url + '/anchor.svg',
            title: 'Ancre',
            onclick: function () {
                tinymce.ScriptLoader.loadQueue();
                editor.windowManager.open({
                    title: 'Ajouter une ancre',
                    width: 600,
                    height: 70,
                    body: [{
                        type: 'textbox',
                        name: 'anchorId',
                        label: 'Nom de l\'ancre, en minuscule et sans espaces',
                    }],
                    onsubmit: function (e) {
                        var anchorId = e.data.anchorId;
                        editor.insertContent('[woody_anchor id="' + anchorId + '"]');
                    }
                });
            },
        });
    }
});

tinymce.PluginManager.add('woody_anchor', tinymce.plugins.woody_anchor);
