<?php

add_theme_support( 'post-thumbnails' );
add_theme_support( 'title-tag' );

function plug_disable_emoji() {
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  add_filter( 'tiny_mce_plugins', 'plug_disable_tinymce_emoji' );
}
add_action( 'init', 'plug_disable_emoji', 1 );
function plug_disable_tinymce_emoji( $plugins ) {
  return array_diff( $plugins, array( 'wpemoji' ) );
}


// Style & Scripts
if (!is_admin()) {
	function theme_styles() {
        wp_enqueue_style( 'main', get_template_directory_uri() . '/assets/css/main.css"');
	}
	function theme_js() {
        wp_enqueue_script( 'main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '', true );
        wp_localize_script('main', 'myajax', 
            array(
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('myajax-nonce')
            )
        );         
	}
	add_action( 'wp_enqueue_scripts', 'theme_styles' );
	add_action( 'wp_enqueue_scripts', 'theme_js' );
     
}

if( wp_doing_ajax() ){
    add_action('wp_ajax_like', 'do_like');
    add_action('wp_ajax_nopriv_like', 'do_like');
}

function do_like() {
    if(!isset($_POST['nonce'], $_POST['post_id'])) die();
    $nonce = $_POST['nonce'];
    $post_id = intval($_POST['post_id']);
    $like_type = intval($_POST['like_type']);
    if($post_id <=0) die(); 
    if( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ) die();
    if ( $like_type < 0 ) {
        $like_plus = 0;
        $like_minus = 1;
    } else {
        $like_plus = 1;
        $like_minus = 0;
    }
    global $wpdb;
    $wpdb->insert( 'wp_test_likes', array(
        'post_id'=> $post_id, 
        'like_plus' => $like_plus, 
        'like_minus' => $like_minus,
        'client_ip' => GetVisitorIP()
        )
    );
}


function get_post_likes($post_id) {
    global $wpdb;
    $likes = 0;
    $lp = $wpdb->get_results(sprintf("SELECT like_plus FROM wp_test_likes WHERE post_id=%u AND like_plus > 0", $post_id) );
    $lm = $wpdb->get_results(sprintf("SELECT like_minus FROM wp_test_likes WHERE post_id=%u AND like_minus > 0", $post_id) );
    $likes = count($lp) - count($lm);
    $color = '';
    if ($likes != 0) {
        if ($likes > 0) {
            $color = 'positive';
        } else {
            $color = 'negative';
        }
    }
    return [$likes, $color];
}

function is_my_post_like($post_id) {
    global $wpdb;
    $likes = 0;
    $ml = $wpdb->get_results(sprintf("SELECT like_plus FROM wp_test_likes WHERE post_id=%u AND client_ip='%s'", $post_id, GetVisitorIP()) );
    if(count($ml) != 0) {
        if ($ml[0]->like_plus > 0 ) {
            $likes = 1;
        } else {
            $likes = -1;
        }
    }
    return $likes;
}

add_action( 'deleted_post', 'test_deleted_post' );
function test_deleted_post( $post_id ){
    global $wpdb;
    $wpdb->query(sprintf('DELETE FROM `wp_test_likes` WHERE `post_id`=%u', $post_id));
}

function GetVisitorIP(){
    if (($IP = getenv('HTTP_CLIENT_IP'))===false)
    if (($IP = getenv('HTTP_X_FORWARDED_FOR'))===false)
    if (($IP = getenv('HTTP_X_FORWARDED'))===false)
    if (($IP = getenv('HTTP_FORWARDED_FOR'))===false)
    if (($IP = getenv('HTTP_FORWARDED'))===false)
        $IP = $_SERVER['REMOTE_ADDR'];
    return $IP;
}


?>