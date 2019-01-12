<?php

namespace WP_OnlinePub;

class Admin_Setting_Api {

	/**
	 * Plugin Option name
	 */
	public static $option_name = 'wp_online_pub_opt';
	public $setting;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin_Setting_Api constructor.
	 */
	public function __construct() {


		/**
		 * Set Admin Setting
		 * @see https://tareq.co/2012/06/wordpress-settings-api-php-class/
		 */
		add_action( 'admin_init', array( $this, 'wedevs_admin_init' ) );
		//add_action( 'admin_menu', array( $this, 'wedevs_admin_menu' ) );


	}

	/**
	 * Register the plugin page
	 */
	public function wedevs_admin_menu() {
		//add_submenu_page( Admin_Page::$admin_page_slug, __( 'تنظیمات', '' ), __( 'تنظیمات', '' ), 'manage_options', 'wp_onlinepub_option', array( $this, 'wedevs_plugin_page' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function wedevs_plugin_page() {

		echo '<div class="wrap">';
		settings_errors();

		$this->setting->show_navigation();
		$this->setting->show_forms();

		echo '</div>';
	}

	/**
	 * Registers settings section and fields
	 */
	public function wedevs_admin_init() {

		$sections = array(
			array(
				'id'    => self::$option_name,
				'title' => __( 'تنظیمات افزونه', 'wedevs' )
			),
			array(
				'id'    => 'wp_online_pub_gravity',
				'title' => __( 'تنظیمات گراویتی فرم', 'wedevs' )
			),
		);

		$fields = array(
			self::$option_name      => array(
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
				),
				array(
					'name'    => 'zarinpal',
					'label'   => __( 'کد مرچنت زرین پال', 'wedevs' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'    => 'user_panel',
					'label'   => __( 'برگه لیست فاکتور کاربران', 'wedevs' ),
					'type'    => 'pages',
					'default' => ''
				),
			),
			'wp_online_pub_gravity' => array(
				array(
					'name'    => 'order',
					'label'   => __( 'فرم ثبت سفارش', 'wedevs' ),
					'type'    => 'select',
					'options' => Gravity_Form::get_forms_list()
				),
				array(
					'name'  => 'title',
					'label' => __( 'شناسه فیلد عنوان', 'wedevs' ),
					'type'  => 'text'
				),
				array(
					'name'  => 'type',
					'label' => __( 'شناسه نوع سفارش', 'wedevs' ),
					'type'  => 'text'
				),
				array(
					'name'  => 'hidden',
					'label' => __( 'شناسه فیلد هایی که باید در گزارش مخفی باشد', 'wedevs' ),
					'desc'  => 'لطفا با کاما جدا کنید مثلا 3,67',
					'type'  => 'text'
				)
			),
		);

		$this->setting = new \WeDevs_Settings_API();

		//set sections and fields
		$this->setting->set_sections( $sections );
		$this->setting->set_fields( $fields );

		//initialize them
		$this->setting->admin_init();
	}


}