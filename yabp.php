<?php
/*
    Plugin Name: Yet Another bol.com Plugin
    Plugin URI: https://tromit.nl/diensten/wordpress-plugins/
    Description: A powerful plugin to easily integrate bol.com products in your blog posts or at your pages to earn money with the bol.com Partner Program.
    Version: 1.4
    Author: Mitchel Troost
    Author URI: https://tromit.nl/
    License: GPL2
    Text Domain: yabp
*/

/*  
    Copyright 2014-2021  Mitchel Troost  (email: mitchel.troost@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
    Since the GPL2 license is used, you are allowed to modify anything below at your own risk.
    However, this is not recommended. The developer may not give support on modified versions of this plugin.
    Instead, please contact the developer and submit a bug report or feature request. 
*/

global $wpdb;

$yabp_version = "1.4";
$table_name_yabp = $wpdb->prefix.'yabp';
$table_name_yabp_items = $wpdb->prefix.'yabp_items';
$table_name_yabp_deals = $wpdb->prefix.'yabp_deals';
$table_name_yabp_deals_items = $wpdb->prefix.'yabp_deals_items';
$yabp_partnerlink_prefix = "https://partner.bol.com/click/click?p=1&amp;t=url&amp;s=";
$yabp_impression_imglink_prefix = "https://partner.bol.com/click/impression?p=1&amp;s=";
$yabp_open_api_link = "https://developers.bol.com/documentatie/open-api/aanmeldformulier-api-toegang/";
$yabp_partnerprogram_link = "https://partner.bol.com/partner/index.do";
$yabp_bolcom_buy_button = "https://www.bol.com/nl/upload/partnerprogramma/promobtn/btn_promo_koop_dark_large.gif";
$yabp_bolcom_buy_button_alt = "https://www.bol.com/nl/upload/partnerprogramma/promobtn/btn_promo_koop_light_large.gif";
$yabp_bolcom_view_button = "https://www.bol.com/nl/upload/partnerprogramma/promobtn/btn_promo_bekijk_dark_large.gif";
$yabp_bolcom_view_button_alt = "https://www.bol.com/nl/upload/partnerprogramma/promobtn/btn_promo_bekijk_light_large.gif";
$yabp_bolcom_putincart_link = "http://www.bol.com/nl/inwinkelwagentje.html?productId=";
$yabp_add_item_item_defaultcountry = 1;
$yabp_add_item_item_count = 10;
$yabp_add_item_item_count_limit = 50;
$yabp_itemlist_count = 10;
$yabp_itemlist_count_limit = 100;
$yabp_productmanager_count = 10;
$yabp_productmanager_expired_products_notification_default = 1;
$yabp_styling_item_fontsize_lowlimit = 5;
$yabp_styling_item_fontsize_highlimit = 30;
$yabp_item_freeshipping_limit = 20;
$yabp_item_freeshipping_text = "Free shipping!";
$yabp_item_textlink_text = "Buy now";
$yabp_item_shortcode_format = "[yabp %entry_id%]";
$yabp_item_time_format = "Y-m-d H:i:s";
$yabp_cron_defaulttime = "08:00";
$api_server = 'api.bol.com';
$api_port = '443';
$yabp_item_shortcode_imgwidth_limit = 150;
$yabp_item_shortcode_imgcolumnwidth_limit = 100;
$yabp_item_shortcode_infocolumnwidth_limit = 100;
$yabp_shortcode_default_imgwidth = 100;
$yabp_shortcode_default_imgcolumnwidth = 30;
$yabp_shortcode_default_infocolumnwidth = 70;


function yabp_I18n() { load_plugin_textdomain('yabp', false, dirname(plugin_basename( __FILE__ )) . '/lang/'); }

function yabp_menu() {        
    $exp_items_count = yabp_expired_items_count();
    $exp_items_title = esc_attr(sprintf('%d expired product(s)', $exp_items_count));
    $exp_items_menu_label_options = sprintf(__('YAbP %s', 'yabp'), "<span class='update-plugins count-".$exp_items_count."' title='".$exp_items_title."'><span class='update-count'>".number_format_i18n($exp_items_count)."</span></span>" );        
    $exp_items_menu_label_productmanager = sprintf(__('Product manager %s', 'yabp'), "<span class='update-plugins count-".$exp_items_count."' title='".$exp_items_title."'><span class='update-count'>".number_format_i18n($exp_items_count)."</span></span>" );        
    
    if(function_exists('add_menu_page')){
        add_menu_page(__('Options', 'yabp'), $exp_items_menu_label_options, 'manage_options', 'yabp', 'yabp_options');
    }
    if(function_exists('add_submenu_page')){
        $yabp_optionspage = add_submenu_page('yabp', __('Options', 'yabp'), __('Options', 'yabp'), 'manage_options', 'yabp', 'yabp_options');
        add_action('load-'.$yabp_optionspage, 'yabp_register_adminscripts_styles_action');
    }
    if(function_exists('add_submenu_page')){
        add_submenu_page('yabp', __('Add product', 'yabp'), __('Add product', 'yabp'), 'manage_options', 'yabp-add-item', 'yabp_add_item');
    }
    if(function_exists('add_submenu_page')){
        add_submenu_page('yabp', __('Product list', 'yabp'), __('Product list', 'yabp'), 'manage_options', 'yabp-itemlist', 'yabp_itemlist');
    }
    if(function_exists('add_submenu_page')){        
        add_submenu_page('yabp', __('Product manager', 'yabp'), $exp_items_menu_label_productmanager, 'manage_options', 'yabp-productmanager', 'yabp_productmanager');
    }
    if(function_exists('add_submenu_page')){        
        add_submenu_page('yabp', __('Shortcode generator', 'yabp'), __('Shortcode generator', 'yabp'), 'manage_options', 'yabp-shortcodegenerator', 'yabp_shortcodegenerator');
    }
}

add_action('admin_menu', 'yabp_menu');

add_action('init', 'yabp_init');
        
function yabp_init(){
    global $yabp_version;
    yabp_I18n();    
    if (get_option('yabp_version') != $yabp_version) { yabp_install(); }
    if ((isset($_GET['activate']) || isset($_GET['activate-multi']))) {
        
        //set crons when activated
        yabp_cron_handle_eventstatus(1);
        yabp_cron_handle_eventstatus(2);
        yabp_cron_handle_eventstatus(3);
    }
    if (is_admin()) { yabp_forms(); }
}

function yabp_install(){
    global $wpdb, $table_name_yabp, 
    $table_name_yabp_items, $yabp_version, $yabp_add_item_item_count, $yabp_itemlist_count, $yabp_item_textlink_text, $yabp_item_freeshipping_text, $yabp_add_item_item_defaultcountry, $table_name_yabp_deals, $table_name_yabp_deals_items, $yabp_productmanager_count, $yabp_productmanager_expired_products_notification_default;
        
    $sql = "CREATE TABLE IF NOT EXISTS ".$table_name_yabp."(
        entry_id INT auto_increment NOT NULL,
        entry_bolid BIGINT NOT NULL,
        entry_thumb INT(1) NOT NULL,
        entry_showthumb INT(1) NOT NULL,
        entry_showprice INT(1) NOT NULL,
        entry_showlistprice INT(1) NOT NULL,
        entry_showtitle INT(1) NOT NULL,
        entry_showsubtitle INT(1) NOT NULL,
        entry_showavailability INT(1) NOT NULL,
        entry_showfreeshipping INT(1) NOT NULL,
        entry_showrating INT(1) NOT NULL,
        entry_showbutton INT(1) NOT NULL,
        entry_updateinterval INT(1) NOT NULL,
        entry_buttontype INT(1) NOT NULL DEFAULT '1',
        entry_putincart INT(1) NOT NULL,
        entry_recordimpressions INT(1) NOT NULL,
        entry_openinnewtab INT(1) NOT NULL,
        entry_style INT(1) NOT NULL,
        entry_imgontop INT(1) NOT NULL,
        entry_country INT(1) NOT NULL DEFAULT '1',
        entry_expired_notification_sent INT(1) NOT NULL DEFAULT '0',
        PRIMARY KEY(entry_id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8";        
        
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);    

    $sql = "CREATE TABLE IF NOT EXISTS ".$table_name_yabp_items."(
        item_id INT auto_increment NOT NULL,
        entry_id INT NOT NULL,
        item_title VARCHAR(500) NOT NULL,
        item_subtitle VARCHAR(500),
        item_externalurl TEXT NOT NULL,
        item_afflink TEXT NOT NULL,
        item_xlthumb TEXT,
        item_lthumb TEXT,
        item_mthumb TEXT,
        item_sthumb TEXT,
        item_xsthumb TEXT,
        item_price VARCHAR(10) NOT NULL,
        item_listprice VARCHAR(10) NOT NULL,
        item_availability TEXT NOT NULL,
        item_availabilitycode INT NOT NULL,
        item_rating INT NOT NULL,
        item_ratingspan TEXT NOT NULL,
        time INT NOT NULL,
        PRIMARY KEY(item_id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
        
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);    
    
    //remove obsolete tables
    $sql = "DROP TABLE IF EXISTS ".$table_name_yabp_deals;    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);    
    
    $sql = "DROP TABLE IF EXISTS ".$table_name_yabp_deals_items;    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);    
           
    update_option('yabp_version', $yabp_version);
    
    //old update database tweaks
    $check_db = $wpdb->get_results("SHOW COLUMNS FROM `".$table_name_yabp."` LIKE 'entry_expired_notification_sent'");
    if (!$check_db) {
        $wpdb->query("ALTER TABLE `".$table_name_yabp."` ADD `entry_expired_notification_sent` INT(1) NOT NULL DEFAULT '0'");
    }                
    $check_db = $wpdb->get_results("SHOW COLUMNS FROM `".$table_name_yabp."` LIKE 'entry_country'");
    if (!$check_db) {
        $wpdb->query("ALTER TABLE `".$table_name_yabp."` ADD `entry_country` INT(1) NOT NULL DEFAULT '1'");
        $wpdb->query("ALTER TABLE `".$table_name_yabp."` ADD `entry_showfreeshipping` INT(1) NOT NULL");
    }                
    $check_db = $wpdb->get_var("SELECT item_title FROM `".$table_name_yabp_items."` LIMIT 1");
    $db_info = $wpdb->get_col_info('max_length', 0);    
    if ($db_info < 101) { 
        $wpdb->query("ALTER TABLE `".$table_name_yabp_items."` MODIFY item_title VARCHAR(500)");
        $wpdb->query("ALTER TABLE `".$table_name_yabp_items."` MODIFY item_subtitle VARCHAR(500)");
    }    
    $check_db = $wpdb->get_results("SHOW COLUMNS FROM `".$table_name_yabp."` LIKE 'entry_buttontype'");
    if (!$check_db) {
        $wpdb->query("ALTER TABLE `".$table_name_yabp."` ADD `entry_buttontype` INT(1) NOT NULL DEFAULT '1', ADD `entry_putincart` INT(1) NOT NULL, ADD `entry_recordimpressions` INT(1) NOT NULL, ADD `entry_openinnewtab` INT(1) NOT NULL, ADD `entry_style` INT(1) NOT NULL");
        delete_option('yabp_styling_item_button_usealternative');
        delete_option('yabp_styling_item_button_useviewbutton');
        delete_option('yabp_item_getimpressions');
    }    
    $check_db = $wpdb->get_results("SHOW COLUMNS FROM `".$table_name_yabp."` LIKE 'entry_imgontop'");
    if (!$check_db) {
        $wpdb->query("ALTER TABLE `".$table_name_yabp."` ADD `entry_imgontop` INT(1) NOT NULL");
    }                                        
    
    if (!get_option('yabp_add_item_item_count')) { update_option('yabp_add_item_item_count', $yabp_add_item_item_count); }
    if (!get_option('yabp_itemlist_count')) { update_option('yabp_itemlist_count', $yabp_itemlist_count); }
    if (!get_option('yabp_productmanager_count')) { update_option('yabp_productmanager_count', $yabp_productmanager_count); }
    if (!get_option('yabp_dealslist_count')) { update_option('yabp_dealslist_count', $yabp_dealslist_count); }
    if (!get_option('yabp_item_textlink_text')) { update_option('yabp_item_textlink_text', __($yabp_item_textlink_text, 'yabp')); }
    if (!get_option('yabp_item_freeshipping_text')) { update_option('yabp_item_freeshipping_text', __($yabp_item_freeshipping_text, 'yabp')); }
    if (!get_option('yabp_add_item_item_defaultcountry')) { update_option('yabp_add_item_item_defaultcountry', $yabp_add_item_item_defaultcountry); }
    if (!get_option('yabp_productmanager_expired_products_notification')) { update_option('yabp_productmanager_expired_products_notification', $yabp_productmanager_expired_products_notification_default); }
       
    //cleanup items database after bug, my apologies   
    $wpdb->query("DELETE FROM `".$table_name_yabp_items."` WHERE item_id NOT IN (SELECT * FROM (SELECT MIN(n.item_id) FROM `".$table_name_yabp_items."` n GROUP BY n.entry_id) x)");
    
    //1.3.7 cleanup
    if (get_option('yabp_deals_updatetype')) { delete_option('yabp_deals_updatetype'); }
    if (get_option('yabp_deals_countdown_text')) { delete_option('yabp_deals_countdown_text'); }
    if (get_option('yabp_deals_countdown_text_expired')) { delete_option('yabp_deals_countdown_text_expired'); }
    if (get_option('yabp_deal_expired_option')) { delete_option('yabp_deal_expired_option'); }
        
}

function yabp_api_dorequest($httpMethod, $url, $parameters, $content, $sessionId) {

    global $api_server, $api_port;
    
    $server = $api_server;
    $port = $api_port;
    $today = gmdate('D, d F Y H:i:s \G\M\T');

    if ($httpMethod == 'GET') { $contentType = 'application/xml';} 
    elseif ($httpMethod == 'POST') { $contentType = 'application/x-www-form-urlencoded'; }

    $headers = $httpMethod . " " . $url . $parameters . " HTTP/1.0\r\n";
    $headers .= "Content-type: " . $contentType . "\r\n";
    $headers .= "Host: " . $server . "\r\n";
    $headers .= "Content-length: " . strlen($content) . "\r\n";
    $headers .= "Connection: close\r\n";
    if (!is_null($sessionId)) {
        $headers .= "X-OpenAPI-Session-ID: " . $sessionId . "\r\n";
    }
        $headers .= "\r\n";

        $socket = fsockopen('ssl://' . $server, $port, $errno, $errstr, 30);
        if (!$socket) { echo "$errstr ($errno)<br />\n"; }
        fputs($socket, $headers);
        fputs($socket, $content);
        $ret = "";

        while (!feof($socket)) {
            $readLine = fgets($socket);
            $ret .= $readLine;
        }
        fclose($socket);

    return $ret;
}

function yabp_pagelinks($link,$showpages='4',$totalpage,$curpage){
    $nav = '';
    
    $prev_page = $curpage != 1 ? ($curpage - 1) : '';
    $next_page = $curpage != $totalpage ? ($curpage + 1) : '';

    if ($totalpage > 1 && $curpage != 1) { $nav .= ' <span style="padding: 5px;"><a href="'.$link.'1" title="'.__('Go to the first page', 'yabp').'">&laquo;</a></span>'; }
    if ($curpage > 1) { $nav .= '<span style="padding: 5px;"><a href="'.$link.''.$prev_page.'" title="'.__('Previous page', 'yabp').'">< '.__('Previous', 'yabp').'</a></span> '; }
    if ($totalpage > 1) {
        if ($totalpage > $showpages) { $showed = ($totalpage+1) - $showpages; }
        else { $showed = 1; }
        
        $last_half    = ceil($showpages/2);
        $first_half = $showpages - $last_half;
        
        for ($i = 1; $i<=$totalpage; $i++) {    
            if ($i+$first_half >= $curpage && $i <= $curpage+$last_half) {
                if ($i == $curpage) { $nav .= '<span style="padding: 5px; font-weight: bold;">'.$i.'</span>'; }
                else { $nav .= '<span style="padding: 5px;"><a href="'.$link.''.$i.'">'.$i.'</a></span>'; }
            }
        }
    }

    if ($curpage < $totalpage) { $nav .= ' <span style="padding: 5px;"><a href="'.$link.''.$next_page.'" title="'.__('Next page', 'yabp').'">'.__('Next', 'yabp').' ></a></span>'; }
    if ($totalpage > 1 && $curpage != $totalpage) { $nav .= '<span style="padding: 5px;"><a href="'.$link.''.$totalpage.'" title="'.__('Last page', 'yabp').'">&raquo;</a></span>'; }    
    if ($totalpage == 1) { $nav = '<span style="font-weight: bold; padding: 5px;">1</span>'; }
    
    $pagenav = '<div class="pagination">'.$nav.'</div>';    
    return $pagenav;
}

