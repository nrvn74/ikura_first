<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $current_page = admin_url( 'tools.php?page=' . plugin_basename( 'wp-sweep/admin.php' ) );
// $message      = '';

global $wpdb;

$exchange_rate = get_option( 'sti_exchange_rate' );
$exchange_rate_values = array(
    'every-5-mins'  => 'Каждые 5 минут',
    'every-10-mins' => 'Каждые 10 минут',
    'every-15-mins' => 'Каждые 15 минут',
    'every-20-mins' => 'Каждые 20 минут',
    'every-30-mins' => 'Каждые 30 минут',
    'every-45-mins' => 'Каждые 45 минут',
    'hourly'        => 'Каждый час',
    'daily'         => 'Каждый день',
    'every-2-days'  => 'Раз в 2 дня',
    'every-3-days'  => 'Раз в 3 дня'
);
$dishes_table = $wpdb->get_blog_prefix() . 'smartomato_dishes';
$categories_table = $wpdb->get_blog_prefix() . 'smartomato_categories';

$products_total = $wpdb->query("SELECT * FROM $dishes_table");
$categories_total = $wpdb->query("SELECT * FROM $categories_table");
$past_import = get_option('sti_past_exchange_date') ? get_option('sti_past_exchange_date') : 'Ещё не запускался';

?>

<h2>Настройка импорта Смартомато</h2>

<form method="post" id="STI_options_form" action>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="sti_auth_login">Логин авторизации</label>
                </th>
                <td>
                    <input type="text" name="sti_auth_login" id="sti_auth_login" value="<?= esc_attr( get_option('sti_auth_login') ) ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sti_auth_password">Пароль</label>
                </th>
                <td>
                    <input type="text" name="sti_auth_password" id="sti_auth_password" value="" placeholder="<?= get_option('sti_auth_password') ? 'Пароль установлен' : '' ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sti_exchange_time">Время для обмена</label>
                    <p style="font-size: 12px; font-weight: 400; opacity: 0.6;">Учитывается только для обменов, производящихся не чаще раза в день. В остальных случаях устанавливается текущее время!</p>
                </th>
                <td>
                    <input type="time" name="sti_exchange_time" id="sti_exchange_time" value="<?= esc_attr( get_option('sti_exchange_time') ) ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sti_exchange_rate">Частота обмена</label>
                </th>
                <td>
                    <select name="sti_exchange_rate" id="sti_exchange_rate">
                        <?php foreach($exchange_rate_values as $key => $value): ?>
                            <option value="<?= $key ?>"<?= $exchange_rate == $key ? ' selected' : null ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sti_organization_id">ID организации</label>
                </th>
                <td>
                    <input type="number" name="sti_organization_id" id="sti_organization_id" value="" placeholder="<?= get_option('sti_organization_id') ? get_option('sti_organization_id') : '' ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sti_widget_host">Хост виджета Смартомато</label>
                </th>
                <td>
                    <input type="text" name="sti_widget_host" id="sti_widget_host" value="" placeholder="<?= get_option('sti_widget_host') ? get_option('sti_widget_host') : 'Например, 26.smartomato.ru' ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>Опции (пока не подключены)</label>
                </th>
                <td>
                    <div>
                        <input type="checkbox" id="sti_option_1" name="sti_option_1">    
                        <label for="sti_option_1">Не импортировать товары без фотографий</label>
                    </div>
                    <div>
                        <input type="checkbox" id="sti_option_2" name="sti_option_2" checked>    
                        <label for="sti_option_2">Не импортировать товары без цены</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>Информация</label>
                </th>
                <td>
                    <p>Товаров импортировано: <strong><?= $products_total ?></strong></p>
                    <p>Категорий импортировано: <strong><?= $categories_total ?></strong></p>
                    <p>Дата последнего импорта: <strong><?= $past_import ?></strong></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>Ручной запуск обмена</label>
                </th>
                <td>
                    <button class="button" id="STI_push_exchange" name="STI_push_exchange" data-code="<?= wp_create_nonce( 'push_exchange_button' ); ?>">Начать обмен</button>
                </td>
            </tr>
        </tbody>
    </table>
    <button type="submit" class="button button-primary" id="STI_update_options" style="margin-top: 3em;" data-code="<?= wp_create_nonce( 'update_options_button' ); ?>">Сохранить</button>
</form>