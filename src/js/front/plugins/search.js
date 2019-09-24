import $ from 'jquery';

$('.woody-component-esSearch').each(function() {
    var $this = $(this),
        $input = $this.find('.woody-esForm input[type="text"]'),
        $tags = $this.find('.search-tags-container .search-tag'),
        $listWrapper = $this.find('.list-wrapper'),
        $loader = $this.find('.ajaxloader'),
        $label = $this.find('.input-group-label'),
        currentUrl = window.location.protocol + '//' + window.location.host + window.location.pathname,
        xhr = null;

    // Set the query with input field
    var query = 'query=' + $input.val();

    function getCheckedTags() {

        // Get every .search-tag:checked to add to the query
        var $activeTags = $this.find(('.search-tags-container .search-tag:checked'));
        if ($activeTags.length) {
            var tagString = '',
                $i = 1;

            $activeTags.each(function() {
                var $activeTag = $(this);

                if ($i > 1) {
                    tagString += ':' + $activeTag.val();
                } else {
                    tagString += $activeTag.val();
                }
                $i++
            });

            query = 'query=' + $input.val() + '&tags=' + tagString;
        }
    }

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
            data: query,
            beforeSend: function() {
                if (xhr != null) {
                    xhr.abort();
                }
            },
            success: function(html) {
                var results = $(html).find('.woody-component-esSearch .list-wrapper').html();
                $listWrapper.html(results);
                window.history.pushState({ query: $input.val() }, null, currentUrl + '?' + query);
                $label.removeClass('hide');
                $loader.addClass('hide');
                $('html, body').animate({ scrollTop: 0 }, 100, 'linear');
            },
            error: function() {
                console.error('search aborted');
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

    $tags.click(function() {
        $label.addClass('hide');
        $loader.removeClass('hide');
    });

    $input.keyup(debounce(function() {
        getCheckedTags();
        ajax_search();
    }, 300));

    $tags.click(debounce(function() {
        getCheckedTags();
        ajax_search();
    }, 300));
});