function yabp_validate_apikey($apikey) {
    if (isset($apikey) && $apikey != null) {        
        $output = yabp_api_dorequest('GET', '/catalog/v4/search', '?q=boek&apikey='.$apikey.'&format=xml&offset=0&limit=1&dataoutput=products', '', null);
        if (substr_count($output, "200 OK") > 0) { 
            update_option('yabp_apikey_valid', true); 
            return true;
        }
        else { 
            update_option('yabp_apikey_valid', null); 
            return false;
        }
    }
    else { 
        update_option('yabp_apikey_valid', null); 
        return false;    
    }
}

function yabp_productmanager_send_expired_products_notification($test=false, $item_ids_array) {
    global $wpdb, $table_name_yabp;
    if ($test) {    
        $to = get_bloginfo('admin_email');
        $subject = "[".str_replace('http://', '', get_bloginfo('wpurl'))."] ".__('Test email notification', 'yabp');
        $body = __('Dear admin', 'yabp').',<br /><br />'.sprintf(__('This is a test email notification for the Yet Another bol.com Plugin of your website at %s. If you can read this, the test has succeeded, meaning you are able to receive future email notifications, if set up.', 'yabp'), get_bloginfo('wpurl')).'<br /><br />'.__('Kind regards', 'yabp').',<br />Yet Another bol.com Plugin';
        $headers = array('From: Yet Another bol.com Plugin <wordpress@'.str_replace('http://', '', get_bloginfo('wpurl')).'>', 'Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, $subject, $body, $headers);                    
    }
    else {
        $expired_items_list = '<ul>';
        foreach ($item_ids_array as $item_ids_array_current) {
            $expired_items_list .= '<li>'.yabp_item_title_via_entry_id($item_ids_array_current).'</li>';
        }
        $expired_items_list .= '</ul>';
        $to = get_bloginfo('admin_email');
        $subject = "[".str_replace('http://', '', get_bloginfo('wpurl'))."] ".__('Expired products notification', 'yabp');
        $body = __('Dear admin', 'yabp').',<br /><br />'.sprintf(__('Please be informed that one or more products from the Yet Another bol.com Plugin of your website at %s have been expired. See the list below:', 'yabp'), get_bloginfo('wpurl')).'<br /><br />'.$expired_items_list.'<br /><br />'.sprintf(__('<a href="%s">Click here</a> to replace the product(s).', 'yabp'), get_admin_url(null, 'admin.php?page=yabp-productmanager')).'<br /><br />'.__('Kind regards', 'yabp').',<br />Yet Another bol.com Plugin';
        $headers = array('From: Yet Another bol.com Plugin <wordpress@'.str_replace('http://', '', get_bloginfo('wpurl')).'>', 'Content-Type: text/html; charset=UTF-8');
        if (wp_mail($to, $subject, $body, $headers)) { 
            return $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_expired_notification_sent = '1' WHERE entry_id IN (".implode(', ', array_map('intval', $item_ids_array)).")");
        }        
    }
}

function yabp_forms() {
    global $wpdb, $yabp_add_item_item_defaultcountry, $yabp_add_item_item_count, $yabp_add_item_item_count_limit, $yabp_itemlist_count, $yabp_itemlist_count_limit, $yabp_styling_item_fontsize_lowlimit, $yabp_styling_item_fontsize_highlimit, $yabp_item_textlink_text, $yabp_item_freeshipping_text, $yabp_deals_countdown_text, $yabp_dealslist_count, $yabp_dealslist_count_limit, $yabp_deals_countdown_text_expired, $yabp_deal_expired_option_default;

    if (isset($_GET['action']) && $_GET['action'] == "yabp_productmanager_expired_products_notification_test") {         
        yabp_productmanager_send_expired_products_notification(true);
        wp_redirect($_SERVER['PHP_SELF'].'?page=yabp&updated=1');        
        die('Done');
    }
    
    if (isset($_POST['savetype']) && $_POST['savetype'] == 'saveoptions_yabp_options') {

        if (empty($_POST['yabp_apikey'])) { $_POST['yabp_apikey'] = null; }
        if ($_POST['yabp_apikey'] != get_option('yabp_apikey') || !get_option('yabp_apikey_valid')) { yabp_validate_apikey(trim($_POST['yabp_apikey'])); }
        update_option('yabp_apikey', trim($_POST['yabp_apikey']));
        if (!is_numeric($_POST['yabp_siteid'])) { $_POST['yabp_siteid'] = null; }
        update_option('yabp_siteid', trim($_POST['yabp_siteid']));

        if (!is_numeric($_POST['yabp_add_item_item_count']) || $_POST['yabp_add_item_item_count'] > $yabp_add_item_item_count_limit || $_POST['yabp_add_item_item_count'] == 0) { $_POST['yabp_add_item_item_count'] = $yabp_add_item_item_count; }
        else { $_POST['yabp_add_item_item_count'] = (int) $_POST['yabp_add_item_item_count']; }
        update_option('yabp_add_item_item_count', $_POST['yabp_add_item_item_count']);
        
        if (!is_numeric($_POST['yabp_itemlist_count']) || $_POST['yabp_itemlist_count'] > $yabp_itemlist_count_limit || $_POST['yabp_itemlist_count'] == 0) { $_POST['yabp_itemlist_count'] = $yabp_itemlist_count; }
        else { $_POST['yabp_itemlist_count'] = (int) $_POST['yabp_itemlist_count']; }        
        update_option('yabp_itemlist_count', $_POST['yabp_itemlist_count']);

        if (empty($_POST['yabp_item_textlink_text'])) { $_POST['yabp_item_textlink_text'] = __($yabp_item_textlink_text, 'yabp'); }
        update_option('yabp_item_textlink_text', $_POST['yabp_item_textlink_text']);

        if (empty($_POST['yabp_item_freeshipping_text'])) { $_POST['yabp_item_freeshipping_text'] = __($yabp_item_freeshipping_text, 'yabp'); }
        update_option('yabp_item_freeshipping_text', $_POST['yabp_item_freeshipping_text']);

        if (!is_numeric($_POST['yabp_add_item_item_defaultcountry'])) { $_POST['yabp_add_item_item_defaultcountry'] = $yabp_add_item_item_defaultcountry; }
        else { $_POST['yabp_add_item_item_defaultcountry'] = (int) $_POST['yabp_add_item_item_defaultcountry']; }        
        update_option('yabp_add_item_item_defaultcountry', $_POST['yabp_add_item_item_defaultcountry']);
        
        if (isset($_POST['yabp_productmanager_expired_products_notification'])) { update_option('yabp_productmanager_expired_products_notification', '1'); }
        elseif (!isset($_POST['yabp_productmanager_expired_products_notification'])) { update_option('yabp_productmanager_expired_products_notification', '0'); }

        if (!is_numeric($_POST['yabp_styling_item_title_fontsize']) || $_POST['yabp_styling_item_title_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_title_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_title_fontsize'] = null; }
        else { $_POST['yabp_styling_item_title_fontsize'] = (int) $_POST['yabp_styling_item_title_fontsize']; }
        update_option('yabp_styling_item_title_fontsize', $_POST['yabp_styling_item_title_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_subtitle_fontsize']) || $_POST['yabp_styling_item_subtitle_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_subtitle_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_subtitle_fontsize'] = null; }
        else { $_POST['yabp_styling_item_subtitle_fontsize'] = (int) $_POST['yabp_styling_item_subtitle_fontsize']; }
        update_option('yabp_styling_item_subtitle_fontsize', $_POST['yabp_styling_item_subtitle_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_price_fontsize']) || $_POST['yabp_styling_item_price_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_price_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_price_fontsize'] = null; }
        else { $_POST['yabp_styling_item_price_fontsize'] = (int) $_POST['yabp_styling_item_price_fontsize']; }
        update_option('yabp_styling_item_price_fontsize', $_POST['yabp_styling_item_price_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_listprice_fontsize']) || $_POST['yabp_styling_item_listprice_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_listprice_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_listprice_fontsize'] = null; }
        else { $_POST['yabp_styling_item_listprice_fontsize'] = (int) $_POST['yabp_styling_item_listprice_fontsize']; }
        update_option('yabp_styling_item_listprice_fontsize', $_POST['yabp_styling_item_listprice_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_availability_fontsize']) || $_POST['yabp_styling_item_availability_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_availability_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_availability_fontsize'] = null; }
        else { $_POST['yabp_styling_item_availability_fontsize'] = (int) $_POST['yabp_styling_item_availability_fontsize']; }
        update_option('yabp_styling_item_availability_fontsize', $_POST['yabp_styling_item_availability_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_freeshipping_fontsize']) || $_POST['yabp_styling_item_freeshipping_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_freeshipping_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_freeshipping_fontsize'] = null; }
        else { $_POST['yabp_styling_item_freeshipping_fontsize'] = (int) $_POST['yabp_styling_item_freeshipping_fontsize']; }
        update_option('yabp_styling_item_freeshipping_fontsize', $_POST['yabp_styling_item_freeshipping_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_countdown_fontsize']) || $_POST['yabp_styling_item_countdown_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_countdown_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_countdown_fontsize'] = null; }
        else { $_POST['yabp_styling_item_countdown_fontsize'] = (int) $_POST['yabp_styling_item_countdown_fontsize']; }
        update_option('yabp_styling_item_countdown_fontsize', $_POST['yabp_styling_item_countdown_fontsize']);
        if (!is_numeric($_POST['yabp_styling_item_textlink_fontsize']) || $_POST['yabp_styling_item_textlink_fontsize'] < $yabp_styling_item_fontsize_lowlimit || $_POST['yabp_styling_item_textlink_fontsize'] > $yabp_styling_item_fontsize_highlimit) { $_POST['yabp_styling_item_textlink_fontsize'] = null; }
        else { $_POST['yabp_styling_item_textlink_fontsize'] = (int) $_POST['yabp_styling_item_textlink_fontsize']; }
        update_option('yabp_styling_item_textlink_fontsize', $_POST['yabp_styling_item_textlink_fontsize']);

        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_title_fontcolour'])) { $_POST['yabp_styling_item_title_fontcolour'] = null; }
        update_option('yabp_styling_item_title_fontcolour', $_POST['yabp_styling_item_title_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_subtitle_fontcolour'])) { $_POST['yabp_styling_item_subtitle_fontcolour'] = null; }
        update_option('yabp_styling_item_subtitle_fontcolour', $_POST['yabp_styling_item_subtitle_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_price_fontcolour'])) { $_POST['yabp_styling_item_price_fontcolour'] = null; }
        update_option('yabp_styling_item_price_fontcolour', $_POST['yabp_styling_item_price_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_listprice_fontcolour'])) { $_POST['yabp_styling_item_listprice_fontcolour'] = null; }
        update_option('yabp_styling_item_listprice_fontcolour', $_POST['yabp_styling_item_listprice_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_availability_fontcolour'])) { $_POST['yabp_styling_item_availability_fontcolour'] = null; }
        update_option('yabp_styling_item_availability_fontcolour', $_POST['yabp_styling_item_availability_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_freeshipping_fontcolour'])) { $_POST['yabp_styling_item_freeshipping_fontcolour'] = null; }
        update_option('yabp_styling_item_freeshipping_fontcolour', $_POST['yabp_styling_item_freeshipping_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_countdown_fontcolour'])) { $_POST['yabp_styling_item_countdown_fontcolour'] = null; }
        update_option('yabp_styling_item_countdown_fontcolour', $_POST['yabp_styling_item_countdown_fontcolour']);
        if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', "#".$_POST['yabp_styling_item_textlink_fontcolour'])) { $_POST['yabp_styling_item_textlink_fontcolour'] = null; }
        update_option('yabp_styling_item_textlink_fontcolour', $_POST['yabp_styling_item_textlink_fontcolour']);
        
        wp_redirect($_SERVER['PHP_SELF'].'?page=yabp&updated=1');

        die('Done');    
    }    
}

