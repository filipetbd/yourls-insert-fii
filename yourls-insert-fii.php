<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/**
* Plugin Name: Yourls Insert Fii
* Plugin URI: http://filipetbd.xyz/
* Description: Simplesmente adiciona o formulário público do Yourls em uma página com o shortcode [yourls]. Só funciona em domínios com Yourls instalado no diretório ROOT.
* Version: 1.0
* Author: filipetbd
* Author URI: http://filipetbd.xyz/
**/



function yourls_form_run() {

require_once( $_SERVER['DOCUMENT_ROOT'].'/includes/load-yourls.php' );


// Change this to match the URL of your public interface. Something like: http://your-own-domain-here.com/index.php

global $wp;
$page = home_url( add_query_arg( array(), $wp->request ) );

// $page = $YOURLS_SITE . '/sample-yourls.php' ;

// Part to be executed if FORM has been submitted
if ( isset( $_REQUEST['url'] ) && $_REQUEST['url'] != 'http://' ) {

	// Get parameters -- they will all be sanitized in yourls_add_new_link()
	$url     = $_REQUEST['url'];
	$keyword = isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : '' ;
	$title   = isset( $_REQUEST['title'] ) ?  $_REQUEST['title'] : '' ;
	$text    = isset( $_REQUEST['text'] ) ?  $_REQUEST['text'] : '' ;

	// Create short URL, receive array $return with various information
	$return  = yourls_add_new_link( $url, $keyword, $title );

	$shorturl = isset( $return['shorturl'] ) ? $return['shorturl'] : '';
	$message  = isset( $return['message'] ) ? $return['message'] : '';
	$title    = isset( $return['title'] ) ? $return['title'] : '';
	$status   = isset( $return['status'] ) ? $return['status'] : '';

	// Stop here if bookmarklet with a JSON callback function ("instant" bookmarklets)
	if( isset( $_GET['jsonp'] ) && $_GET['jsonp'] == 'yourls' ) {
		$short = $return['shorturl'] ? $return['shorturl'] : '';
		$message = "Short URL (Ctrl+C to copy)";
		header('Content-type: application/json');
		echo yourls_apply_filter( 'bookmarklet_jsonp', "yourls_callback({'short_url':'$short','message':'$message'});" );

		die();
	}
}

// Insert <head> markup and all CSS & JS files
//yourls_html_head();

// Display title
//echo "<h1>YOURLS - Your Own URL Shortener</h1>\n";

// Display left hand menu
//yourls_html_menu() ;

// Part to be executed if FORM has been submitted
if ( isset( $_REQUEST['url'] ) && $_REQUEST['url'] != 'http://' ) {

	// Display result message of short link creation
	if( isset( $message ) ) {
		echo "<p>$message</p>";
	}

	if( $status == 'success' ) {
		// Include the Copy box and the Quick Share box
		yourls_share_box( $url, $shorturl, $title, $text );

		// Initialize clipboard -- requires js/share.js and js/clipboard.min.js to be properly loaded in the <head>
		echo "<script>init_clipboard();</script>\n";
	}

// Part to be executed when no form has been submitted
} else {

		$site = $page;

		// Display the form
		echo <<<HTML
		<h2>Encurtar URL</h2>
		<form method="post" action="">
		<p><label>URL: <input type="text" class="text" name="url" value="http://" /></label></p>
		<!-- <p><label>Optional custom short URL: $site/<input type="text" class="text" name="keyword" /></label></p>
		<p><label>Optional title: <input type="text" class="text" name="title" /></label></p> -->
		<p><input type="submit" class="button primary" value="Encurtar" /></p>
		</form>
HTML;

}

}
function my_error_notice() {
	if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/includes/load-yourls.php') ) {

		?>
    <div class="error notice is-dismissible">
        <p>O <b>Yourls</b> não foi encontrado nesse servidor >> O plugin Yourls Insert Fii é inútil por aqui.</p>
    </div>
    <?php

}
}
add_action( 'admin_notices', 'my_error_notice' );
function yourls_form_shortcode() {


	if (file_exists($_SERVER['DOCUMENT_ROOT'].'/includes/load-yourls.php')) {
	    yourls_form_run();
	} else {
	    echo "O Yourls não foi encontrado nesse servidor.";
	}
}
add_shortcode('yourls', 'yourls_form_shortcode');
