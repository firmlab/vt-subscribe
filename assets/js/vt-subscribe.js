jQuery(document).ready(function($) {

    function setCookie(cname, cvalue, min) {
        // var d = new Date();
        // d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expired = new Date();
        expired.setMinutes( expired.getMinutes() + min );

        var expires = "expires="+expired;
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        document.cookie = cname + '-exp' + "=" + expired + ";" + expires + ";path=/";
    }
    
    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    
    var closedPopup = getCookie("popup-vt-subscribe-hide");
    var closedPopupExpire = getCookie("popup-vt-subscribe-hide-exp");
    console.log(vtJsData.logged_in);

    if((closedPopup == '' || new Date(closedPopupExpire) < new Date()) && vtJsData.logged_in == 0) {
        setTimeout(function() {
            jQuery('#popup-vt-subscribe').addClass('popshow');
        }, 5000);
    }

    jQuery('.popup-vt-subscribe-close').on('click', function() {
        jQuery('#popup-vt-subscribe').removeClass('popshow');
        setCookie('popup-vt-subscribe-hide', 'hide', 5);
    });

    jQuery('#popup-vt-subscribe-email').on('keyup', function(e) {
        if(jQuery(e.target).val().trim() != '') {
            jQuery('#vt_subscribe_popup_btn').removeAttr('disabled');
        } else {
            jQuery('#vt_subscribe_popup_btn').attr('disabled', true);
        }
    });

    jQuery('#vt_subscribe_popup_form').on('submit', function(e) {
        e.preventDefault();
        var emailInput = jQuery('#popup-vt-subscribe-email').val().trim();
        if(emailInput == '') {
            return;
        }
        //TODO: create ajax request front and back
        jQuery.ajax({
            url: vtJsData.ajax_url,
            type: "POST",
            data: {
                action: "vt_subscribe",
                email: emailInput,
                security: vtJsData.ajax_nonce
            },
            success: function(e) {
                console.log(e);
                var result = JSON.parse(e);
                if(result.status == 'ok') {
                    jQuery('#popup-vt-subscribe-notification').append('<p style="text-align: center;"><strong>Thank you, for subscribe!</strong></p>');
                    setTimeout(function() {
                        jQuery('#popup-vt-subscribe').removeClass('popshow');
                        setCookie('popup-vt-subscribe-hide', 'hide', 5000);
                    }, 1500);
                }
            }
        });
    });
});