function yabp_options() {
    global $wpdb, $yabp_styling_item_fontsize_lowlimit, $yabp_styling_item_fontsize_highlimit, $yabp_open_api_link, $yabp_partnerprogram_link, $yabp_deals_countdown_template, $yabp_add_item_item_count_limit, $yabp_itemlist_count_limit, $yabp_dealslist_count_limit;
    
    ?>
    <div class="wrap">
    <h2>Yet Another bol.com Plugin</h2>
    <h3><?php _e('Options', 'yabp'); ?></h3>
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1) { ?><div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#B9FF9C; border:1px solid #ccc;"><p><?php _e('Options successfully saved.', 'yabp'); ?></p></div><?php } ?>
    <div style="padding:10px; background:#fff; border:1px solid #ccc;">
    <form method="post">                        
        <p><strong><?php _e('Settings', 'yabp'); ?></strong></p>
        <p><?php _e('bol.com Open API key (API Access Key)', 'yabp'); ?>: <input type="text" size="50" value="<?php echo get_option('yabp_apikey')?>" name="yabp_apikey" /> <?php if (get_option('yabp_apikey')) { if (get_option('yabp_apikey_valid')) { ?><span style="color: green;"><?php _e('Valid!', 'yabp'); ?></span><?php } else { ?><span style="color: red;"><?php _e('Invalid!', 'yabp'); ?></span><?php } ?><?php } if (!get_option('yabp_apikey') || !get_option('yabp_apikey_valid')) { ?> <a href="<?php echo $yabp_open_api_link; ?>"><?php _e('Get an Open API key', 'yabp'); ?></a><?php } ?></p>
        <p><?php _e('bol.com Partner Program siteid', 'yabp'); ?>: <input type="text" size="50" value="<?php echo get_option('yabp_siteid')?>" name="yabp_siteid" /><?php if (!get_option('yabp_siteid') || get_option('yabp_siteid') == "") { ?> <a href="<?php echo $yabp_partnerprogram_link; ?>"><?php _e('Retrieve your SiteId', 'yabp'); ?></a><?php } ?></p>
        <p><?php _e('Number of products shown on the \'Add product\' page', 'yabp'); ?>: <input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_add_item_item_count')?>" name="yabp_add_item_item_count" /> (1-<?php echo $yabp_add_item_item_count_limit; ?>)</p>
        <p><?php _e('Number of products shown on the \'Product list\' page', 'yabp'); ?>: <input type="text" maxlength="3" size="3" value="<?php echo get_option('yabp_itemlist_count')?>" name="yabp_itemlist_count" /> (1-<?php echo $yabp_itemlist_count_limit; ?>)</p>
        <p><?php _e('Text of the text link of the products', 'yabp'); ?>: <input type="text" maxlength="100" size="50" value="<?php echo get_option('yabp_item_textlink_text')?>" name="yabp_item_textlink_text" /></p>
        <p><?php _e('\'Free shipping\' text at the products', 'yabp'); ?>: <input type="text" maxlength="100" size="50" value="<?php echo get_option('yabp_item_freeshipping_text')?>" name="yabp_item_freeshipping_text" /></p>
        <p><?php _e('Default product catalog:', 'yabp'); ?><br />
        <input type="radio" id="yabp_add_item_item_defaultcountry_nl" name="yabp_add_item_item_defaultcountry" value="1" <?php if (get_option('yabp_add_item_item_defaultcountry') == 1) { ?>checked <?php } ?>title="<?php _e('Set the Dutch catalog as the default catalog when searching for new products', 'yabp'); ?>" /> <label for="yabp_add_item_item_defaultcountry_nl" title="<?php _e('Set the Dutch catalog as the default catalog when searching for new products', 'yabp'); ?>"><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/'; ?>img/nl.png" alt="<?php _e('Dutch catalog', 'yabp'); ?>" /> <?php _e('Dutch catalog', 'yabp'); ?></label><br />
        <input type="radio" id="yabp_add_item_item_defaultcountry_be" name="yabp_add_item_item_defaultcountry" value="2" <?php if (get_option('yabp_add_item_item_defaultcountry') == 2) { ?>checked <?php } ?>title="<?php _e('Set the Belgium catalog as the default catalog when searching for new products', 'yabp'); ?>" /> <label for="yabp_add_item_item_defaultcountry_be" title="<?php _e('Set the Belgium catalog as the default catalog when searching for new products', 'yabp'); ?>"><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/'; ?>img/be.png" alt="<?php _e('Belgium catalog', 'yabp'); ?>" /> <?php _e('Belgium catalog', 'yabp'); ?></label></p>
        <p><a name="yabp_productmanager_expired_products_notification"></a><input type="checkbox" id="yabp_productmanager_expired_products_notification" name="yabp_productmanager_expired_products_notification" <?php if (get_option('yabp_productmanager_expired_products_notification') == 1) { ?>checked <?php } ?>title="<?php _e('Send an email notification to the admin\'s emailaddress when a product expires', 'yabp'); ?>" /> <label for="yabp_productmanager_expired_products_notification" title="<?php _e('Send an email notification to the admin\'s emailaddress when a product expires', 'yabp'); ?>"> <?php _e('Send an email notification when a product expires.', 'yabp'); ?></label> (<a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp&amp;action=yabp_productmanager_expired_products_notification_test"; ?>" title="<?php _e('To ensure you are able to receive email notifications, you can test this function by clicking on this link. Check your spam folder if you did not receive any email.', 'yabp'); ?>"><?php _e('Click here to send a test email', 'yabp'); ?></a>)</p>
        <p>&nbsp;</p>
        <p><strong><?php _e('Styling' , 'yabp'); ?></strong></p>
        <p style="font-style: italic;"><?php _e('The use of styling is optional. By default, the plugin uses the style from your current theme. When inserting font sizes and font colours below, you override the default style. The font size is the number in pixels, and the colours in hex code (eg. FF0000). You can pick hex codes by clicking on the input field. To delete a hex code, backspace it in its field. Don\'t forget to save!', 'yabp'); ?></p>
        <table>
            <tr><td><?php _e('Product title font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_title_fontsize')?>" name="yabp_styling_item_title_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('Product title font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_title_fontcolour')?>" name="yabp_styling_item_title_fontcolour" /></td></tr>
            <tr><td><?php _e('Product subtitle font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_subtitle_fontsize')?>" name="yabp_styling_item_subtitle_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('Product subtitle font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_subtitle_fontcolour')?>" name="yabp_styling_item_subtitle_fontcolour" /></td></tr>
            <tr><td><?php _e('Product price font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_price_fontsize')?>" name="yabp_styling_item_price_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('Product price font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_price_fontcolour')?>" name="yabp_styling_item_price_fontcolour" /></td></tr>
            <tr><td><?php _e('Product list price font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_listprice_fontsize')?>" name="yabp_styling_item_listprice_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('Product list price font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_listprice_fontcolour')?>" name="yabp_styling_item_listprice_fontcolour" /></td></tr>
            <tr><td><?php _e('Product availability font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_availability_fontsize')?>" name="yabp_styling_item_availability_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('Product availability font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_availability_fontcolour')?>" name="yabp_styling_item_availability_fontcolour" /></td></tr>
            <tr><td><?php _e('\'Free shipping\' text font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_freeshipping_fontsize')?>" name="yabp_styling_item_freeshipping_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('\'Free shipping\' text font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_freeshipping_fontcolour')?>" name="yabp_styling_item_freeshipping_fontcolour" /></td></tr>
            <tr><td><?php _e('\'Countdown\' text font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_countdown_fontsize')?>" name="yabp_styling_item_countdown_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('\'Countdown\' text font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_countdown_fontcolour')?>" name="yabp_styling_item_countdown_fontcolour" /></td></tr>
            <tr><td><?php _e('Product text link font size', 'yabp'); ?>:</td><td><input type="text" maxlength="2" size="2" value="<?php echo get_option('yabp_styling_item_textlink_fontsize')?>" name="yabp_styling_item_textlink_fontsize" /> <sub>(<?php echo $yabp_styling_item_fontsize_lowlimit."-".$yabp_styling_item_fontsize_highlimit; ?>)</sub></td></tr>
            <tr><td><?php _e('Product text link font colour', 'yabp'); ?>:</td><td><input class="color {required:false,pickerClosable:true,pickerCloseText:'<?php _e('Close', 'yabp'); ?>'}" type="text" maxlength="6" size="6" value="<?php echo get_option('yabp_styling_item_textlink_fontcolour')?>" name="yabp_styling_item_textlink_fontcolour" /></td></tr>
        </table>
        <p class="submit">
            <input type="hidden" name="savetype" value="saveoptions_yabp_options" />
            <input class="button-primary" name="save" type="submit" value="<?php _e('Save', 'yabp'); ?>" />
            <input type="hidden" name="action" value="save" />
        </p>
    </form>
    </div>
    <p>&nbsp;</p>
    <h3><?php _e('Message from the developer' , 'yabp'); ?></h3>
    <div style="padding:10px; background:#fff; border:1px solid #ccc;">
        <p><?php _e('Thank you for using this plugin! To support the developer for future development of it, a donation of any amount is really appreciated. If at any moment a pro version of this plugin is released, all supporters will get access to that version!' , 'yabp'); ?><br />
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="Z5Y8SDPMQK36A">
                <input type="image" src="https://www.paypalobjects.com/nl_NL/NL/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal, de veilige en complete manier van online betalen.">
                <img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1" />
            </form>
        </p>
        <p><?php _e('Take a look at the <a href="https://wordpress.org/plugins/yabp/">Plugin page on WordPress.org</a> for a step-by-step installation description and the FAQ. You may also find more information on <a href="http://tromit.nl/diensten/wordpress-plugins/">the homepage of the plugin</a>.' , 'yabp'); ?></p>
        <p><?php _e('This is not an official plugin from bol.com, but it is safe to use as bol.com\'s Open API v4 is being used. No personal data is saved or forwarded. The names and images in this plugin belong to their respective owners. By using this plugin, you agree to the following terms and conditions. The developer is not responsible for any errors or losses when using this plugin for earning money with the bol.com Partner Program. Your website has to comply with bol.com\'s terms and conditions at all times. You are responsible you use the correct Open API key and siteid with this plugin. At the moment, only the siteid cannot be checked automatically for its correctness.' , 'yabp'); ?></p>
    </div>

<?php
}

function yabp_add_item_new($bolid, $countryid, $check=false, $returnid=false) {
    if (isset($bolid) && isset($countryid) && is_numeric($countryid)) {
        global $wpdb, $table_name_yabp;
        if ($check) { 
            if ($wpdb->get_var("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_bolid = '".esc_sql($bolid)."'")) { return true; }
            else { return false; }
        }
        else {         
            if ($wpdb->query("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_bolid = '".esc_sql($bolid)."'")) { return false; }
            else { 
                if ($returnid) {
                    $bolid = esc_sql($bolid);
                    $countryid = esc_sql($countryid);
                    $wpdb->insert($table_name_yabp, array('entry_id' => '', 'entry_bolid' => $bolid, 'entry_thumb' => 3, 'entry_showthumb' => 1, 'entry_showprice' => 1, 'entry_showlistprice' => 1, 'entry_showtitle' => 1, 'entry_showsubtitle' => 1, 'entry_showavailability' => 1, 'entry_showrating' => 1, 'entry_showbutton' => 1, 'entry_updateinterval' => 3, 'entry_buttontype' => 1, 'entry_putincart' => 0, 'entry_recordimpressions' => 1, 'entry_openinnewtab' => 1, 'entry_style' => 1, 'entry_country' => $countryid, 'entry_showfreeshipping' => 1));
                    return $wpdb->insert_id;
                }
                else { 
                    $bolid = esc_sql($bolid);                
                    $countryid = esc_sql($countryid);
                    return $wpdb->insert($table_name_yabp, array('entry_id' => '', 'entry_bolid' => $bolid, 'entry_thumb' => 3, 'entry_showthumb' => 1, 'entry_showprice' => 1, 'entry_showlistprice' => 1, 'entry_showtitle' => 1, 'entry_showsubtitle' => 1, 'entry_showavailability' => 1, 'entry_showrating' => 1, 'entry_showbutton' => 1, 'entry_updateinterval' => 3, 'entry_buttontype' => 1, 'entry_putincart' => 0, 'entry_recordimpressions' => 1, 'entry_openinnewtab' => 1, 'entry_style' => 1, 'entry_country' => $countryid, 'entry_showfreeshipping' => 1));
                }
            }
        }
    }
    else { return false; }
}

function yabp_add_item_replace($entry_id, $bolid, $countryid) {
    if (isset($entry_id) && is_numeric($entry_id) && isset($bolid) && isset($countryid) && is_numeric($countryid)) {
        global $wpdb, $table_name_yabp;

        if ($wpdb->get_var("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'") && $wpdb->get_var("SELECT entry_bolid FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'") != $bolid) { 
            return $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_bolid = '".esc_sql($bolid)."', entry_country = '".esc_sql($countryid)."' WHERE entry_id = '".esc_sql($entry_id)."'"); 
        }
        else { return false; }
    }
    else { return false; }
}

function yabp_cron_updateinterval_check_number($interval) {
    global $wpdb, $table_name_yabp;

    if (isset($interval) && is_numeric($interval)) {
        if ($wpdb->get_var("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_updateinterval = '".esc_sql($interval)."'")) { return $wpdb->get_var("SELECT COUNT(entry_id) FROM `".$table_name_yabp."` WHERE entry_updateinterval = '".esc_sql($interval)."'"); }
        else { return false; }        
    }
    else { return false; }
}

function yabp_entry_value_via_entry_id($entry_id, $column, $formatted=false) {
    global $wpdb, $table_name_yabp;
    if (isset($entry_id) && is_numeric($entry_id) && isset($column) && ($column == 'entry_bolid' || $column == 'entry_updateinterval' || $column == 'entry_thumb' || $column == 'entry_buttontype' || $column == 'entry_country')) {
        if ($column == 'entry_thumb' && $formatted) {
            if ($wpdb->get_var("SELECT ".esc_sql($column)." FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'")) {
                $thumb_size = $wpdb->get_var("SELECT ".esc_sql($column)." FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'");
                switch ($thumb_size) {
                    case 1:
                        return "item_xsthumb";
                    case 2:
                        return "item_sthumb";
                    case 3:
                        return "item_mthumb";
                    case 4:
                        return "item_lthumb";
                    case 5:
                        return "item_xlthumb";
                }
                return false;
            }
            else { return false; }                            
        }
        elseif ($column == 'entry_country' && $formatted) {
            if ($wpdb->get_var("SELECT entry_country FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'")) {                 
                return ($wpdb->get_var("SELECT entry_country FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'")==2?"be":"nl");
            }            
            else { return false; }
        }
        else { 
            if ($wpdb->get_var("SELECT ".esc_sql($column)." FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'")) { return $wpdb->get_var("SELECT ".esc_sql($column)." FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'"); }
            else { return false; }                            
        }
    }
    else { return false; }
}

function yabp_entry_boolean_via_entry_id($entry_id, $column, $returnvalue=false) {
    global $wpdb, $table_name_yabp;
    if (isset($entry_id) && is_numeric($entry_id) && isset($column) && ($column == 'entry_showthumb' || $column == 'entry_showprice' || $column == 'entry_showlistprice' || $column == 'entry_showtitle' ||  $column == 'entry_showsubtitle' || $column == 'entry_showavailability' || $column == 'entry_showrating' || $column == 'entry_showbutton' || $column == 'entry_putincart' || $column == 'entry_recordimpressions' || $column == 'entry_openinnewtab' || $column == 'entry_imgontop' || $column == 'entry_showfreeshipping' || $column == 'entry_expired_notification_sent')) {
        if ($returnvalue) { return $wpdb->get_var("SELECT ".esc_sql($column)." FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'"); }
        else {
            if ($wpdb->get_var("SELECT ".esc_sql($column)." FROM `".$table_name_yabp."` WHERE entry_id = '".esc_sql($entry_id)."'") == 1) { return true; }
            else { return false; }                            
        }
    }
    else { return false; }
}

