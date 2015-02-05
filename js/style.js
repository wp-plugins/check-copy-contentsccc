(function($) {
    $(".theContentWrap-ccc").bind('copy', function() {

        //選択中のテキスト取得
        var copyText = $.selection();
        //nonce取得
        var nonce = $("input#ccc_nonce").val();

        $.ajax({
            type: 'POST',
            url: CCC.endpoint,
            data: {
                'action': CCC.action,
                'copyText': copyText,
                'postID': CCC.postID,
                'url': window.location.href,
                'remote_addr': CCC.remote_addr,
                'referrer': document.referrer,
                'nonce': nonce
            },
            success: function(data) {
                var json_str = JSON.stringify(data);
                $('#json-data').append(json_str);
            },
            error: function() {}
        });
    });
})(jQuery);