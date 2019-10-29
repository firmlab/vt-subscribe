<?php
/**
 * @package Vertice_Subscribe
 * @version 1.0.0
 */
/*
Plugin Name: Vertice Subscribe
Plugin URI: http://vertice.com/plugins/vt-subscribe/
Description: Create a popup subscribe form and integrate it with mailchimp API.
Author: Firman Adetia Putra
Version: 1.0.0
Author URI: http://vertice.com
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'VT_SUBSCRIBE_VERSION', '1.0.0' );
define( 'VT_SUBSCRIBE__MINIMUM_WP_VERSION', '4.0' );
define( 'VT_SUBSCRIBE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

global $vt_subscribe_version;
$vt_subscribe_version = '1.0';

/**
 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
 * @static
 */
function plugin_activation() {
    if ( version_compare( $GLOBALS['wp_version'], VT_SUBSCRIBE__MINIMUM_WP_VERSION, '<' ) ) {
        load_plugin_textdomain( 'vtsubscribe' );
        
        $message = '<strong>'.sprintf(esc_html__( 'Vertice Subscribe %s requires WordPress %s or higher.' , 'vtsubscribe' ), VT_SUBSCRIBE_VERSION, VT_SUBSCRIBE__MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'vtsubscribe'), 'https://codex.wordpress.org/Upgrading_WordPress');

        wp_die($message);
        exit;
    }

    global $wpdb;
    global $vt_subscribe_version;

    $table_name = $wpdb->prefix . 'vt_subscribe';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email tinytext NOT NULL,
        subscribe_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'vt_subscribe_version', $vt_subscribe_version );
}

/**
 * Removes all connection options
 * @static
 */
function plugin_deactivation( ) {
    
}

function vt_subscribe_register_settings() {
    add_option( 'vt_subscribe_username', '');
    add_option( 'vt_subscribe_api_key', '');
    add_option( 'vt_subscribe_data_center', '');
    add_option( 'vt_subscribe_audience_id', '');
    add_option( 'vt_subscribe_title_popup', '');
    add_option( 'vt_subscribe_description_popup', '');
    register_setting( 'vt_subscribe_options_group', 'vt_subscribe_username' );
    register_setting( 'vt_subscribe_options_group', 'vt_subscribe_description_popup' );
    register_setting( 'vt_subscribe_options_group', 'vt_subscribe_title_popup' );
    register_setting( 'vt_subscribe_options_group', 'vt_subscribe_api_key' );
    register_setting( 'vt_subscribe_options_group', 'vt_subscribe_data_center' );
    register_setting( 'vt_subscribe_options_group', 'vt_subscribe_audience_id' );
}

function vt_subscribe_register_options_page() {
    add_options_page('Vertice Subscribe Setting', 'VT Subscribe', 'manage_options', 'vtsubscribe', 'vt_subscribe_options_page');
}

function vt_subscribe_options_page() {
    ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Vertice Subscribe Setting</h1>
            <hr>
            <form method="post" action="options.php">
                <?php settings_fields( 'vt_subscribe_options_group' ); ?>
                <h4>Text pop up display</h4>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="vt_subscribe_title_popup">Title Popup</label></th>
                        <td>
                            <input class="regular-text" type="text" id="vt_subscribe_title_popup" name="vt_subscribe_title_popup" value="<?php echo get_option('vt_subscribe_title_popup'); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="vt_subscribe_description_popup">Description Popup</label></th>
                        <td>
                            <input class="regular-text" type="text" id="vt_subscribe_description_popup" name="vt_subscribe_description_popup" value="<?php echo get_option('vt_subscribe_description_popup'); ?>" />
                        </td>
                    </tr>
                </table>
                <hr>
                <h4>Setting required informations for Mailchimp API 3.0</h4>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="vt_subscribe_username">Mailchimp Username</label></th>
                        <td>
                            <input class="regular-text" type="text" id="vt_subscribe_username" name="vt_subscribe_username" value="<?php echo get_option('vt_subscribe_username'); ?>" />
                            <p>Can be found on your mailchimp profile setting</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="vt_subscribe_api_key">Mailchimp API Key</label></th>
                        <td>
                            <input class="regular-text" type="text" id="vt_subscribe_api_key" name="vt_subscribe_api_key" value="<?php echo get_option('vt_subscribe_api_key'); ?>" />
                            <p>API Key without <b>-data_center</b> eg: 0192213daf13-us6 | <b>no need -us6</b> <br> Read more about <a href="https://mailchimp.com/help/about-api-keys" target="_blank">API Keys</a></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="vt_subscribe_data_center">Data Center</label></th>
                        <td>
                            <input class="regular-text" type="text" id="vt_subscribe_data_center" name="vt_subscribe_data_center" value="<?php echo get_option('vt_subscribe_data_center'); ?>" />
                            <p>Subdomain URL from your mailchimp account. eg: https://<b>us3</b>.admin.mailchimp.com/ | <b>us3</b> is the data center</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="vt_subscribe_audience_id">Audience ID</label></th>
                        <td>
                            <input class="regular-text" type="text" id="vt_subscribe_audience_id" name="vt_subscribe_audience_id" value="<?php echo get_option('vt_subscribe_audience_id'); ?>" />
                            <p>How to know your <a target="_blank" href="https://mailchimp.com/help/find-audience-id/">Audience ID</a></p>
                        </td>
                    </tr>
                </table>
                <hr>
                <?php  submit_button(); ?>
            </form>
        </div>
    <?php
}