function yabp_item_title_via_entry_id($entry_id) {
    global $wpdb, $table_name_yabp_items;
    
    if (isset($entry_id) && is_numeric($entry_id)) { 
        if ($wpdb->get_var("SELECT item_title FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($entry_id)."'")) { return $wpdb->get_var("SELECT item_title FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($entry_id)."'"); }
        else { return false; }                
    }
    else { return false; }
}

function yabp_item_exists_via_entry_id($entry_id) {
    global $wpdb, $table_name_yabp_items;
    
    if (isset($entry_id) && is_numeric($entry_id)) { 
        if ($wpdb->get_var("SELECT item_id FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($entry_id)."'")) { return true; }
        else { return false; }                
    }
    else { return false; }
}

function yabp_item_value_via_column_name($entry_id, $column_name) {
    global $wpdb, $table_name_yabp_items;
    
    if (isset($entry_id) && is_numeric($entry_id) && isset($column_name) && ($column_name == 'item_title' || $column_name == 'item_subtitle' || $column_name == 'item_externalurl' || $column_name == 'item_afflink' || $column_name == 'item_xlthumb' || $column_name == 'item_lthumb' || $column_name == 'item_mthumb' || $column_name == 'item_sthumb' || $column_name == 'item_xsthumb' || $column_name == 'item_price' || $column_name == 'item_listprice' || $column_name == 'item_availability' || $column_name == 'item_availabilitycode' || $column_name == 'item_rating' || $column_name == 'item_ratingspan' || $column_name == 'item_productid' || $column_name == 'time')) {
        if ($wpdb->get_var("SELECT ".esc_sql($column_name)." FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($entry_id)."'")) { return $wpdb->get_var("SELECT ".esc_sql($column_name)." FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($entry_id)."'"); }
        else { return false; }                
    }
    else { return false; }
}

function yabp_item_update_via_entry_id($entry_id, $forced_update = false) {
    global $wpdb, $table_name_yabp, $table_name_yabp_items, $yabp_partnerlink_prefix, $yabp_add_item_item_defaultcountry;
    
    if (isset($entry_id) && is_numeric($entry_id)) { 

        if ((!get_option('yabp_apikey') || !get_option('yabp_siteid')) || !get_option('yabp_apikey_valid') || get_option('yabp_siteid') == "") { return false; }

        $number = 0;
        $bolid = yabp_entry_value_via_entry_id($entry_id, 'entry_bolid');        
        $countryid = ((int) yabp_entry_value_via_entry_id($entry_id, 'entry_country')>0?(int) yabp_entry_value_via_entry_id($entry_id, 'entry_country'):$yabp_add_item_item_defaultcountry);        
        $country = ($countryid==2?"be":"nl");            
        $output = yabp_api_dorequest('GET', '/catalog/v4/products/'.$bolid, '?apikey=' . get_option('yabp_apikey') . '&format=xml&includeattributes=true&country='.$country, '', null);
        
        if (substr_count($output, "200 OK") > 0) {
            
            $xml = strstr($output, '<?xml');
            $phpobject = simplexml_load_string($xml);

            $i = 0;                        
            foreach ($phpobject->Products as $item) {
                
                $number++;                
                //useful data   
                $item_title = $item -> Title;
                $item_subtitle = $item -> Subtitle;
                $item_externalurl = $item -> Urls[0] -> Value;
                //$item_afflink
                $item_xlthumb = preg_replace("/^http:/i", "https:", $item -> Images[4]-> Url);
                $item_lthumb = preg_replace("/^http:/i", "https:", $item -> Images[3]-> Url);
                $item_mthumb = preg_replace("/^http:/i", "https:", $item -> Images[2]-> Url);
                $item_sthumb = preg_replace("/^http:/i", "https:", $item -> Images[1]-> Url);
                $item_xsthumb = preg_replace("/^http:/i", "https:", $item -> Images[0]-> Url);
                $item_price = doubleval($item -> OfferData -> Offers[0] -> Price);
                $item_listprice = doubleval($item -> OfferData -> Offers[0] -> ListPrice);
                $item_availability = $item -> OfferData -> Offers[0] -> AvailabilityDescription;
                $item_availabilitycode = $item -> OfferData -> Offers[0] -> AvailabilityCode;
                $item_rating = $item -> Rating;
                //$item_ratingspan
                $time = time();

                if (@GetImageSize($item_sthumb)) { } 
                else { $item_sthumb = "https://www.bol.com/nl/static/images/main/noimage_124x100default.gif"; }

                if ($item_rating != "") {
                    $nicerating = (int) $item_rating/10;                    
                    $altrating = $nicerating;
                    $nicerating = round($nicerating * 2) / 2;
                    if (strlen($nicerating) < 2) { $nicerating .= ".0"; } 
                    $nicerating = str_replace(".", "_", $nicerating);                    
                    $item_ratingspan = '<span class="rating"><img alt="'.sprintf(__('Score %1$.1f out of 5 stars.', 'yabp'), $altrating).'" title="'.sprintf(__('Score %1$.1f out of 5 stars.', 'yabp'), $altrating).'" src="'.preg_replace("/^http:/i", "https:", plugin_dir_url( __FILE__ )).'img/icons/'. $nicerating . '.png"></span>';
                } 
                else { $item_ratingspan = ''; }
            }
        }
        else { return false; }

        if (substr_count($output, "404 Not Found") > 0 || substr_count($output, "403 Forbidden") > 0 || substr_count($output, "500 Internal Server Error") > 0 || substr_count($output, "405 Method Not Allowed") > 0 || substr_count($output, "400 Bad Request") > 0) { return false; }
                
        $item_afflink = $yabp_partnerlink_prefix.get_option('yabp_siteid')."&amp;f=TXL&amp;url=".urlencode($item_externalurl)."&amp;name=".urlencode(strtolower($item_title));        
                
        if (yabp_item_title_via_entry_id($entry_id) && $item_price > 0) {
            //entry exists, and product still available 
            $wpdb->update( 
                $table_name_yabp_items, 
                array( 
                    'item_title' => htmlspecialchars($item_title), 
                    'item_subtitle' => htmlspecialchars($item_subtitle), 
                    'item_externalurl' => htmlspecialchars($item_externalurl), 
                    'item_afflink' => htmlspecialchars($item_afflink), 
                    'item_xlthumb' => htmlspecialchars($item_xlthumb), 
                    'item_lthumb' => htmlspecialchars($item_lthumb), 
                    'item_mthumb' => htmlspecialchars($item_mthumb), 
                    'item_sthumb' => htmlspecialchars($item_sthumb), 
                    'item_xsthumb' => htmlspecialchars($item_xsthumb), 
                    'item_price' => $item_price, 
                    'item_listprice' => $item_listprice, 
                    'item_availability' => htmlspecialchars($item_availability), 
                    'item_availabilitycode' => htmlspecialchars($item_availabilitycode), 
                    'item_rating' => $item_rating, 
                    'item_ratingspan' => htmlspecialchars($item_ratingspan), 
                    'time' => $time                        
                ), 
                array( 'entry_id' => $entry_id ) 
            );            
            if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_expired_notification_sent', true) == 1) { $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_expired_notification_sent = '0' WHERE entry_id = '".esc_sql($entry_id)."'"); }            
        }
        elseif (yabp_item_title_via_entry_id($entry_id) && $item_price == 0) {
            //entry exists, and item (currently) not available
            $wpdb->update( 
                $table_name_yabp_items, 
                array( 
                    'item_price' => $item_price, 
                    'item_listprice' => $item_listprice, 
                    'item_availability' => htmlspecialchars($item_availability), 
                    'item_availabilitycode' => htmlspecialchars($item_availabilitycode), 
                    'time' => $time                        
                ), 
                array( 'entry_id' => $entry_id ) 
            );
        }
        elseif ((yabp_item_title_via_entry_id($entry_id) && $forced_update) || (!yabp_item_title_via_entry_id($entry_id) && $forced_update)) { 
            //entry does not exist, or item is expired and an forced update is applied
            if (!yabp_item_exists_via_entry_id($entry_id)) {
                //entry does not exist, create new
                $wpdb->insert( 
                    $table_name_yabp_items, 
                    array( 
                        'item_id' => '',
                        'entry_id' => $entry_id,
                        'item_title' => htmlspecialchars($item_title), 
                        'item_subtitle' => htmlspecialchars($item_subtitle), 
                        'item_externalurl' => htmlspecialchars($item_externalurl), 
                        'item_afflink' => htmlspecialchars($item_afflink), 
                        'item_xlthumb' => htmlspecialchars($item_xlthumb), 
                        'item_lthumb' => htmlspecialchars($item_lthumb), 
                        'item_mthumb' => htmlspecialchars($item_mthumb), 
                        'item_sthumb' => htmlspecialchars($item_sthumb), 
                        'item_xsthumb' => htmlspecialchars($item_xsthumb), 
                        'item_price' => $item_price, 
                        'item_listprice' => $item_listprice, 
                        'item_availability' => htmlspecialchars($item_availability), 
                        'item_availabilitycode' => htmlspecialchars($item_availabilitycode), 
                        'item_rating' => $item_rating, 
                        'item_ratingspan' => htmlspecialchars($item_ratingspan), 
                        'time' => $time,                        
                    ), 
                    array( 
                        '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', 
                    ) 
                );
            }
            else {
                //entry does exist, update current
                if ($item_price > 0) {
                    //product still available, so full update
                    $wpdb->update( 
                        $table_name_yabp_items, 
                        array( 
                            'item_title' => htmlspecialchars($item_title), 
                            'item_subtitle' => htmlspecialchars($item_subtitle), 
                            'item_externalurl' => htmlspecialchars($item_externalurl), 
                            'item_afflink' => htmlspecialchars($item_afflink), 
                            'item_xlthumb' => htmlspecialchars($item_xlthumb), 
                            'item_lthumb' => htmlspecialchars($item_lthumb), 
                            'item_mthumb' => htmlspecialchars($item_mthumb), 
                            'item_sthumb' => htmlspecialchars($item_sthumb), 
                            'item_xsthumb' => htmlspecialchars($item_xsthumb), 
                            'item_price' => $item_price, 
                            'item_listprice' => $item_listprice, 
                            'item_availability' => htmlspecialchars($item_availability), 
                            'item_availabilitycode' => htmlspecialchars($item_availabilitycode), 
                            'item_rating' => $item_rating, 
                            'item_ratingspan' => htmlspecialchars($item_ratingspan), 
                            'time' => $time                        
                        ), 
                        array( 'entry_id' => $entry_id ) 
                    );                        
                    if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_expired_notification_sent', true) == 1) { $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_expired_notification_sent = '0' WHERE entry_id = '".esc_sql($entry_id)."'"); }            
                }
                else {
                    //product not available: update price and availabilty, and keep current titles etc.
                    $wpdb->update( 
                        $table_name_yabp_items, 
                        array( 
                            'item_price' => $item_price, 
                            'item_listprice' => $item_listprice, 
                            'item_availability' => htmlspecialchars($item_availability), 
                            'item_availabilitycode' => htmlspecialchars($item_availabilitycode), 
                            'time' => $time                        
                        ), 
                        array( 'entry_id' => $entry_id ) 
                    );
                }                   
            }
        }
        return $item_price;        
    }
    else { return false; }
}

function yabp_added_item_pagenumber_itemlist() {
    global $wpdb, $table_name_yabp, $yabp_itemlist_count;
    
    if (!get_option('yabp_itemlist_count')) { $perpage = $yabp_itemlist_count; }
    else { $perpage = get_option('yabp_itemlist_count'); }
        
    $total = $wpdb->get_row("SELECT COUNT(entry_id) FROM `".$table_name_yabp."`",ARRAY_N);    
    $page = ceil($total[0] / (float) $perpage);
    return $page;
}

function yabp_page_exists_itemlist($pagenumber) {
    global $wpdb, $table_name_yabp, $yabp_itemlist_count;
    
    if (!get_option('yabp_itemlist_count')) { $perpage = $yabp_itemlist_count; }
    else { $perpage = get_option('yabp_itemlist_count'); }
        
    $total = $wpdb->get_row("SELECT COUNT(entry_id) FROM `".$table_name_yabp."`",ARRAY_N);
    $pages = ceil($total[0] / (float) $perpage);    
    
    if ($pages >= $pagenumber) { return true; }
    else { return false; }
}

function yabp_expired_items_count() {
    global $wpdb, $table_name_yabp_items;
    return $wpdb->get_var("SELECT COUNT(entry_id) FROM `".$table_name_yabp_items."` WHERE item_price = '0'");
}

function yabp_format_price($price) {
    if (isset($price) && is_numeric($price)) {
        
        if ($price == 0) { return __('Not available', 'yabp'); }
        
        if (substr_count($price, ".") < 1) { return "&euro;".$price.",-"; }
        else { 
            if (strlen(str_replace(".", "", strstr($price, "."))) == 1) { return "&euro;".str_replace(".", ",", $price)."0"; }
            else {
                return "&euro;".str_replace(".", ",", $price);
            }
        }
    }
    else { return false; }
}

function yabp_format_shortcode($entry_id, $type=null) {
    global $yabp_item_shortcode_format, $yabp_deals_shortcode_format;
    
    if (isset($entry_id) && is_numeric($entry_id)) {
        if (isset($type) && $type == "deals") { return str_replace("%entry_id%", $entry_id, $yabp_deals_shortcode_format); }
        else { return str_replace("%entry_id%", $entry_id, $yabp_item_shortcode_format); }
    }
    else { return false; }
}

function yabp_format_time($time) {
    global $yabp_item_time_format;
    
    if (isset($time) && is_numeric($time)) {
        return date($yabp_item_time_format, ($time+(get_option('gmt_offset') * 3600)));
    }
    else { return false; }
}

function yabp_format_updateinterval($updateinterval, $reverse=false, $backend=false, $cronfunction=false) {
    if (isset($updateinterval)) {
        if ($cronfunction) {
            switch ($updateinterval) {
                case 1:
                    return "yabp_cron_event_hourly";
                case 2:
                    return "yabp_cron_event_twicedaily";
                case 3:
                    return "yabp_cron_event_daily";
            }
            return false;
        }
        elseif ($backend) {
            switch ($updateinterval) {
                case 1:
                    return "hourly";
                case 2:
                    return "twicedaily";
                case 3:
                    return "daily";
            }
            return false;
        }
        elseif ($reverse) {
            $updateinterval = trim($updateinterval);
            switch ($updateinterval) {
                case __('hourly', 'yabp'):
                    return 1;
                case __('twice a day', 'yabp'):
                    return 2;
                case __('daily', 'yabp'):
                    return 3;
            }
            return false;
        }
        else {        
            switch ($updateinterval) {
                case 1:
                    return __('hourly', 'yabp');
                case 2:
                    return __('twice a day', 'yabp');
                case 3:
                    return __('daily', 'yabp');
            }
            return false;
        }
    }
    else { return false; }    
}

function yabp_format_thumbsize($thumbsize, $reverse=false) {
    if (isset($thumbsize)) {
        if ($reverse) {
            $thumbsize = trim($thumbsize);
            switch ($thumbsize) {
                case "XS":
                    return 1;
                case "S":
                    return 2;
                case "M":
                    return 3;
                case "L":
                    return 4;
                case "XL":
                    return 5;
            }
            return 3;
        }
        else {        
            switch ($thumbsize) {
                case 1:
                    return "XS";
                case 2:
                    return "S";
                case 3:
                    return "M";
                case 4:
                    return "L";
                case 5:
                    return "XL";
            }
            return false;
        }
    }
    else { return false; }    
}

function yabp_format_buttontype($buttontype, $reverse=false) {
    if (isset($buttontype)) {
        if ($reverse) {
            $buttontype = trim($buttontype);
            switch ($buttontype) {
                case __('View on', 'yabp'):
                    return 1;
                case __('View on (alt)', 'yabp'):
                    return 2;
                case __('Buy at', 'yabp'):
                    return 3;
                case __('Buy at (alt)', 'yabp'):
                    return 4;
            }
            return 3;
        }
        else {        
            switch ($buttontype) {
                case 1:
                    return __('View on', 'yabp');
                case 2:
                    return __('View on (alt)', 'yabp');
                case 3:
                    return __('Buy at', 'yabp');
                case 4:
                    return __('Buy at (alt)', 'yabp');
            }
            return false;
        }
    }
    else { return false; }    
}

function yabp_add_item() {
    global $wpdb, $yabp_add_item_item_count, $yabp_add_item_item_defaultcountry;
    
    if (!get_option('yabp_add_item_item_count')) { $item_count = $yabp_add_item_item_count; }
    else { $item_count = get_option('yabp_add_item_item_count'); }

    ?>
    <div class="wrap">
    <h2>Yet Another bol.com Plugin</h2>
    <h3><?php _e('Add product', 'yabp'); ?></h3>
    <?php    
    
    if ((!get_option('yabp_apikey') || !get_option('yabp_siteid')) || !get_option('yabp_apikey_valid') || get_option('yabp_siteid') == "") { ?><p><?php printf(__('Please enter both your valid bol.com Open API key and siteid at the <a href="%s">Options page</a> to continue.', 'yabp'), $_SERVER['PHP_SELF'].'?page=yabp'); ?></p><?php }
    else {                
        if (isset($_POST['yabp_add_items_reset'])) { unset($_POST['yabp_add_items_submit']); }
        elseif (isset($_POST['yabp_add_items_submit']) && empty($_POST['yabp_add_items_array'])) { unset($_POST); }
        elseif (isset($_POST['yabp_add_items_submit']) && !empty($_POST['yabp_add_items_array'])) {
            
            if (isset($_POST['yabp_replace_item_entry_id']) && is_numeric($_POST['yabp_replace_item_entry_id']) && yabp_entry_value_via_entry_id($_POST['yabp_replace_item_entry_id'], 'entry_bolid') && isset($_POST['yabp_replace_item_entry_id_p']) && is_numeric($_POST['yabp_replace_item_entry_id_p'])) {
                $yabp_replace_item_entry_id = $_POST['yabp_replace_item_entry_id'];
                $yabp_replace_item_entry_id_p = $_POST['yabp_replace_item_entry_id_p'];
                
                $countryid = (isset($_POST['yabp_add_items_previouscountry'])&&(int) $_POST['yabp_add_items_previouscountry']>0?(int) $_POST['yabp_add_items_previouscountry']:$yabp_add_item_item_defaultcountry);
                $trytoreplace = yabp_add_item_replace($yabp_replace_item_entry_id, $_POST['yabp_add_items_array'][0], $countryid);                
                if ($trytoreplace) { 
                    yabp_item_update_via_entry_id($yabp_replace_item_entry_id, true);                    
                    echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#B9FF9C; border:1px solid #ccc;"><p>'.sprintf(__('Item successfully replaced. <a href="%1$s">Click here</a> to view its settings.', 'yabp'), $_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$yabp_replace_item_entry_id_p.'#yabp-'.$yabp_replace_item_entry_id).'</p></div>';
                }
                else { echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p>'.__('The selected product is already found in the database, or an error occured. If you did not select a product twice, please try again later.', 'yabp').'</p></div>'; }                
            }
            else {            
                //add a product
                $countryid = (isset($_POST['yabp_add_items_previouscountry'])&&(int) $_POST['yabp_add_items_previouscountry']>0?(int) $_POST['yabp_add_items_previouscountry']:$yabp_add_item_item_defaultcountry);                
                $i = 0;
                foreach($_POST['yabp_add_items_array'] as $add_item) {
                    $trytoadd = yabp_add_item_new($add_item,$countryid,false,true);
                    if ($trytoadd) { 
                        yabp_item_update_via_entry_id($trytoadd, true);
                        $i++;                         
                        if ($i == 1) { $addid = $trytoadd; $addp = yabp_added_item_pagenumber_itemlist(); }
                    }
                }
                if ($i > 0) { echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#B9FF9C; border:1px solid #ccc;"><p>'.sprintf(__('%1$d product(s) successfully added. <a href="%2$s">Click here</a> to retrieve the shortcodes.', 'yabp'), $i, $_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$addp.'#yabp-'.$addid).'</p></div>'; }
                else { echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p>'.__('The selected products are already found in the database, or an error occured. If you did not select products twice, please try again later.', 'yabp').'</p></div>'; }
            }         
        }              
        elseif (isset($_POST['yabp_add_item_searchterm_submit']) && !empty($_POST['yabp_add_item_searchterm'])) {        
            $error = false;
            $retry = false;
            $number = 0;
            $resultlist = '';
            $keyword = wp_strip_all_tags($_POST['yabp_add_item_searchterm']);
            $countryid = (isset($_POST['yabp_add_item_country'])&&(int) $_POST['yabp_add_item_country']>0?(int) $_POST['yabp_add_item_country']:$yabp_add_item_item_defaultcountry);
            $country = ($countryid==2?"be":"nl");            
            $countryformattedtext = __(($countryid==2?"Belgium":"Dutch")." catalog", 'yabp');
            $countryformatted = '<img src="'.get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/img/'.$country.'.png" alt="'.$countryformattedtext.'" title="'.$countryformattedtext.'" /> '.$countryformattedtext;
            $output = yabp_api_dorequest('GET', '/catalog/v4/search', '?q=' . urlencode($keyword) . '&apikey=' . get_option('yabp_apikey') . '&format=xml&offset=0&limit='.$item_count.'&includeattributes=true&dataoutput=products&country='.$country, '', null);

            if (substr_count($output, "200 OK") > 0) {
                $xml = strstr($output, '<?xml');
                $phpobject = simplexml_load_string($xml);
                $totalresults = $phpobject->TotalResultSize;
                
            
                if ($totalresults == 0) { $summary = sprintf(__('No products found for search term \'%s\'. Please use another search term.', 'yabp'), stripslashes($keyword)); $retry = true; $error = true; }
                elseif ($totalresults == 1) { $summary = sprintf(__('Displaying the first and only result for search term \'%1$s\' from the %2$s.', 'yabp'), stripslashes($keyword), $countryformatted); }
                else { $summary = sprintf(__('Displaying the first %1$d of total %2$d results for search term \'%3$s\' from the %4$s.', 'yabp'), count($phpobject->Products), $totalresults, stripslashes($keyword), $countryformatted); }
                $i = 0;
                        
                foreach ($phpobject->Products as $item) {                
                    $id = $item -> Id;
                    $thumbnailurl = preg_replace("/^http:/i", "https:", $item -> Images[1]-> Url);
                    $title = $item -> Title;
                    $rating = $item -> Rating;
                    $price = doubleval($item -> OfferData -> Offers[0] -> Price);
                    $listprice = doubleval($item -> OfferData -> Offers[0] -> ListPrice);
                    $externalurl = $item -> Urls[0] -> Value;
                    $number++;                    

                    if (@GetImageSize($thumbnailurl)) { }
                    else { $thumbnailurl = "https://www.bol.com/nl/static/images/main/noimage_124x100default.gif"; }

                    if ($rating != "") {
                        $nicerating = (int) $rating/10;                    
                        $altrating = $nicerating;
                        $nicerating = round($nicerating * 2) / 2;
                        if (strlen($nicerating) < 2) { $nicerating .= ".0"; } 
                        $nicerating = str_replace(".", "_", $nicerating);                    
                        $ratingspan = '<span class="rating"><img alt="'.sprintf(__('Score %1$.1f out of 5 stars.', 'yabp'), $altrating).'" title="'.sprintf(__('Score %1$.1f out of 5 stars.', 'yabp'), $altrating).'" src="'.preg_replace("/^http:/i", "https:", plugin_dir_url( __FILE__ )).'img/icons/'. $nicerating . '.png"></span>';
                    } 
                    else { $ratingspan = ''; }
                
                    if ($number == count($phpobject->Products)) { $resultlist .= '<tr><td><input type="checkbox" name="yabp_add_items_array[]" value="'.$id.'"'.(yabp_add_item_new($id, $countryid, true)?' disabled="disabled" /><br /><br />('.__('product already in database', 'yabp').')':' />').'</td><td><a href="'.$externalurl.'"><img alt="'.$title.'" title="'.$title.'" src="'.$thumbnailurl.'" /></a></td><td><a href="'.$externalurl.'">'.$title.'</a><br /><br />(ID: '.$id.')</td><td>'.($listprice>0?'<span style="text-decoration: line-through;">'.yabp_format_price($listprice).'</span> ':'').yabp_format_price($price).'</td><td>'.($ratingspan==""?__('Not rated yet', 'yabp'):$ratingspan).'</td></tr>'."\n"; }
                    else { $resultlist .= '<tr><td style="border-bottom: 1px solid grey;"><input type="checkbox" name="yabp_add_items_array[]" value="'.$id.'"'.(yabp_add_item_new($id, $countryid, true)?' disabled="disabled" /><br /><br />('.__('product already in database', 'yabp').')':' />').'</td><td style="border-bottom: 1px solid grey;"><a href="'.$externalurl.'"><img alt="'.$title.'" title="'.$title.'" src="'.$thumbnailurl.'" /></a></td><td style="border-bottom: 1px solid grey;"><a href="'.$externalurl.'">'.$title.'</a><br /><br />(ID: '.$id.')</td><td style="border-bottom: 1px solid grey;">'.($listprice>0?'<span style="text-decoration: line-through;">'.yabp_format_price($listprice).'</span> ':'').yabp_format_price($price).'</td><td style="border-bottom: 1px solid grey;">'.($ratingspan==""?__('Not rated yet', 'yabp'):$ratingspan).'</td></tr>'."\n"; }
                }
            }
            elseif (substr_count($output, "403 Forbidden") > 0) { $summary .= sprintf(__('Your bol.com Open API key is invalid. Please enter the correct one at the <a href="%s">Options page</a>.', 'yabp'), $_SERVER['PHP_SELF'].'?page=yabp'); update_option('yabp_apikey_valid', null); $error = true; }
            elseif (substr_count($output, "500 Internal Server Error") > 0 || substr_count($output, "503 Service Unavailable") > 0) { $summary .= __('An error occured. At the moment, the Open API is not working. Please try again later.', 'yabp'); $error = true; }
            elseif (substr_count($output, "400 Bad Request") > 0 || substr_count($output, "405 Method Not Allowed") > 0) { $summary .= __('The plugin cannot connect to the Open API at the moment. Please contact the developer of this plugin to fix this problem.', 'yabp'); $error = true; }
            elseif (substr_count($output, "404 Not Found") > 0) { $summary .= __('No products can be found. Please use another search term.', 'yabp'); $error = true; }
        
            if ($error) { ?><div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p><?php echo $summary; ?></p></div><?php }
            else { ?><div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#B9FF9C; border:1px solid #ccc;"><p><?php echo $summary; ?></p></div><?php }

            if (((isset($_GET['action']) && $_GET['action'] == "replace_item" && isset($_GET['entry_id']) && is_numeric($_GET['entry_id']) && yabp_entry_value_via_entry_id($_GET['entry_id'], 'entry_bolid') && isset($_GET['p']) && is_numeric($_GET['p'])) || (isset($_POST['yabp_replace_item_entry_id']) && is_numeric($_POST['yabp_replace_item_entry_id']) && yabp_entry_value_via_entry_id($_POST['yabp_replace_item_entry_id'], 'entry_bolid') && isset($_POST['yabp_replace_item_entry_id_p']) && is_numeric($_POST['yabp_replace_item_entry_id_p']))) && !$error) { 
                if (isset($_POST['yabp_replace_item_entry_id'])) { 
                    $replace_entry_id = $_POST['yabp_replace_item_entry_id']; 
                    $replace_entry_id_p = $_POST['yabp_replace_item_entry_id_p']; 
                } 
                else { 
                    $replace_entry_id = $_GET['entry_id']; 
                    $replace_entry_id_p = $_GET['p']; 
                } 
                ?><div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#B5EBFF; border:1px solid #ccc;"><p><?php (yabp_item_title_via_entry_id($replace_entry_id)==''||yabp_item_title_via_entry_id($replace_entry_id)==null?printf(__('Selected item will be replaced by the product you select from the bol.com Catalog. <a href="%1$s">Click here</a> to cancel this, and to go back to the item\'s settings.', 'yabp'), $_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$replace_entry_id_p.'#yabp-'.$replace_entry_id):printf(__('Item \'%1$s\' will be replaced by the product you select from the bol.com Catalog. <a href="%2$s">Click here</a> to cancel this, and to go back to the item\'s settings.', 'yabp'), yabp_item_title_via_entry_id($replace_entry_id), $_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$replace_entry_id_p.'#yabp-'.$replace_entry_id)); ?></p></div><?php
                $resultlist = str_replace("type=\"checkbox\"", "type=\"radio\"", $resultlist);
            }
        
            if (isset($resultlist) && !$error) {
            ?>                            
            <form method="post">
                <table class="widefat comments fixed" cellspacing="0">
                <thead><tr><th></th><th><?php _e('Thumbnail', 'yabp'); ?></th><th><?php _e('Title', 'yabp'); ?></th><th><?php _e('Price', 'yabp'); ?></th><th><?php _e('Rating', 'yabp'); ?></th></tr></thead>
                <tbody>
                    <?php echo $resultlist; ?>
                </tbody>
                </table>            
                <p class="submit">
                    <input class="button-primary" name="yabp_add_items_submit" type="submit" value="<?php if (isset($replace_entry_id)) { _e('Replace', 'yabp'); } else { _e('Add selected', 'yabp'); } ?>" />
                    <input class="button-secondary" name="yabp_add_items_reset" type="submit" value="<?php _e('Edit search terms', 'yabp'); ?>" />
                    <input type="hidden" name="yabp_add_items_previoussearchterm" value="<?php echo stripslashes($keyword); ?>" />
                    <input type="hidden" name="yabp_add_items_previouscountry" value="<?php echo $countryid; ?>" />
                    <?php if (isset($replace_entry_id) && isset($replace_entry_id_p)) { ?><input type="hidden" name="yabp_replace_item_entry_id" id="yabp_replace_item_entry_id" value="<?php echo $replace_entry_id; ?>" /><input type="hidden" name="yabp_replace_item_entry_id_p" id="yabp_replace_item_entry_id_p" value="<?php echo $replace_entry_id_p; ?>" /><?php } ?>
                </p>
            </form>
            <?php
            }        
        }     
        
        if ((!isset($_POST['yabp_add_item_searchterm_submit']) && !isset($_POST['yabp_add_items_submit'])) || (isset($_POST['yabp_add_item_searchterm_submit']) && empty($_POST['yabp_add_item_searchterm'])) || (isset($retry) && $retry)) {        
        ?>
        <?php if (isset($_POST['yabp_add_item_searchterm_submit']) && empty($_POST['yabp_add_item_searchterm'])) { ?><div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p><?php _e('Enter a search term to continue.', 'yabp'); ?></p></div><?php } ?>
        <?php if ((isset($_GET['action']) && $_GET['action'] == "replace_item" && isset($_GET['entry_id']) && is_numeric($_GET['entry_id']) && yabp_entry_value_via_entry_id($_GET['entry_id'], 'entry_bolid') && isset($_GET['p']) && is_numeric($_GET['p'])) || (isset($_POST['yabp_replace_item_entry_id']) && is_numeric($_POST['yabp_replace_item_entry_id']) && yabp_entry_value_via_entry_id($_POST['yabp_replace_item_entry_id'], 'entry_bolid') && isset($_POST['yabp_replace_item_entry_id_p']) && is_numeric($_POST['yabp_replace_item_entry_id_p']))) { if (isset($_POST['yabp_replace_item_entry_id'])) { $replace_entry_id = $_POST['yabp_replace_item_entry_id']; $replace_entry_id_p = $_POST['yabp_replace_item_entry_id_p']; } else { $replace_entry_id = $_GET['entry_id']; $replace_entry_id_p = $_GET['p']; } ?><div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#B5EBFF; border:1px solid #ccc;"><p><?php printf(__('Item \'%1$s\' will be replaced by the product you select from the bol.com Catalog. <a href="%2$s">Click here</a> to cancel this, and to go back to the item\'s settings.', 'yabp'), yabp_item_title_via_entry_id($replace_entry_id), $_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$replace_entry_id_p.'#yabp-'.$replace_entry_id); ?></p></div><?php } ?>
        <form method="post">        
            <p><input type="radio" id="yabp_add_item_country_nl" name="yabp_add_item_country" value="1" <?php if ((!isset($_POST['yabp_add_item_searchterm_submit']) && get_option('yabp_add_item_item_defaultcountry') == 1) || (isset($_POST['yabp_add_item_searchterm_submit']) && $_POST['yabp_add_item_country'] == 1) || (isset($_POST['yabp_add_items_reset']) && isset($_POST['yabp_add_items_previouscountry']) && is_numeric($_POST['yabp_add_items_previouscountry']) && $_POST['yabp_add_items_previouscountry'] == 1)) { ?>checked <?php } ?>title="<?php _e('Search for products in the Dutch catalog', 'yabp'); ?>" /> <label for="yabp_add_item_country_nl" title="<?php _e('Search for products in the Dutch catalog', 'yabp'); ?>"><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/'; ?>img/nl.png" alt="<?php _e('Dutch catalog', 'yabp'); ?>" /> <?php _e('Dutch catalog', 'yabp'); ?></label><br />
            <input type="radio" id="yabp_add_item_country_be" name="yabp_add_item_country" value="2" <?php if ((!isset($_POST['yabp_add_item_searchterm_submit']) && get_option('yabp_add_item_item_defaultcountry') == 2) || (isset($_POST['yabp_add_item_searchterm_submit']) && $_POST['yabp_add_item_country'] == 2) || (isset($_POST['yabp_add_items_reset']) && isset($_POST['yabp_add_items_previouscountry']) && is_numeric($_POST['yabp_add_items_previouscountry']) && $_POST['yabp_add_items_previouscountry'] == 2)) { ?>checked <?php } ?>title="<?php _e('Search for products in the Belgium catalog', 'yabp'); ?>" /> <label for="yabp_add_item_country_be" title="<?php _e('Search for products in the Belgium catalog', 'yabp'); ?>"><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/'; ?>img/be.png" alt="<?php _e('Belgium catalog', 'yabp'); ?>" /> <?php _e('Belgium catalog', 'yabp'); ?></label></p>
            <p><?php _e('Search terms', 'yabp'); ?>: <input type="text" size="80" name="yabp_add_item_searchterm"<?php if (isset($_POST['yabp_add_items_reset']) && isset($_POST['yabp_add_items_previoussearchterm'])) { ?> value="<?php echo stripslashes($_POST['yabp_add_items_previoussearchterm']); ?>"<?php } ?> /></p>
            <p class="submit">
                <input class="button-primary" name="yabp_add_item_searchterm_submit" type="submit" value="<?php _e('Search', 'yabp'); ?>" />
                <?php if ((isset($_GET['action']) && $_GET['action'] == "replace_item" && isset($_GET['entry_id']) && is_numeric($_GET['entry_id']) && yabp_entry_value_via_entry_id($_GET['entry_id'], 'entry_bolid') && isset($_GET['p']) && is_numeric($_GET['p'])) || (isset($_POST['yabp_replace_item_entry_id']) && is_numeric($_POST['yabp_replace_item_entry_id']) && yabp_entry_value_via_entry_id($_POST['yabp_replace_item_entry_id'], 'entry_bolid') && isset($_POST['yabp_replace_item_entry_id_p']) && is_numeric($_POST['yabp_replace_item_entry_id_p']))) { ?><input type="hidden" name="yabp_replace_item_entry_id" id="yabp_replace_item_entry_id" value="<?php echo $replace_entry_id; ?>" /><input type="hidden" name="yabp_replace_item_entry_id_p" id="yabp_replace_item_entry_id_p" value="<?php echo $replace_entry_id_p; ?>" /><?php } ?>
            </p>
        </form>
        <?php
        }
    }
}

function yabp_itemlist() {
    global $wpdb, $table_name_yabp, $table_name_yabp_items, $yabp_itemlist_count;
    
    if (!get_option('yabp_itemlist_count')) { $perpage = $yabp_itemlist_count; }
    else { $perpage = get_option('yabp_itemlist_count'); }

    ?>
    <div class="wrap">
    <h2>Yet Another bol.com Plugin</h2>
    <h3><?php _e('Product list', 'yabp'); ?></h3>        
    <?php        
    
    $_page = isset($_GET['p']) && intval($_GET['p']) != '' ? $_GET['p'] : 1;    
    $gettotal = $wpdb->get_row("SELECT COUNT(entry_id) FROM `".$table_name_yabp."`",ARRAY_N);
    $totalpage = ceil( $gettotal[0] / (float) $perpage);
    $limit = ( ($_page-1) * $perpage).','.$perpage;
    
    if ($gettotal[0] == 0) {
        echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p>'.__('No products can be found.', 'yabp').'</p></div>'."\n";
        return;
    }
            
    if ($_page > $totalpage) {
        echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p>'.sprintf(__('Invalid page. <a href="%s">Click here</a> to go back.', 'yabp'), $_SERVER['PHP_SELF'].'?page=yabp-itemlist').'</p></div>';
        return;
    }

    $nav = yabp_pagelinks($_SERVER['PHP_SELF'].'?page=yabp-itemlist&amp;p=', 10, $totalpage, $_page);    
    $items_entries = $wpdb->get_results("SELECT entry_id FROM `".$table_name_yabp."` LIMIT ".$limit);
    
    ?>
    <script type="text/javascript" src="<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/'; ?>js/jquery.inplace.js"></script>
    <script type="text/javascript">
        function sure() {
            var ask = confirm('<?php _e('Are you sure?', 'yabp'); ?>');
            if (ask == true) { return true; }
            else { return false; }
        }
        
        jQuery(document).ready(function() {            
            jQuery(".update_intervals").editInPlace({
                url: "<?php echo $_SERVER['PHP_SELF']?>?page=yabp-itemlist&action=edititemupdateinterval",
                field_type: "select",
                select_options: "<?php _e('hourly', 'yabp'); ?>,<?php _e('twice a day', 'yabp'); ?>,<?php _e('daily', 'yabp'); ?>",
                default_text: "[<?php _e('Click to add', 'yabp'); ?>]",
                select_text: "<?php _e('Choose value', 'yabp'); ?>",
                save_button: '<input type="submit" class="inplace_save" value="<?php _e('Save', 'yabp'); ?>" />',
                cancel_button: '<input type="submit" class="inplace_cancel" value="<?php _e('Cancel', 'yabp'); ?>" />',                
                saving_image: "<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)); ?>/img/loading_small.gif"
              });
            jQuery(".thumb_sizes").editInPlace({
                url: "<?php echo $_SERVER['PHP_SELF']?>?page=yabp-itemlist&action=edititemthumbsize",
                field_type: "select",
                select_options: "XS,S,M,L,XL",
                default_text: "[<?php _e('Click to add', 'yabp'); ?>]",
                select_text: "<?php _e('Choose value', 'yabp'); ?>",
                save_button: '<input type="submit" class="inplace_save" value="<?php _e('Save', 'yabp'); ?>" />',
                cancel_button: '<input type="submit" class="inplace_cancel" value="<?php _e('Cancel', 'yabp'); ?>" />',                
                saving_image: "<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)); ?>/img/ajax_loading_small.gif"
              });
            jQuery(".button_types").editInPlace({
                url: "<?php echo $_SERVER['PHP_SELF']?>?page=yabp-itemlist&action=edititembuttontype",
                field_type: "select",
                select_options: "<?php _e('View on', 'yabp'); ?>,<?php _e('View on (alt)', 'yabp'); ?>,<?php _e('Buy at', 'yabp'); ?>,<?php _e('Buy at (alt)', 'yabp'); ?>",
                default_text: "[<?php _e('Click to add', 'yabp'); ?>]",
                select_text: "<?php _e('Choose value', 'yabp'); ?>",
                save_button: '<input type="submit" class="inplace_save" value="<?php _e('Save', 'yabp'); ?>" />',
                cancel_button: '<input type="submit" class="inplace_cancel" value="<?php _e('Cancel', 'yabp'); ?>" />',                
                saving_image: "<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)); ?>/img/ajax_loading_small.gif"
              });
        }); 
    </script>
    <?php if ($_page == 1) { ?><div style="margin-bottom:10px; padding:5px; background:#B5EBFF; border:1px solid #ccc;"><p><?php _e('You can add subids at your links by adding \'subid="your sub id"\' to the shortcodes. For example: [yabp 1 subid="homepage header"]. You can edit the update interval, thumbnail size and button type by clicking on it\'s current value. Update interval \'daily\' is recommended.', 'yabp'); ?></p></div><?php } ?>
    <table class="widefat comments fixed" cellspacing="0">
        <thead><tr><th># / <?php _e('Shortcode', 'yabp'); ?> (<a href="#" title="<?php _e('Paste shortcodes of products anywhere on your website to display these products with their own settings.', 'yabp'); ?>">?</a>)</th><th><?php _e('Thumbnail', 'yabp'); ?></th><th><?php _e('Title', 'yabp'); ?> / <?php _e('Last update', 'yabp'); ?></th><th><?php _e('Price', 'yabp'); ?> / <?php _e('Rating', 'yabp'); ?></th><th><?php _e('Options', 'yabp'); ?></th></tr></thead>
        <tbody>
    <?php
        $i = 0;
        foreach ($items_entries as $item_entry) {            
            $item = $wpdb->get_row("SELECT * FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($item_entry->entry_id)."'");            
            $i++;
            ?>
            <tr>
                <?php if (!yabp_item_title_via_entry_id($item_entry->entry_id)) { 
                    if ($i == count($items_entries)) { ?><td><?php echo ((($_page-1) * $perpage) + $i); ?></td><td colspan="3"><?php _e('Click \'Update now\' to retrieve this products\'s data.', 'yabp'); ?></td><td><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=update_item&amp;entry_id=".$item_entry->entry_id; ?>"><?php _e('Update now', 'yabp'); ?></a><br /><br /><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-add-item&amp;action=replace_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" onclick="return sure()" title="<?php _e('Replace this item with an item from the bol.com Catalog', 'yabp'); ?>"><?php _e('Replace', 'yabp'); ?></a><br /><br /><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=delete_item&amp;entry_id=".$item_entry->entry_id."&amp;p=".$_page; ?>" onclick="return sure()"><?php _e('Delete', 'yabp'); ?></a></td><?php }
                    else { ?><td style="border-bottom: 1px solid grey;"><?php echo ((($_page-1) * $perpage) + $i); ?></td><td colspan="3" style="border-bottom: 1px solid grey;"><?php _e('Click \'Update now\' to retrieve this products\'s data.', 'yabp'); ?></td><td style="border-bottom: 1px solid grey;"><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=update_item&amp;entry_id=".$item_entry->entry_id; ?>"><?php _e('Update now', 'yabp'); ?></a><br /><br /><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-add-item&amp;action=replace_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" onclick="return sure()" title="<?php _e('Replace this item with an item from the bol.com Catalog', 'yabp'); ?>"><?php _e('Replace', 'yabp'); ?></a><br /><br /><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=delete_item&amp;entry_id=".$item_entry->entry_id."&amp;p=".$_page; ?>" onclick="return sure()"><?php _e('Delete', 'yabp'); ?></a></td><?php }
                }
                else { 
                    ?><td><a name="yabp-<?php echo $item->entry_id; ?>"></a><strong><?php echo ((($_page-1) * $perpage) + $i); ?></strong><br /><br /><input value="<?php echo yabp_format_shortcode($item->entry_id); ?>" size="<?php echo strlen(yabp_format_shortcode($item->entry_id)); ?>" onClick="this.setSelectionRange(0, this.value.length)" /><br /><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-shortcodegenerator&amp;entry_id=".$item->entry_id; ?>"><?php _e('More options', 'yabp'); ?></a></td>
                    <td><a href="<?php echo htmlspecialchars_decode($item->item_externalurl); ?>"><img alt="<?php echo htmlspecialchars_decode($item->item_title); ?>" title="<?php echo htmlspecialchars_decode($item->item_title); ?>" src="<?php echo htmlspecialchars_decode($item->item_mthumb); ?>" /></a></td>
                    <td><a href="<?php echo htmlspecialchars_decode($item->item_externalurl); ?>"><?php echo htmlspecialchars_decode($item->item_title); ?></a><br /><br /><?php echo yabp_format_time($item->time); ?><br /><br /><?php $countryid = yabp_entry_value_via_entry_id($item->entry_id, 'entry_country'); $country = yabp_entry_value_via_entry_id($item->entry_id, 'entry_country', true); if ($countryid == 2) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_country&amp;value=1&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to retrieve this product\'s data from the Dutch catalog', 'yabp'); ?>"><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_country&amp;value=2&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to retrieve this product\'s data from the Belgium catalog', 'yabp'); ?>"><?php } ?><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.basename(dirname(__FILE__)).'/'; ?>img/<?php echo $country; ?>.png" alt="<?php __(($countryid==2?"Belgium":"Dutch")." catalog", 'yabp'); ?>" /></a></td>
                    <td><?php echo ($item->item_listprice>0?"<span style=\"text-decoration: line-through;\">".yabp_format_price($item->item_listprice)."</span> ":"").yabp_format_price($item->item_price); ?><br /><br /><?php if (empty($item->item_ratingspan)) { _e('Not rated yet', 'yabp'); } else { echo htmlspecialchars_decode($item->item_ratingspan); } ?></td>
                    <td><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=update_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Update this item now', 'yabp'); ?>"><?php _e('Update now', 'yabp'); ?></a><br /><br />
                    <?php _e('Update interval', 'yabp'); ?>:<br /><span class="update_intervals" id="update_interval-<?php echo $item->entry_id; ?>" title="<?php _e('Edit value', 'yabp'); ?>"><?php echo yabp_format_updateinterval(yabp_entry_value_via_entry_id($item->entry_id, 'entry_updateinterval')); ?></span><br /><br />
                    <?php _e('Thumbnail size', 'yabp'); ?>:<br /><span class="thumb_sizes" id="thumb_size-<?php echo $item->entry_id; ?>" title="<?php _e('Edit value', 'yabp'); ?>"><?php echo yabp_format_thumbsize(yabp_entry_value_via_entry_id($item->entry_id, 'entry_thumb')); ?></span><br /><br />
                    <?php _e('Button type', 'yabp'); ?>:<br /><span class="button_types" id="button_type-<?php echo $item->entry_id; ?>" title="<?php _e('Edit value', 'yabp'); ?>"><?php echo yabp_format_buttontype(yabp_entry_value_via_entry_id($item->entry_id, 'entry_buttontype')); ?></span><br /><br />
                    <a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-add-item&amp;action=replace_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" onclick="return sure()" title="<?php _e('Replace this item with an item from the bol.com Catalog', 'yabp'); ?>"><?php _e('Replace', 'yabp'); ?></a><br /><br />
                    <a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=delete_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" onclick="return sure()" title="<?php _e('Delete this item', 'yabp'); ?>"><?php _e('Delete', 'yabp'); ?></a></td>
                    </tr><tr><td colspan="5" style="<?php if ($i != count($items_entries)) { ?>border-bottom: 1px solid grey; <?php } ?>text-align: center;">
                     <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showthumb')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showthumb&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the thumbnail', 'yabp'); ?>"><?php _e('Hide thumbnail', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showthumb&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the thumbnail', 'yabp'); ?>"><?php _e('Show thumbnail', 'yabp'); ?></a><?php } ?>
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showprice')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showprice&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the price', 'yabp'); ?>"><?php _e('Hide price', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showprice&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the price', 'yabp'); ?>"><?php _e('Show price', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showlistprice')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showlistprice&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the list price', 'yabp'); ?>"><?php _e('Hide list price', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showlistprice&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the list price', 'yabp'); ?>"><?php _e('Show list price', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showtitle')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showtitle&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the title', 'yabp'); ?>"><?php _e('Hide title', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showtitle&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the title', 'yabp'); ?>"><?php _e('Show title', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showsubtitle')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showsubtitle&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the subtitle', 'yabp'); ?>"><?php _e('Hide subtitle', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showsubtitle&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the subtitle', 'yabp'); ?>"><?php _e('Show subtitle', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showavailability')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showavailability&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the availability', 'yabp'); ?>"><?php _e('Hide availability', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showavailability&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the availability', 'yabp'); ?>"><?php _e('Show availability', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showrating')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showrating&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the rating', 'yabp'); ?>"><?php _e('Hide rating', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showrating&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the rating', 'yabp'); ?>"><?php _e('Show rating', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showbutton')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showbutton&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the button', 'yabp'); ?>"><?php _e('Hide button', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showbutton&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the button', 'yabp'); ?>"><?php _e('Show button', 'yabp'); ?></a><?php } ?>
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_putincart')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_putincart&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to do not put product directly in cart', 'yabp'); ?>"><?php _e('Do not put in cart', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_putincart&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to put product directly in cart', 'yabp'); ?>"><?php _e('Do put in cart', 'yabp'); ?></a><?php } ?> 
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_recordimpressions')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_recordimpressions&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to do not record impressions', 'yabp'); ?>"><?php _e('Do not record impressions', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_recordimpressions&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to record impressions', 'yabp'); ?>"><?php _e('Do record impressions', 'yabp'); ?></a><?php } ?>
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_openinnewtab')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_openinnewtab&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to open item in the current tab', 'yabp'); ?>"><?php _e('Open item in current tab', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_openinnewtab&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to open item in a new tab', 'yabp'); ?>"><?php _e('Open item in new tab', 'yabp'); ?></a><?php } ?>
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_imgontop')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_imgontop&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show thumbnail above the product information', 'yabp'); ?>"><?php _e('Show thumbnail next to content', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_imgontop&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show thumbnail above the product information', 'yabp'); ?>"><?php _e('Show thumbnail above content', 'yabp'); ?></a><?php } ?>
                     | <?php if (yabp_entry_boolean_via_entry_id($item->entry_id, 'entry_showfreeshipping')) { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showfreeshipping&amp;value=false&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to hide the \'Free shipping\' text', 'yabp'); ?>"><?php _e('Hide \'Free shipping\' text', 'yabp'); ?></a><?php } else { ?><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;do=entry_setup&amp;action=entry_showfreeshipping&amp;value=true&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" title="<?php _e('Click to show the \'Free shipping\' text (when applicable)', 'yabp'); ?>"><?php _e('Show \'Free shipping\' text', 'yabp'); ?></a><?php } ?></td><?php                                
                }
                ?>
            </tr>
        <?php 
        }        

        if (empty($items_entries)) { ?><tr><td></td><td><?php _e('No products found.', 'yabp'); ?></td></tr><?php }
        ?>
        </tbody>
        <tfoot><tr><th># / <?php _e('Shortcode', 'yabp'); ?> (<a href="#" title="<?php _e('Paste shortcodes of products anywhere on your website to display these products with their own settings.', 'yabp'); ?>">?</a>)</th><th><?php _e('Thumbnail', 'yabp'); ?></th><th><?php _e('Title', 'yabp'); ?> / <?php _e('Last update', 'yabp'); ?></th><th><?php _e('Price', 'yabp'); ?> / <?php _e('Rating', 'yabp'); ?></th><th><?php _e('Options', 'yabp'); ?></th></tr></tfoot>
    </table>        
    <?php
    echo '<br />'.$nav;

}

