!(function ($, flatpickr) {
    $('#post').each(function () {

        // Add Flatpickr to Unpublish metabox
        $('#woody-unpublisher').each(function () {

            var unPublisher = flatpickr('#wUnpublisher_date', {
                enableTime: true,
                dateFormat: 'Y-m-dTH:i',
                altInput: true,
                altFormat: 'j F Y à H:i',
                locale: flatpickr.l10ns.fr,
                time_24hr: true,
                minDate: 'today'
            });

            $('.unpublisher-reset-date').click(function () {
                $(this).siblings('.flatpickr-input').val('');
            });
        });

    });
})(jQuery, flatpickr);
