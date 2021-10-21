function setEditMode(field) {
    window.editMode = field.$input()[0].attributes.value.value;
    var tabs = acf.getFields({
        type: 'tab'
    });

    tabs.forEach(tab => {
        filterTabs(tab);
    });

    field.on('change', function() {
        setEditMode(field);
    });
}

function filterTabs(field) {
    let layoutTabsTexts = [
        'Mise en page',
        'Disposition des blocs',
        'Plus d\'options'
    ];

    if (window.editMode == 'lite' && layoutTabsTexts.includes(field.tab.$el[0].childNodes[0].innerHTML)) {
        field.hide();
        if (field.tabs.tabs.length == 2) {
            field.tabs.tabs.forEach(tab => {
                if (tab.field.cid != field.cid) {
                    tab.$el[0].classList.add('alone-tab');
                }
            });
        }
    } else {
        field.show();
    }

    if (window.editMode == 'advanced') {
        for (let node of document.getElementsByClassName('alone-tab')) {
            node.classList.remove('alone-tab');
        }
    }
}

if (typeof acf == 'object') {
    // acf.addAction('load_field/type=tab', filterTabs);
    acf.addAction('append_field/type=tab', filterTabs);
    acf.addAction('load_field/name=edit_mode', setEditMode);
}