function vtsubscribe_init() {
    // wp_die(wp_get_current_user()->ID);
    if ( ! is_admin() ) { 
        add_action('wp_enqueue_scripts', 'vtsubscribe_enqueue_script');
        add_action( 'wp_footer', 'popup_subscribe', 100 );
    }

    add_action( 'admin_init', 'vt_subscribe_register_settings' );
    add_action('admin_menu', 'vt_subscribe_register_options_page');
}

function vtsubscribe_enqueue_script() {
    wp_register_script( 'vt-subscribe-script', plugins_url( 'vt-subscribe/assets/js/vt-subscribe.js' ), array('jquery'), '1.0.3', true );
    wp_enqueue_script( 'vt-subscribe-script' );

    wp_register_style( 'vt-subscribe-style', plugins_url( 'vt-subscribe/assets/css/vt-subscribe.css' ), array(), '1.0.4', 'all' );
    wp_enqueue_style( 'vt-subscribe-style' );
    $jsData = [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'logged_in' => (wp_get_current_user()->ID > 0) ? 1 : 0,
        'ajax_nonce' => wp_create_nonce('vt_subscribe_nonce')
    ];
    wp_localize_script( 'vt-subscribe-script', 'vtJsData', $jsData );
}



function popup_subscribe() {
    $title = get_option('vt_subscribe_title_popup');
    $desc = get_option('vt_subscribe_description_popup');
    $popup = '<div id="popup-vt-subscribe" class="overlay">
        <div class="popup"> <a class="close popup-vt-subscribe-close" href="javascript:void(0)">&times;</a>
        <div id="dialog" class="window">
            
            <div class="box">
            <div class="newletter-title">
                <h2>' . $title . '</h2>
            </div>
            <div class="box-content newleter-content">
                <label>' . $desc . '</label>
                <div id="frm_subscribe">
                <form name="subscribe" id="vt_subscribe_popup_form">
                    <div>
                    <!-- <span class="required">*</span><span>Email</span>-->
                    <input placeholder="your@email.com" type="email" value="" name="email_field" id="popup-vt-subscribe-email">
                    <div id="popup-vt-subscribe-notification"></div>
                    <button type="submit" disabled id="vt_subscribe_popup_btn" class="button"><span>Submit</span></button> </div>
                </form>
                </div>
                <!-- /#frm_subscribe -->
            </div>
            <!-- /.box-content -->
            </div>
        </div>
        </div>
    </div>';

    echo $popup;
    // return $content.$popup;
    // return 'AAA';
}

function vt_subscribe() {
    check_ajax_referer( 'vt_subscribe_nonce', 'security' );
    
    $email = urldecode($_POST['email']);
    $api_username = get_option('vt_subscribe_username');
    $api_key = get_option('vt_subscribe_api_key');https:
    $url = 'https://' . get_option('vt_subscribe_data_center') . '.api.mailchimp.com/3.0/lists/' . get_option('vt_subscribe_audience_id') . '/members';

    $data = array(
        'email_address' => $email,
        'status' => 'subscribed'
    );

    $response = wp_remote_post( $url, array(
        'body'    => json_encode($data),
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api_username . ':' . $api_key ),
        ),
    ) );

    if( is_wp_error($response) ) {
        echo json_encode(array('status' => 'failed'));
        exit;
    }

    global $wpdb;
    if(
        $wpdb->insert('wp_vt_subscribe', array(
            'email' => $email,
        ))
    ) {
        echo json_encode(array('status' => 'ok'));
    } else {
        echo json_encode(array('status' => 'failed'));
    }
    exit;
}

register_activation_hook( __FILE__, 'plugin_activation' );
register_deactivation_hook( __FILE__, 'plugin_deactivation' );

add_action( 'wp_ajax_vt_subscribe', 'vt_subscribe' );
add_action( 'wp_ajax_nopriv_vt_subscribe', 'vt_subscribe' );

add_action( 'init', 'vtsubscribe_init' );