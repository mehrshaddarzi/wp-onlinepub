<?php

namespace WP_OnlinePub;

use WP_Online_Pub;

class Ajax {


	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		$list_function = array(
			'check_new_notification_online_pub'
		);

		foreach ( $list_function as $method ) {
			add_action( 'wp_ajax_' . $method, array( $this, $method ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $this, $method ) );
		}

	}

	/**
	 * Show Json and Exit
	 *
	 * @since    1.0.0
	 * @param $array
	 */
	public function json_exit( $array ) {
		wp_send_json( $array );
		exit;
	}


	/**
	 * Check New Notification
	 */
	public function check_new_notification_online_pub() {
		global $wpdb;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			//Create Empty Result
			$result = array(
				'exist' => 'no',
				'title' => '',
				'text'  => '',
				'url'   => ''
			);

			//Setup Data
			$user_id = get_current_user_id();
			$time    = date( "Y-m-d H:i:s", $_REQUEST['time'] );

			//check New Alert Ticket
			$alert = $wpdb->get_row( "SELECT COUNT(*) FROM `z_ticket` WHERE `user_id` = {$user_id} and `sender` = 'admin' and `read_user` =0" );
			if ( null !== $alert ) {
				$result = array(
					'exist' => 'yes',
					'title' => 'پیام جدید',
					'text'  => 'شما یک پیام جدید دارید',
					'url'   => add_query_arg( array( 'order' => $alert['chat_id'] ), get_the_permalink( WP_Online_Pub::$option['user_panel'] ) )
				);
			}

			//Check New Factor
			$factor = $wpdb->get_row( "SELECT * FROM `z_factor` WHERE `user_id` = $user_id AND `date` >= $time ORDER BY `id` DESC LIMIT 1", ARRAY_A );
			if ( null !== $factor ) {
				$result = array(
					'exist' => 'yes',
					'title' => 'فاکتور جدید',
					'text'  => 'شما یک فاکتور دارید',
					'url'   => add_query_arg( array( 'view_factor' => $factor['id'], 'redirect' => 'user', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() )
				);
			}

			//Send Data
			$this->json_exit( $result );
		}
		die();
	}

}