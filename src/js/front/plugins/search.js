import $ from 'jquery';

$('.woody-component-esSearch').each(function() {
    var $this = $(this),
        $input = $this.find('.woody-esForm input[type="text"]'),
        $listWrapper = $this.find('.list-wrapper'),
        $loader = $this.find('.ajaxloader'),
        $label = $this.find('.input-group-label'),
        currentUrl = window.location.protocol + '//' + window.location.host + window.location.pathname,
        xhr = null;

    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this,
                args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };

    var ajax_search = function() {
        xhr = $.ajax({
            type: 'GET',
            url: currentUrl,
            data: 'query=' + $input.val(),
            beforeSend: function() {
                if (xhr != null) {
                    xhr.abort();
                }
            },
            success: function(html) {
                var results = $(html).find('.woody-component-esSearch .list-wrapper').html();
                $listWrapper.html(results);
                window.history.pushState({ query: $input.val() }, null, currentUrl + '?query=' + $input.val());
                $label.removeClass('hide');
                $loader.addClass('hide');
                $('html, body').animate({ scrollTop: 0 }, 100, 'linear');
            },
            error: function() {
                console.error('search failed');
            },
        });
    }

    window.addEventListener('popstate', function(e) {
        var state = e.state;
        if (state == null) {
            $input.val('');
        } else {
            $input.val(state.query);
            ajax_search();
        }
    });

    $input.keyup(function() {
        $label.addClass('hide');
        $loader.removeClass('hide');
    });

    $input.keyup(debounce(function() {
        ajax_search();
    }, 1000));
});