function yabp_itemlist_init() {
    global $wpdb, $table_name_yabp, $table_name_yabp_items;

    if (is_admin()) {
        if (isset($_GET['page']) && $_GET['page'] == "yabp-itemlist" && isset($_GET['action']) && $_GET['action'] == 'update_item' && isset($_GET['entry_id']) && is_numeric($_GET['entry_id']) && yabp_entry_value_via_entry_id($_GET['entry_id'], 'entry_bolid')) {
            $entry_id = (int) $_GET['entry_id'];            
            yabp_item_update_via_entry_id($entry_id, true); //forced update           

            if (isset($_GET['p']) && is_numeric($_GET['p'])) { $p = $_GET['p']; }
            else { $p = 1; }
            
            wp_redirect($_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$p.'#yabp-'.$entry_id);        
            die('Done');
        }    
        elseif (isset($_GET['page']) && $_GET['page'] == "yabp-itemlist" && isset($_GET['action']) && $_GET['action'] == 'delete_item' && isset($_GET['entry_id']) && is_numeric($_GET['entry_id']) && yabp_entry_value_via_entry_id($_GET['entry_id'], 'entry_bolid')) {
            $entry_id = (int) $_GET['entry_id'];
            
            $oldvalue = yabp_entry_value_via_entry_id($entry_id, 'entry_updateinterval');
            
            $wpdb->query("DELETE FROM `".$table_name_yabp."` WHERE entry_id = '".$entry_id."'");
            $wpdb->query("DELETE FROM `".$table_name_yabp_items."` WHERE entry_id = '".$entry_id."'");            

            if (isset($_GET['p']) && is_numeric($_GET['p'])) { 
                if (yabp_page_exists_itemlist($_GET['p'])) { $p = "&p=".$_GET['p']; }
                else { $p = "&p=".($_GET['p']-1); }
            }
            else { $p = ""; }
            
            wp_redirect($_SERVER['PHP_SELF'].'?page=yabp-itemlist'.$p);        
            die('Done');
        }    
        elseif (isset($_GET['page']) && $_GET['page'] == "yabp-itemlist" && isset($_GET['do']) && $_GET['do'] == 'entry_setup' && isset($_GET['action']) && ($_GET['action'] == 'entry_showthumb' || $_GET['action'] == 'entry_showprice' || $_GET['action'] == 'entry_showlistprice' || $_GET['action'] == 'entry_showtitle' || $_GET['action'] == 'entry_showsubtitle' || $_GET['action'] == 'entry_showavailability' || $_GET['action'] == 'entry_showrating' || $_GET['action'] == 'entry_showbutton' || $_GET['action'] == 'entry_putincart' || $_GET['action'] == 'entry_recordimpressions' || $_GET['action'] == 'entry_openinnewtab' || $_GET['action'] == 'entry_imgontop' || $_GET['action'] == 'entry_country' || $_GET['action'] == 'entry_showfreeshipping') && isset($_GET['value']) && ($_GET['value'] == 'true' || $_GET['value'] == 'false' || (is_numeric($_GET['value']) && ($_GET['value'] == 1 || $_GET['value'] == 2))) && isset($_GET['entry_id']) && is_numeric($_GET['entry_id']) && yabp_entry_value_via_entry_id($_GET['entry_id'], 'entry_bolid')) {
            $entry_id = (int) $_GET['entry_id'];            
            $column = $_GET['action'];            
                        
            if (is_numeric($_GET['value'])) { $value = $_GET['value']; }
            elseif ($_GET['value'] == 'true') { $value = 1; }
            else { $value = 0; }
            $wpdb->query("UPDATE `".$table_name_yabp."` SET ".esc_sql($column)." = '".esc_sql($value)."' WHERE entry_id = '".esc_sql($entry_id)."'");                        
            
            if ($column == 'entry_country') { yabp_item_update_via_entry_id($entry_id); }
            
            if (isset($_GET['p']) && is_numeric($_GET['p'])) { $p = $_GET['p']; }
            else { $p = 1; }
            
            wp_redirect($_SERVER['PHP_SELF'].'?page=yabp-itemlist&p='.$p.'#yabp-'.$entry_id);        
            die('Done');
        }    
        elseif (isset($_GET['page']) && $_GET['page'] == "yabp-itemlist" && isset($_GET['action']) && $_GET['action'] == 'edititemupdateinterval') {
            $getid = str_replace("update_interval-","",$_POST['element_id']);
            $entry_id = (int) $getid;
            if (!yabp_entry_value_via_entry_id($entry_id, 'entry_bolid')) { die(__('Invalid product', 'yabp')); }            
            $updateinterval = trim($_POST['update_value']);            
            if ($updateinterval == "") { die(yabp_format_updateinterval(yabp_entry_value_via_entry_id($entry_id, 'entry_updateinterval'))); }            
            $updateinterval_formated = yabp_format_updateinterval($updateinterval, true);            
            
            $oldvalue = yabp_entry_value_via_entry_id($entry_id, 'entry_updateinterval');
            $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_updateinterval = '".esc_sql($updateinterval_formated)."' WHERE entry_id = '".esc_sql($entry_id)."'");
            
            //obsolete? all crons should run at any time
            //if (yabp_cron_updateinterval_check_number($oldvalue) < 1) { yabp_cron_handle_eventstatus($oldvalue, false); }
            //elseif (yabp_cron_updateinterval_check_number($updateinterval_formated) == 1) { yabp_cron_handle_eventstatus($updateinterval_formated, true); }

            die(trim($updateinterval));
        }    
        elseif (isset($_GET['page']) && $_GET['page'] == "yabp-itemlist" && isset($_GET['action']) && $_GET['action'] == 'edititemthumbsize') {
            $getid = str_replace("thumb_size-","",$_POST['element_id']);
            $entry_id = (int) $getid;
            if (!yabp_entry_value_via_entry_id($entry_id, 'entry_bolid')) { die(__('Invalid product', 'yabp')); }
            $thumb_size = trim($_POST['update_value']);            
            if ($thumb_size == "") { die(yabp_format_thumbsize(yabp_entry_value_via_entry_id($entry_id, 'entry_thumb'))); }            
            $thumb_size_formated = yabp_format_thumbsize($thumb_size, true);            
            $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_thumb = '".esc_sql($thumb_size_formated)."' WHERE entry_id = '".esc_sql($entry_id)."'");
            die(trim($thumb_size));
        }    
        elseif (isset($_GET['page']) && $_GET['page'] == "yabp-itemlist" && isset($_GET['action']) && $_GET['action'] == 'edititembuttontype') {
            $getid = str_replace("button_type-","",$_POST['element_id']);
            $entry_id = (int) $getid;
            if (!yabp_entry_value_via_entry_id($entry_id, 'entry_bolid')) { die(__('Invalid product', 'yabp')); }            
            $buttontype = trim($_POST['update_value']);            
            if ($buttontype == "") { die(yabp_format_buttontype(yabp_entry_value_via_entry_id($entry_id, 'entry_buttontype'))); }            
            $buttontype_formated = yabp_format_buttontype($buttontype, true);                        
            $wpdb->query("UPDATE `".$table_name_yabp."` SET entry_buttontype = '".esc_sql($buttontype_formated)."' WHERE entry_id = '".esc_sql($entry_id)."'");
            die(trim($buttontype));
        }    
    }
}

