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
	$('#content').on('click', '.page-numbers', function(q){
        q.preventDefault();
        $('#mc_conferences').fadeOut(3000, function(){ });//fade out the content area
        var mc_select = $("#mc_conferences_filter");
        var data = {
            action: "mc_conferences_filter",
            mc_filter_field: $("option:selected", mc_select).val(),
            user_id: mc_select.attr('data-user_id'),
            offset: mc_select.attr('data-offset'),
            per_page: mc_select.attr('data-per_page'),
            paged: $(this).attr('href')
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
        $('#mc_conferences').fadeIn(3000, function(){ });//fade in the content area
    });
}(jQuery, _mc_front);