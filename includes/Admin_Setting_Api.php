<?php

namespace WP_OnlinePub;

class Admin_Setting_Api {

	/**
	 * Plugin Option name
	 */
	public static $option_name = 'wp_online_pub_opt';

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {


		/**
		 * Set Admin Setting
		 * @see https://tareq.co/2012/06/wordpress-settings-api-php-class/
		 */
		add_action( 'admin_init', array( $this, 'wedevs_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'wedevs_admin_menu' ) );


	}

	/**
	 * Register the plugin page
	 */
	public function wedevs_admin_menu() {
		add_submenu_page( Admin_Page::$admin_page_slug, __( 'تنظیمات', '' ), __( 'تنظیمات', '' ), 'manage_options', 'wp_onlinepub_option', array( $this, 'wedevs_plugin_page' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function wedevs_plugin_page() {
		$settings_api = new \WeDevs_Settings_API();

		echo '<div class="wrap">';
		settings_errors();

		$settings_api->show_navigation();
		$settings_api->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function wedevs_admin_init() {

		$sections = array(
			array(
				'id'    => 'basic',
				'title' => __( 'تنظیمات افزونه', 'wedevs' )
			),
		);

		$fields = array(
			'basic' => array(
				array(
					'name'    => 'modir_mobile',
					'label'   => __( 'شماره همراه مدیر', 'wedevs' ),
					'desc'    => __( 'شماره همراه مدیر برای اطلاع رسانی', 'wedevs' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'  => 'modir_email',
					'label' => __( 'ایمیل مدیر', 'wedevs' ),
					'desc'  => __( 'ایمیل مدیر برای اطلاع رسانی', 'wedevs' ),
					'type'  => 'text'
				),
				array(
					'name'  => 'acc_1',
					'label' => __( 'اطلاعات حساب یک', 'wedevs' ),
					'type'  => 'textarea'
				),
				array(
					'name'  => 'acc_2',
					'label' => __( 'اطلاعات حساب دو', 'wedevs' ),
					'type'  => 'textarea'
				)
			)
		);

		$settings_api = new \WeDevs_Settings_API();

		//set sections and fields
		$settings_api->set_sections( $sections );
		$settings_api->set_fields( $fields );

		//initialize them
		$settings_api->admin_init();
	}


}