add_action('init', 'yabp_itemlist_init');

function yabp_productmanager() {
    global $wpdb, $table_name_yabp, $table_name_yabp_items, $yabp_productmanager_count;
    
    if (!get_option('yabp_productmanager_count')) { $perpage = $yabp_productmanager_count; }
    else { $perpage = get_option('yabp_productmanager_count'); }

    ?>
    <div class="wrap">
    <h2>Yet Another bol.com Plugin</h2>
    <h3><?php _e('Product manager', 'yabp'); ?></h3>        
    <h4><?php _e('Expired products', 'yabp'); ?>: <?php echo yabp_expired_items_count(); ?> (<a href="#" title="<?php _e('Expired products are products that are not available anymore. On this page, you can replace them easily.', 'yabp'); ?>" style="text-decoration: none;">?</a>) - <?php _e('Email notification', 'yabp'); ?>: <?php if (get_option('yabp_productmanager_expired_products_notification') == 1) { ?><span style="color: green; font-weight: bold;"><?php _e('ON', 'yabp'); ?></span><?php } else { ?><span style="color: red; font-weight: bold;"><?php _e('OFF', 'yabp'); ?></span><?php } ?> (<a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp#yabp_productmanager_expired_products_notification"; ?>" style="text-decoration: none"><?php _e('edit', 'yabp'); ?></a>)</h4>        
    <?php        
    
    $_page = isset($_GET['p']) && intval($_GET['p']) != '' ? $_GET['p'] : 1;    
    $gettotal = $wpdb->get_row("SELECT COUNT(entry_id) FROM `".$table_name_yabp_items."` WHERE item_price = '0'",ARRAY_N);
    $totalpage = ceil( $gettotal[0] / (float) $perpage);
    $limit = ( ($_page-1) * $perpage).','.$perpage;
    
    if ($gettotal[0] == 0) {
        echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p>'.__('No products can be found.', 'yabp').'</p></div>'."\n";
        return;
    }
            
    if ($_page > $totalpage) {
        echo '<div style="font-weight: bold; margin-bottom:10px; padding:5px; background:#FFBDB0; border:1px solid #ccc;"><p>'.sprintf(__('Invalid page. <a href="%s">Click here</a> to go back.', 'yabp'), $_SERVER['PHP_SELF'].'?page=yabp-itemlist').'</p></div>';
        return;
    }

    $nav = yabp_pagelinks($_SERVER['PHP_SELF'].'?page=yabp-productmanager&amp;p=', 10, $totalpage, $_page);    
    $items_entries = $wpdb->get_results("SELECT entry_id FROM `".$table_name_yabp_items."` WHERE item_price = '0' LIMIT ".$limit);
    
    ?>
    <script type="text/javascript">
        function sure() {
            var ask = confirm('<?php _e('Are you sure?', 'yabp'); ?>');
            if (ask == true) { return true; }
            else { return false; }
        }        
    </script>
    <table class="widefat comments fixed" cellspacing="0">
        <thead><tr><th># / <?php _e('Shortcode', 'yabp'); ?> (<a href="#" title="<?php _e('Paste shortcodes of products anywhere on your website to display these products with their own settings.', 'yabp'); ?>">?</a>)</th><th><?php _e('Thumbnail', 'yabp'); ?></th><th><?php _e('Title', 'yabp'); ?> / <?php _e('Last update', 'yabp'); ?></th><th><?php _e('Options', 'yabp'); ?></th></tr></thead>
        <tbody>
    <?php
        $i = 0;
        foreach ($items_entries as $item_entry) {            
            $item = $wpdb->get_row("SELECT * FROM `".$table_name_yabp_items."` WHERE entry_id = '".esc_sql($item_entry->entry_id)."'");            
            $i++;
            ?>
            <tr>
                <td<?php if ($i != count($items_entries)) { ?> style="border-bottom: 1px solid grey;"<?php } ?>><a name="yabp-<?php echo $item->entry_id; ?>"></a><strong><?php echo ((($_page-1) * $perpage) + $i); ?></strong><br /><br /><input value="<?php echo yabp_format_shortcode($item->entry_id); ?>" size="<?php echo strlen(yabp_format_shortcode($item->entry_id)); ?>" onClick="this.setSelectionRange(0, this.value.length)" /></td>
                <td<?php if ($i != count($items_entries)) { ?> style="border-bottom: 1px solid grey;"<?php } ?>><a href="<?php echo $item->item_externalurl; ?>"><img alt="<?php echo $item->item_title; ?>" title="<?php echo $item->item_title; ?>" src="<?php echo $item->item_mthumb; ?>" /></a></td>
                <td<?php if ($i != count($items_entries)) { ?> style="border-bottom: 1px solid grey;"<?php } ?>><a href="<?php echo $item->item_externalurl; ?>"><?php echo $item->item_title; ?></a><br /><br /><?php echo yabp_format_time($item->time); ?></td>
                <td<?php if ($i != count($items_entries)) { ?> style="border-bottom: 1px solid grey;"<?php } ?>><a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-add-item&amp;action=replace_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" onclick="return sure()" title="<?php _e('Replace this item with an item from the bol.com Catalog', 'yabp'); ?>"><?php _e('Replace', 'yabp'); ?></a><br /><br />
                <a href="<?php echo $_SERVER['PHP_SELF']."?page=yabp-itemlist&amp;action=delete_item&amp;entry_id=".$item->entry_id."&amp;p=".$_page; ?>" onclick="return sure()" title="<?php _e('Delete this item', 'yabp'); ?>"><?php _e('Delete', 'yabp'); ?></a></td>
            </tr>
        <?php 
        }        

        if (empty($items_entries)) { ?><tr><td></td><td><?php _e('No products found.', 'yabp'); ?></td></tr><?php }
        ?>
        </tbody>
        <tfoot><tr><th># / <?php _e('Shortcode', 'yabp'); ?> (<a href="#" title="<?php _e('Paste shortcodes of products anywhere on your website to display these products with their own settings.', 'yabp'); ?>">?</a>)</th><th><?php _e('Thumbnail', 'yabp'); ?></th><th><?php _e('Title', 'yabp'); ?> / <?php _e('Last update', 'yabp'); ?></th><th><?php _e('Options', 'yabp'); ?></th></tr></tfoot>
    </table>        
    <?php
    echo '<br />'.$nav;
}

function yabp_shortcodegenerator() {
    global $yabp_shortcode_default_imgwidth, $yabp_shortcode_default_imgcolumnwidth, $yabp_shortcode_default_infocolumnwidth, $yabp_item_shortcode_format, $yabp_item_shortcode_imgwidth_limit, $yabp_item_shortcode_imgcolumnwidth_limit, $yabp_item_shortcode_infocolumnwidth_limit;

    if (isset($_POST['yabp_shortcodegenerator_submit']) && ((empty($_POST['yabp_shortcodegenerator_productid']) || !is_numeric($_POST['yabp_shortcodegenerator_productid']) || $_POST['yabp_shortcodegenerator_productid'] < 1))) {
        $error = true;
        unset($_POST['yabp_shortcodegenerator_productid']);
    }    
    elseif (isset($_POST['yabp_shortcodegenerator_submit']) && isset($_POST['yabp_shortcodegenerator_productid'])) { 
        $entry_id = $_POST['yabp_shortcodegenerator_productid'];
        $shortcode = str_replace("%entry_id%", $entry_id."%scadd%", $yabp_item_shortcode_format);
        
        if (isset($_POST['yabp_shortcodegenerator_subid']) && !empty($_POST['yabp_shortcodegenerator_subid'])) { $subid = ' subid="'.trim($_POST['yabp_shortcodegenerator_subid']).'"'; }
        else { $subid = ''; unset($_POST['yabp_shortcodegenerator_subid']); }
        if (isset($_POST['yabp_shortcodegenerator_imgwidth']) && is_numeric($_POST['yabp_shortcodegenerator_imgwidth']) && $_POST['yabp_shortcodegenerator_imgwidth'] > 0 && ($_POST['yabp_shortcodegenerator_imgwidth'] < $yabp_item_shortcode_imgwidth_limit)) { $imgwidth = ' imgwidth="'.$_POST['yabp_shortcodegenerator_imgwidth'].'"'; }
        else { $imgwidth = ''; unset($_POST['yabp_shortcodegenerator_imgwidth']); }
        if (isset($_POST['yabp_shortcodegenerator_imgcolumnwidth']) && is_numeric($_POST['yabp_shortcodegenerator_imgcolumnwidth']) && $_POST['yabp_shortcodegenerator_imgcolumnwidth'] > 0 && ($_POST['yabp_shortcodegenerator_imgcolumnwidth'] < $yabp_item_shortcode_imgcolumnwidth_limit)) { $imgcolumnwidth = ' imgcolumnwidth="'.$_POST['yabp_shortcodegenerator_imgcolumnwidth'].'"'; }
        else { $imgcolumnwidth = ''; unset($_POST['yabp_shortcodegenerator_imgcolumnwidth']); }
        if (isset($_POST['yabp_shortcodegenerator_infocolumnwidth']) && is_numeric($_POST['yabp_shortcodegenerator_infocolumnwidth']) && $_POST['yabp_shortcodegenerator_infocolumnwidth'] > 0 && ($_POST['yabp_shortcodegenerator_infocolumnwidth'] < $yabp_item_shortcode_infocolumnwidth_limit)) { $infocolumnwidth = ' infocolumnwidth="'.$_POST['yabp_shortcodegenerator_infocolumnwidth'].'"'; }
        else { $infocolumnwidth = ''; unset($_POST['yabp_shortcodegenerator_infocolumnwidth']); }
        
        if ($imgcolumnwidth != '' && $infocolumnwidth != '' && ($_POST['yabp_shortcodegenerator_imgcolumnwidth'] + $_POST['yabp_shortcodegenerator_infocolumnwidth']) != 100) {
            $imgcolumnwidth = ''; unset($_POST['yabp_shortcodegenerator_imgcolumnwidth']);
            $infocolumnwidth = ''; unset($_POST['yabp_shortcodegenerator_infocolumnwidth']);
        }
        
        $shortcode = str_replace('%scadd%', $subid.$imgwidth.$imgcolumnwidth.$infocolumnwidth, $shortcode);
        
        $error = false;
    }
    
    $autosetup = false;
    if (!isset($_POST['yabp_shortcodegenerator_submit']) && isset($_GET['entry_id']) && is_numeric($_GET['entry_id'])) {
        $_POST['yabp_shortcodegenerator_productid'] = $_GET['entry_id'];        
        $autosetup = true;       
    }
    ?>
    <div class="wrap">
    <h2>Yet Another bol.com Plugin</h2>
    <h3><?php _e('Shortcode generator', 'yabp'); ?></h3>        
    <h4><?php _e('Set up your shortcodes easily', 'yabp'); ?></h4>
    <div style="padding:10px; background:#fff; border:1px solid #ccc;">
    <p><?php if ($autosetup) { _e('The product ID has been set up automatically. You can now set up your shortcode to your needs.', 'yabp'); } else { _e('Please paste your product ID in the first field below. Then you can set up your shortcode to your needs.', 'yabp'); } ?></p>
    <form method="post">
        <?php _e('Product ID', 'yabp'); ?>: <input type="text" size="20" value="<?php if (isset($_POST['yabp_shortcodegenerator_productid']) && is_numeric($_POST['yabp_shortcodegenerator_productid']) && $_POST['yabp_shortcodegenerator_productid'] > 0) { echo $_POST['yabp_shortcodegenerator_productid']; } ?>" name="yabp_shortcodegenerator_productid" /> (<?php printf(__('product ID is \'x\' in your shortcode: %1$s', 'yabp'), str_replace("%entry_id%", "x", $yabp_item_shortcode_format)); ?>)<br />
        <?php _e('SubId', 'yabp'); ?>: <input type="text" size="30" value="<?php if (isset($_POST['yabp_shortcodegenerator_subid'])) { echo $_POST['yabp_shortcodegenerator_subid']; } ?>" name="yabp_shortcodegenerator_subid" /><br />
        <?php _e('Image width', 'yabp'); ?>: <input type="text" size="3" maxlength="3" value="<?php if (isset($_POST['yabp_shortcodegenerator_imgwidth'])) { echo $_POST['yabp_shortcodegenerator_imgwidth']; } ?>" name="yabp_shortcodegenerator_imgwidth" />% (<?php _e('default', 'yabp'); ?>: <?php echo $yabp_shortcode_default_imgwidth; ?>)<br />
        <?php _e('Image column width', 'yabp'); ?>: <input type="text" size="3" maxlength="3" value="<?php if (isset($_POST['yabp_shortcodegenerator_imgcolumnwidth'])) { echo $_POST['yabp_shortcodegenerator_imgcolumnwidth']; } ?>" name="yabp_shortcodegenerator_imgcolumnwidth" />% (<?php _e('default', 'yabp'); ?>: <?php echo $yabp_shortcode_default_imgcolumnwidth; ?>)<br />
        <?php _e('Info column width', 'yabp'); ?>: <input type="text" size="3" maxlength="3" value="<?php if (isset($_POST['yabp_shortcodegenerator_infocolumnwidth'])) { echo $_POST['yabp_shortcodegenerator_infocolumnwidth']; } ?>" name="yabp_shortcodegenerator_infocolumnwidth" />% (<?php _e('default', 'yabp'); ?>: <?php echo $yabp_shortcode_default_infocolumnwidth; ?>)<br /><br />
        <span style="font-style: italic;"><?php _e('Note: the image and info columns show the product image and information. These columns are positioned next to eachother if this has been set up at the products or deals list. When you set up the image width, you just adjust its width within its own column. Using the two fields above, you can override the default widths of the image and info columns. Both column widths together should be 100% at all times.', 'yabp'); ?></span>
        
        <p class="submit">
            <input name="yabp_shortcodegenerator_submit" type="submit" value="<?php _e('Generate', 'yabp'); ?>" class="button-primary" />
        </p>
        <?php 
        if (isset($error) && $error) {
            ?>
            <p style="color: #FF0000"><?php _e('Please provide a valid product ID.', 'yabp'); ?></p>
            <?php
        }
        ?>
    </form>
    </div>
    <?php
        if (isset($_POST['yabp_shortcodegenerator_submit']) && !$error) {            
    ?>
    <div style="padding:10px; background:#fff; border:1px solid #ccc; margin-top: 20px;">
        <?php _e('Generated shortcode', 'yabp'); ?>:<br />
        <input type="text" size="<?php echo strlen($shortcode); ?>" onClick="this.setSelectionRange(0, this.value.length)" value="<?php echo htmlspecialchars($shortcode); ?>" name="yabp_shortcodegenerator_output" />
    </div>
    <?php
        }
}

add_shortcode('yabp', 'yabp_item_shortcode_execute');

function yabp_item_shortcode_execute($atts, $content = '') {    
    global $yabp_bolcom_buy_button, $yabp_bolcom_buy_button_alt, $yabp_bolcom_view_button, $yabp_bolcom_view_button_alt, $yabp_impression_imglink_prefix, $yabp_partnerlink_prefix, $yabp_bolcom_putincart_link, $yabp_item_freeshipping_limit, $yabp_item_shortcode_imgwidth_limit, $yabp_item_shortcode_imgcolumnwidth_limit, $yabp_item_shortcode_infocolumnwidth_limit;
    $entry_id = $atts[0];
    
    if (isset($atts['subid'])) { $subid = urlencode($atts['subid']); }
    else { $subid = false; }
    
    if (isset($atts['imgwidth'])) { $imgwidth = (int) $atts['imgwidth']; }
    else { $imgwidth = false; }
    
    if (isset($atts['imgcolumnwidth']) && yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop') < 1) { $imgcolumnwidth = (int) $atts['imgcolumnwidth']; }
    else { $imgcolumnwidth = false; }

    if (isset($atts['infocolumnwidth']) && yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop') < 1) { $infocolumnwidth = (int) $atts['infocolumnwidth']; }
    else { $infocolumnwidth = false; }

    if ($imgcolumnwidth && $infocolumnwidth && ($imgcolumnwidth+$infocolumnwidth) > 100) { $imgcolumnwidth = false; $infocolumnwidth = false; }
        
    if (!yabp_entry_value_via_entry_id($entry_id, 'entry_updateinterval')) { $output = ''; }
    else {
        $output = '<div class="yabp_item_wrapper">';    
        $title = htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_title'));
    
        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')) { 
            $thumburl = htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,yabp_entry_value_via_entry_id($entry_id, 'entry_thumb', true)));
            $output .= '<div class="yabp_item_img'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.($imgcolumnwidth&&$imgcolumnwidth>0&&$imgcolumnwidth<=$yabp_item_shortcode_imgcolumnwidth_limit?' style="max-width: '.$imgcolumnwidth.'%;"':'').'><img alt="'.$title.'" title="'.$title.'" src="'.$thumburl.'"'.($imgwidth&&$imgwidth>0&&$imgwidth<=$yabp_item_shortcode_imgwidth_limit?' style="width: '.$imgwidth.'%;"':'').' /></div>';
        }
        $output .= '<div class="yabp_item_info_left'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.($infocolumnwidth&&$infocolumnwidth>0&&$infocolumnwidth<=$yabp_item_shortcode_infocolumnwidth_limit?' style="max-width: '.$infocolumnwidth.'%;"':'').'>';    
    
        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showtitle')) {
            $output .= '<span class="yabp_item_title'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_title_fontsize')||get_option('yabp_styling_item_title_fontcolour')?' style="'.(get_option('yabp_styling_item_title_fontsize')?'font-size: '.get_option('yabp_styling_item_title_fontsize').'px;':'').(get_option('yabp_styling_item_title_fontcolour')?'color: #'.get_option('yabp_styling_item_title_fontcolour').';':'').'"':'').'>'.$title.'</span>';
        }
        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showsubtitle') && yabp_item_value_via_column_name($entry_id,'item_subtitle') != "") {
            $subtitle = htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_subtitle'));
            $output .= '<br /><span class="yabp_item_subtitle'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_subtitle_fontsize')||get_option('yabp_styling_item_subtitle_fontcolour')?' style="'.(get_option('yabp_styling_item_subtitle_fontsize')?'font-size: '.get_option('yabp_styling_item_subtitle_fontsize').'px;':'').(get_option('yabp_styling_item_subtitle_fontcolour')?'color: #'.get_option('yabp_styling_item_subtitle_fontcolour').';':'').'"':'').'>'.$subtitle.'</span>';
        }

        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showrating')) {
            $ratingspan = htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_ratingspan'));
            if (empty($ratingspan)) { $ratingspan = __('Not rated yet', 'yabp'); }
            $output .= '<br /><span class="yabp_item_rating'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'">'.$ratingspan.'</span>';
        }

        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showprice')) {
            $price = yabp_format_price(yabp_item_value_via_column_name($entry_id,'item_price'));
            if (yabp_item_value_via_column_name($entry_id,'item_price') > 0) {            
                if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showlistprice') && yabp_item_value_via_column_name($entry_id,'item_listprice') > 0) {
                    $listprice = yabp_format_price(yabp_item_value_via_column_name($entry_id,'item_listprice'));
            
                    $output .= '<br /><span class="yabp_item_listprice'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_listprice_fontsize')||get_option('yabp_styling_item_listprice_fontcolour')?' style="'.(get_option('yabp_styling_item_listprice_fontsize')?'font-size: '.get_option('yabp_styling_item_listprice_fontsize').'px;':'').(get_option('yabp_styling_item_listprice_fontcolour')?'color: #'.get_option('yabp_styling_item_listprice_fontcolour').';':'').'"':'').'>'.$listprice.'</span>';
                    $output .= '<span class="yabp_item_price'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_price_fontsize')||get_option('yabp_styling_item_price_fontcolour')?' style="'.(get_option('yabp_styling_item_price_fontsize')?'font-size: '.get_option('yabp_styling_item_price_fontsize').'px;':'').(get_option('yabp_styling_item_price_fontcolour')?'color: #'.get_option('yabp_styling_item_price_fontcolour').';':'').'"':'').'>'.$price.'</span>';        
                }
                else {
                    $output .= '<br /><span class="yabp_item_price'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_price_fontsize')||get_option('yabp_styling_item_price_fontcolour')?' style="'.(get_option('yabp_styling_item_price_fontsize')?'font-size: '.get_option('yabp_styling_item_price_fontsize').'px;':'').(get_option('yabp_styling_item_price_fontcolour')?'color: #'.get_option('yabp_styling_item_price_fontcolour').';':'').'"':'').'>'.$price.'</span>';
                }
            }
        }
        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showavailability')) {
            $availability = htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_availability'));
            if (empty($availability)) { $availability = __('Possibly not available', 'yabp'); }            
            $output .= '<br /><span class="yabp_item_availability'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_availability_fontsize')||get_option('yabp_styling_item_availability_fontcolour')?' style="'.(get_option('yabp_styling_item_availability_fontsize')?'font-size: '.get_option('yabp_styling_item_availability_fontsize').'px;':'').(get_option('yabp_styling_item_availability_fontcolour')?'color: #'.get_option('yabp_styling_item_availability_fontcolour').';':'').'"':'').'>'.$availability.'</span>';
        }

        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showfreeshipping') && yabp_item_value_via_column_name($entry_id,'item_price') >= $yabp_item_freeshipping_limit) {
            $freeshipping_text = get_option('yabp_item_freeshipping_text');            
            $output .= '<br /><span class="yabp_item_freeshipping'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"'.(get_option('yabp_styling_item_freeshipping_fontsize')||get_option('yabp_styling_item_freeshipping_fontcolour')?' style="'.(get_option('yabp_styling_item_freeshipping_fontsize')?'font-size: '.get_option('yabp_styling_item_freeshipping_fontsize').'px;':'').(get_option('yabp_styling_item_freeshipping_fontcolour')?'color: #'.get_option('yabp_styling_item_freeshipping_fontcolour').';':'').'"':'').'>'.$freeshipping_text.'</span>';
        }
    
        if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_showbutton')) {
            $view = false;
            $curbuttontype = (int) yabp_entry_value_via_entry_id($entry_id, 'entry_buttontype');
            switch ($curbuttontype) {            
                case 1:
                    $view = true; $buttonurl = $yabp_bolcom_view_button; break;
                case 2:
                    $view = true; $buttonurl = $yabp_bolcom_view_button_alt; break;
                case 3:
                    $buttonurl = $yabp_bolcom_buy_button; break;
                case 4:
                    $buttonurl = $yabp_bolcom_buy_button_alt; break;
            }    
            $output .= '<br /><span class="yabp_item_button'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"><a href="'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_putincart')==1?$yabp_partnerlink_prefix.get_option('yabp_siteid')."&amp;f=BTN&amp;url=".urlencode($yabp_bolcom_putincart_link).yabp_entry_value_via_entry_id($entry_id, 'entry_bolid')."&amp;name=".urlencode(strtolower(yabp_item_title_via_entry_id($entry_id))):str_replace("&amp;f=TXL", "&amp;f=BTN", htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_afflink')))).($subid?'&amp;subid='.$subid:'').'" rel="nofollow'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_openinnewtab')==1?" external":"").'"><img alt="'.($view?__('Click to view this product at bol.com', 'yabp'):__('Click to buy this product at bol.com', 'yabp')).'" title="'.($view?__('Click to view this product at bol.com', 'yabp'):__('Click to buy this product at bol.com', 'yabp')).'" src="'.$buttonurl.'" /></a></span>';
        
            if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_recordimpressions')) { $output .= '<img src="'.$yabp_impression_imglink_prefix.get_option('yabp_siteid').'&amp;t=url&amp;f=BTN&amp;name='.urlencode(yabp_item_value_via_column_name($entry_id,'item_title')).'" width="1" height="1" />'; }
        }
        else {
            $output .= '<br /><span class="yabp_item_textlink'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_showthumb')==1&&yabp_entry_boolean_via_entry_id($entry_id, 'entry_imgontop')==1?"_imgontop":"").'"><a href="'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_putincart')==1?$yabp_partnerlink_prefix.get_option('yabp_siteid')."&amp;f=TXL&amp;url=".urlencode($yabp_bolcom_putincart_link).yabp_entry_value_via_entry_id($entry_id, 'entry_bolid')."&amp;name=".urlencode(strtolower(yabp_item_title_via_entry_id($entry_id))):htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_afflink'))).($subid?"&amp;subid=".$subid:"").'" rel="nofollow'.(yabp_entry_boolean_via_entry_id($entry_id, 'entry_openinnewtab')==1?" external":"").'"><span'.(get_option('yabp_styling_item_textlink_fontsize')||get_option('yabp_styling_item_textlink_fontcolour')?' style="'.(get_option('yabp_styling_item_textlink_fontsize')?'font-size: '.get_option('yabp_styling_item_textlink_fontsize').'px;':'').(get_option('yabp_styling_item_textlink_fontcolour')?'color: #'.get_option('yabp_styling_item_textlink_fontcolour').';':'').'"':'').'>'.get_option('yabp_item_textlink_text').'</span></a></span>';
            if (yabp_entry_boolean_via_entry_id($entry_id, 'entry_recordimpressions')) { $output .= '<img src="'.$yabp_impression_imglink_prefix.get_option('yabp_siteid').'&amp;t=url&amp;f=TXL&amp;name='.urlencode(htmlspecialchars_decode(yabp_item_value_via_column_name($entry_id,'item_title'))).'" width="1" height="1" />'; }
        }    
        $output .= "</div></div>";
    }
    return $output;
}

