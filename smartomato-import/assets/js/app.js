"use strict";

jQuery(function(){
    let url = new URL(location);

    if(url.searchParams.has('push-exchange')){
        setTimeout(() => {
            jQuery('#STI_push_exchange').trigger('click');
        }, 700);

        url.searchParams.delete('push-exchange');
        window.history.replaceState(null, null, url);
    }

    jQuery('#STI_push_exchange').click(() => {

        const nonce = jQuery('#STI_push_exchange').data('code');
        const button = document.getElementById('STI_push_exchange');

        if(!nonce) return;

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                'action': 'stipush',
                '_wpnonce': nonce,
            },
            beforeSend: function(){
                button.disabled = true;
                button.innerText = 'Импорт запущен';
            },
            success: function (data) {
                console.log(data);
            },
            error: function(data) {
                console.log(data.data.error);
            },
            complete: function(){
                button.disabled = false;
                button.innerText = 'Импорт завершен';
            }
        });
    });

    jQuery('#STI_options_form').submit((e) => {
        e.preventDefault();

        const nonce = jQuery('#STI_update_options').data('code');
        const button = document.getElementById('STI_update_options');
        let login = jQuery('#sti_auth_login').val();
        let password = jQuery('#sti_auth_password').val();
        let exchange_time = jQuery('#sti_exchange_time').val();
        let exchange_rate = jQuery('#sti_exchange_rate').val();
        let organization_id = jQuery('#sti_organization_id').val();
        let widget_host = jQuery('#sti_widget_host').val();
        let iiko_api_key = jQuery('#sti_iiko_api_key').val();

        if(!nonce) return;

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                'action': 'stisaveoptions',
                '_wpnonce': nonce,
                'login': login,
                'password': password,
                'exchange_time': exchange_time,
                'exchange_rate': exchange_rate,
                'organization_id': organization_id,
                'widget_host': widget_host,
                'iiko_api_key': iiko_api_key,
            },
            beforeSend: function(){
                button.disabled = true;
                button.innerText = 'Сохраняем...';
            },
            success: function () {
                button.innerText = 'Сохранено';
            },
            error: function() {
                button.innerText = 'Ошибка';
            },
            complete: function(){
                button.disabled = false;

                if(jQuery('#sti_auth_password').val().length > 1){
                    jQuery('#sti_auth_password').val('').attr('placeholder', 'Пароль установлен');
                }
            }
        });
    });

});