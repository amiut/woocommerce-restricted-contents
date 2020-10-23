(function($) {
    $('.restricted-create-ajax-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serializeArray(),
            success: function(res) {
                if (res.success) {
                    window.location.reload();

                } else {
                    alert(res.data.message);

                }
            }
        })
    });
})(jQuery)
