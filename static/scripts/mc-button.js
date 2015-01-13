!function($, s) {
    $(".mc-button").click(function() {
        var e = $(this), data = {
            action: "mc_button_action",
            wpnonce: e.data("wpnonce"),
            type: e.data("action"),
            conference_id: e.data("conference_id"),
            send_mail: e.data("send-mail"),
            extra: e.data("extra"),
            callback: e.data("callback"),
            redirect_to: e.data("redirect-to"),
            success_label: e.data("success-label"),
            success_hide: e.data("success-hide")
        };
        $.ajax({
            type: "POST",
            url: s.admin_ajax,
            data: data,
            dataType: "json",
            cache: !1,
            complete: function(r, status) {
                "success" === status && r.responseJSON.success ? (e.addClass("success disabled"), 
                "true" == data.success_hide ? e.hide() : "" !== data.success_label && e.html(data.success_label), 
                $(document).trigger("mc_btn_success", [ data, e, r ]), "string" == typeof r.responseJSON.redirect_to && r.responseJSON.redirect_to.length > 0 && window.location.replace(r.responseJSON.redirect_to)) : (e.addClass("fail"), 
                $(document).trigger("mc_btn_fail", [ data, e, r ]));
            }
        });
    });
}(jQuery, _mc_button);