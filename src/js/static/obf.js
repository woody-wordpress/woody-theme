(function($) {

    $('.obf').each(function() {
        var $this = $(this);
        var href = atob($this.data('obf'));
        var attrs = '';
        // On concatène chacun des attributs de l'élément pour les réapliquer au lien
        $.each(this.attributes, function() {
            if (this.specified) {
                if (this.name != 'data-obf' && this.name != 'data-target') {
                    attrs += this.name + '="' + this.value + '" ';
                }
                if (this.name == 'data-target') {
                    attrs += 'target="' + this.value + '" ';
                }
            }
        });

        $this.replaceWith('<a href="' + href + '" ' + attrs + '>' + $this[0].innerHTML + '</a>');
    });

})(jQuery);
