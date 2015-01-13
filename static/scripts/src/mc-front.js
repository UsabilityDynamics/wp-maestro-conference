!function($, s) {
    $('#mc_conferences_filter').change(function(){
        var e = $(this), data = {
            action: "mc_conferences_filter",
            mc_filter_field: $("option:selected", e).val(),
            user_id: e.attr('data-user_id'),
            offset: e.attr('data-offset'),
            per_page: e.attr('data-per_page')
        };
        $.ajax({
            type: "POST",
            url: s.admin_ajax,
            data: data,
            dataType: "json",
            cache: !1,
            complete: function(r) {
                $('.mc_conferences').html(r.responseText);
            }
        });
    });
}(jQuery, _mc_front);