add_action('wp_enqueue_scripts', 'yabp_register_styles_scripts', 12);

function yabp_register_styles_scripts() {
    wp_register_style('yabp', preg_replace("/^http:/i", "https:", plugin_dir_url( __FILE__ )).'css/yabp.css');
    wp_enqueue_style('yabp');
    
    wp_register_script('yabp', preg_replace("/^http:/i", "https:", plugin_dir_url( __FILE__ )).'js/yabp.js');
    wp_enqueue_script('yabp');
}

function yabp_register_adminscripts_styles() { wp_enqueue_script('yabp_jscolor', preg_replace("/^http:/i", "https:", plugin_dir_url( __FILE__ )).'js/jscolor/jscolor.js'); }

function yabp_register_adminscripts_styles_action() { add_action('admin_enqueue_scripts', 'yabp_register_adminscripts_styles'); }

function yabp_cron_handle_eventstatus($interval) {
    global $yabp_cron_defaulttime;
    if (isset($interval) && is_numeric($interval)) {
        $crontime = strtotime(date("Y-m-d")." ".$yabp_cron_defaulttime);
        wp_schedule_event($crontime, yabp_format_updateinterval($interval, false, true), yabp_format_updateinterval($interval, false, false, true));
        return true;
    }
    else { return false; }
}

