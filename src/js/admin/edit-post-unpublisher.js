import flatpickr from "flatpickr";
import { French } from "flatpickr/dist/l10n/fr.js"

!(function ($) {
    $('#post').each(function () {

        // Add Flatpickr to Unpublish metabox
        $('#woody-unpublisher').each(function () {

            var unPublisher = flatpickr('#wUnpublisher_date', {
                enableTime: true,
                dateFormat: 'Y-m-dTH:i',
                altInput: true,
                altFormat: 'j F Y à H:i',
                locale: French,
                time_24hr: true,
                minDate: 'today'
            });

            $('.unpublisher-reset-date').click(function () {
                $(this).siblings('.flatpickr-input').val('');
            });
        });

    });
})(jQuery);
