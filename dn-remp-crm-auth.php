<?php

/**
 * Plugin Name: DN REMP CRM Auth
 * Plugin URI:  https://remp2020.com
 * Description: REMP CRM login, authentification and user data retrieval functions. You need to define <code>DN_REMP_CRM_HOST</code> in your <code>wp-config.php</code> file for this plugin to work correctly and then use included functions in your theme.
 * Version:     1.1.0
 * Author:      Michal Rusina
 * Author URI:  http://michalrusina.sk/
 * License:     MIT
 */

if (!defined('WPINC')) {
	die();
}

register_activation_hook(__FILE__, 'remp_crm_auth_activate');

add_action('init', 'remp_crm_auth_init');
add_action('wp_enqueue_scripts', 'remp_login_form_script');

/**
 * Echo or return simple login form.
 *
 * @since 1.0.0
 *
 * @param bool $echo Wether to return or echo the form HTML
 *
 * @return string Returns the HTML for login form
 */

function remp_login_form($echo = true) {
	$html = '';

	if (defined('DN_REMP_CRM_HOST')) {
		$html = sprintf(
			'
			<form class="remp_login_form" action="%s">
				<input class="remp_login_email" type="email" placeholder="%s">
				<input class="remp_login_password" type="password" placeholder="%s">
				<button class="remp_login_submit" type="submit">%s</button>
			</form>
			',
			DN_REMP_CRM_HOST . '/api/v1/users/login/',
			__('E-mail', 'dn-remp-crm-auth'),
			__('Password', 'dn-remp-crm-auth'),
			__('Login', 'dn-remp-crm-auth')
		);
	}

	/**
	 * Filter login form HTML
	 *
	 * @since 1.0.0
	 *
	 * @param string $html Form HTML
	 *
	 * @return string Form HTML
	 */

	$html = apply_filters('remp_login_form_html', $html);

	if ($echo) {
		echo $html;
	}

	return $html;
}

/**
 * Returns user data.
 *
 * @since 1.0.0
 *
 * @param string $data Wether to return basic "info" or list of current and future "subscriptions".
 *
 * @return array|false|null Returns data, false if not logged in or null if bad input or missing configuration.
 */

function remp_get_user(string $data = 'info') {
	$apis = [
		'info' => '/api/v1/user/info',
		'subscriptions' => '/api/v1/users/subscriptions'
	];

	/**
	 * Filters available CRM APIs
	 *
	 * @since 1.1.0
	 *
	 * @param array $apis Array of APIs' relative paths with leading and without trailing slash
	 */
	$apis = apply_filters('remp_crm_auth_apis', $apis);

	if (!defined('DN_REMP_CRM_HOST')) {
		return [
			'body' => null,
			'error_msg' => __('DN_REMP_CRM_HOST nie je definované', 'dn-remp-crm-auth')
		];
	}

	if (!in_array($data, array_keys($apis))) {
		$valid_apis = implode(',', array_keys($apis));
		return [
			'body' => null,
			'error_msg' => __(
				'remp_get_user(): param $data je neplatný. Platné hodnoty sú ' . $valid_apis,
				'dn-remp-crm-auth'
			)
		];
	}

	$token = remp_get_user_token();

	if ($token === false) {
		return [
			'body' => null,
			'error_msg' => null
		];
	}

	$headers = [
		'Content-Type' => 'application/json',
		'Authorization' => 'Bearer ' . $token
	];

	$response = wp_remote_get(DN_REMP_CRM_HOST . $apis[$data], ['headers' => $headers]);

	switch (wp_remote_retrieve_response_code($response)) {
		case '':
			$error_msg = is_wp_error($response)
				? $response->get_error_message()
				: __('Chyba komnunikácie s CRM', 'dn-remp-crm-auth');
			$body = null;
			break;
		case 200:
			$error_msg = '';
			$body = json_decode($response['body'], true);
			break;
		default:
			$error = json_decode(wp_remote_retrieve_body($response), true);
			$error_msg = $error['message'] ?? __('CRM vrátilo chybu bez bližšieho popisu', 'dn-remp-crm-auth');
			$body = null;
	}

	return [
		'body' => $body,
		'error_msg' => $error_msg
	];
}

/**
 * Returns user token.
 *
 * @since 1.0.0
 *
 * @return string|false Returns user token or false if not logged in.
 */

function remp_get_user_token() {
	if (isset($_COOKIE['n_token'])) {
		return $_COOKIE['n_token'];
	} else {
		return false;
	}
}

/**
 * Localisations loaded & dependencies check
 *
 * @since 1.0.0
 */

function remp_crm_auth_init() {
	load_plugin_textdomain('dn-remp-crm-auth');

	if (current_user_can('activate_plugins') && !defined('DN_REMP_CRM_HOST')) {
		add_action('admin_init', 'remp_crm_auth_deactivate');
		add_action('admin_notices', 'remp_crm_auth_deactivate_notice');
		unset($_GET['activate']);
	}
}

/**
 * Activation hook
 *
 * @since 1.0.0
 */

function remp_crm_auth_activate() {
	if (!function_exists('is_plugin_active_for_network')) {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';
	}
}

/**
 * Deactivate uppon unsuccessful dependency check
 *
 * @since 1.0.0
 */

function remp_crm_auth_deactivate() {
	deactivate_plugins(plugin_basename(__FILE__));
}

/**
 * Adds admin notice when deactivated uppon unsuccessful dependency check
 *
 * @since 1.0.0
 */

function remp_crm_auth_deactivate_notice() {
	printf(
		'<div class="error"><p>%s</p></div>',
		__(
			'The plugin <strong>DN REMP CRM Auth</strong> requires <code>DN_REMP_CRM_HOST</code> constant to be defined in <code>wp-config.php</code>.',
			'dn-remp-crm-auth'
		)
	);
}

/**
 * Adds javascript handling for login form. If not needed, or if you use custom implementation, feel free to remove_action.
 *
 * @since 1.0.0
 */

function remp_login_form_script() {
	wp_register_script('dn-remp-crm-auth', plugin_dir_url(__FILE__) . 'dn-remp-crm-auth.js', ['jquery'], false, true);
	wp_enqueue_script('dn-remp-crm-auth');
}