add_action('yabp_cron_event_hourly', 'yabp_cron_event_hourly_do');
add_action('yabp_cron_event_twicedaily', 'yabp_cron_event_twicedaily_do');
add_action('yabp_cron_event_daily', 'yabp_cron_event_daily_do');

function yabp_cron_event_hourly_do() {
    global $wpdb, $table_name_yabp; 
    $expired_products_array = array();    
    $interval = 1; 
    $yabp_productmanager_expired_products_notification = get_option('yabp_productmanager_expired_products_notification');
    $entries = $wpdb->get_results("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_updateinterval = '".esc_sql($interval)."'");
    foreach ($entries as $entry) {            
        $item_price = yabp_item_update_via_entry_id($entry->entry_id); //update
        if ($yabp_productmanager_expired_products_notification == 1 && $item_price == 0 && yabp_entry_boolean_via_entry_id($entry->entry_id, 'entry_expired_notification_sent', true) != 1) {
            //item is expired, prepare for notification
            array_push($expired_products_array, $entry->entry_id);
        }        
    }
    if (count($expired_products_array) > 0) { yabp_productmanager_send_expired_products_notification(false, $expired_products_array); }    
}

function yabp_cron_event_twicedaily_do() {
    global $wpdb, $table_name_yabp; 
    $expired_products_array = array();
    $interval = 2;   
    $yabp_productmanager_expired_products_notification = get_option('yabp_productmanager_expired_products_notification');
    $entries = $wpdb->get_results("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_updateinterval = '".esc_sql($interval)."'");
    foreach ($entries as $entry) {            
        $item_price = yabp_item_update_via_entry_id($entry->entry_id); //update
        if ($yabp_productmanager_expired_products_notification == 1 && $item_price == 0 && yabp_entry_boolean_via_entry_id($entry->entry_id, 'entry_expired_notification_sent', true) != 1) {
            array_push($expired_products_array, $entry->entry_id);
        }        
    }
    if (count($expired_products_array) > 0) { yabp_productmanager_send_expired_products_notification(false, $expired_products_array); }          
}

function yabp_cron_event_daily_do() {
    global $wpdb, $table_name_yabp; 
    $expired_products_array = array();
    $interval = 3;
    $yabp_productmanager_expired_products_notification = get_option('yabp_productmanager_expired_products_notification');
    $entries = $wpdb->get_results("SELECT entry_id FROM `".$table_name_yabp."` WHERE entry_updateinterval = '".esc_sql($interval)."'");
    foreach ($entries as $entry) {            
        $item_price = yabp_item_update_via_entry_id($entry->entry_id);  //update              
        if ($yabp_productmanager_expired_products_notification == 1 && $item_price == 0 && yabp_entry_boolean_via_entry_id($entry->entry_id, 'entry_expired_notification_sent', true) != 1) {
            array_push($expired_products_array, $entry->entry_id);
        }
    }
    if (count($expired_products_array) > 0) { yabp_productmanager_send_expired_products_notification(false, $expired_products_array); }
}

register_deactivation_hook(__FILE__, 'yabp_cron_handle_deactivate');

function yabp_cron_handle_deactivate() {
    wp_clear_scheduled_hook('yabp_cron_event_hourly');
    wp_clear_scheduled_hook('yabp_cron_event_twicedaily');
    wp_clear_scheduled_hook('yabp_cron_event_daily');
    wp_clear_scheduled_hook('yabp_deals_cron_event_daily');
}
    
?>