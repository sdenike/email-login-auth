<?php
/*
Plugin Name:	Email Login Auth
Plugin URI:	https://systmweb.com
Description:	Enables logging into WordPress with users email address.
Version:	0.5
Author:		Shelby DeNike
Author URI:	https://systmweb.com
License:	GPL2
*/

defined( 'ABSPATH' ) or die();
define( 'EMAIL_LOGIN_AUTH_PLUGIN_FILE', __FILE__ );
require_once 'email-login-auth-options.php';

if ( get_option( 'email-login-auth-email', email_login_auth_get_default('email-login-auth-email') ) ) {
	function email_login_auth( $user, $username, $password ) {
		if ( is_email( $username ) ) {
			$user_by_email = get_user_by( 'email', $username );
			if ( $user_by_email instanceof WP_User ) {
				$user = null;
				$username = $user_by_email->user_login;
			}
			return wp_authenticate_username_password( $user, $username, $password );
		} else if ( get_option( 'email-login-auth-username', email_login_auth_get_default('email-login-auth-username') ) ) {
			if ( $username == 'admin' && ! get_option( 'email-login-auth-username-admin', email_login_auth_get_default('email-login-auth-username-admin') ) ) {
				return new WP_Error( 'invalid_username', sprintf( __( '<strong>ERROR</strong>: Invalid username. <a href="%s">Lost your password?</a>' ), wp_lostpassword_url() ) );
			}
			return wp_authenticate_username_password( $user, $username, $password );
		}
	}
	add_filter( 'authenticate', 'email_login_auth', 20, 3 );

	if ( ! get_option( 'email-login-auth-username', email_login_auth_get_default('email-login-auth-username') ) ) {
		remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
	}

	function email_login_auth_username_label( $translated_text, $untranslated_text, $domain ) {
		if ( $untranslated_text == 'Username' ) {
			remove_filter( current_filter(), __FUNCTION__ );
			if ( get_option( 'email-login-auth-username', email_login_auth_get_default('email-login-auth-username') ) ) {
				$translated_text .= ' / ' . __('E-mail');
			} else {
				$translated_text = __('E-mail');
			}
		}
		return $translated_text;
	}

	function register_email_login_auth_label() {
		add_filter( 'gettext', 'email_login_auth_username_label', 99, 3 );
	}
	add_filter( 'login_init', 'register_email_login_auth_label' );
}

function get_email_login_auth_plugin_name() {
	return 'Email Login Auth';
}
