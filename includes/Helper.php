<?php

namespace WP_OnlinePub;

class Helper {

	/**
	 * Get User Mobile
	 * @param bool $user_id
	 * @return
	 */
	public static function get_user_mobile( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return get_user_meta( $user_id, 'mobile_phone', true );
	}

	/**
	 * Get User email
	 *
	 * @param bool $user_id
	 * @return string
	 */
	public static function get_user_email( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		//setup user data
		$user_info = get_userdata( $user_id );
		return $user_info->user_email;
	}

	/**
	 * Get User Name
	 *
	 * @param bool $user_id
	 * @return string
	 */
	public static function get_user_full_name( $user_id = false ) {
		$user_info = get_userdata( $user_id );

		//check display name
		if ( $user_info->display_name != "" ) {
			return $user_info->display_name;
		}

		//Check First and Last name
		if ( $user_info->first_name != "" ) {
			return $user_info->first_name . " " . $user_info->last_name;
		}

		//return Username
		return $user_info->user_login;
	}

	/**
	 * Check User Exist By id
	 *
	 * @param $user
	 * @return bool
	 * We Don`t Use get_userdata or get_user_by function, because We need only count nor UserData object.
	 */
	public static function user_id_exists( $user ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user ) );
		if ( $count == 1 ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Show Order Status
	 *
	 * @param int $status
	 * @return string
	 */
	public static function show_status( $status = 1 ) {
		switch ( $status ) {
			case "1":
				return 'در حال بررسی اولیه';
				break;
			case "2":
				return 'صدور پیش فاکتور';
				break;
			case "3":
				return 'تایید پرداخت پیش فاکتور';
				break;
			case "4":
				return 'تایید انجام سفارش';
				break;
			case "5":
				return 'در حال انجام کار';
				break;
			case "6":
				return 'ارسال برای بازبینی';
				break;
			case "7":
				return 'صدور فاکتور';
				break;
			case "8":
				return 'تایید واریز';
				break;
			case "9":
				return 'اتمام پروژه';
				break;
		}
	}

	/**
	 * Check Order Id For Custom User
	 *
	 * @param $order_id
	 * @param $user_id
	 * @return bool
	 */
	public static function check_order_for_user( $order_id, $user_id ) {
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `z_order` WHERE `id` = $order_id and `user_id` = $user_id" );
		if ( $count == 1 ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Get Order detail by id
	 *
	 * @param $id
	 * @return
	 */
	public static function get_order( $id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM `z_order` WHERE `id` = {$id}", ARRAY_A );
	}


	/**
	 * Remove Factor
	 *
	 * @param $factor_id
	 */
	public static function remove_factor( $factor_id ) {
		global $wpdb;
		$factor = $wpdb->get_row( "SELECT * FROM `z_factor` WHERE `id` = $factor_id", ARRAY_A );
		if ( null !== $factor ) {
			//Remove all item from this factor
			$wpdb->query( "DELETE FROM `z_factor_item` WHERE `factor_id` = {$factor['id']}" );
		}

		//Remove Factor
		$wpdb->query( "DELETE FROM `z_factor` WHERE `id` = $factor_id" );
	}


	/**
	 * Remove Ticket By Order id
	 *
	 * @param $order_id
	 */
	public static function remove_ticket( $order_id ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM `z_ticket` WHERE `chat_id` = $order_id" );
	}


	/**
	 * Get Number Factor for id
	 *
	 * @param $order_id
	 * @return int
	 */
	public static function get_number_factor_for_order( $order_id ) {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM `z_factor` WHERE `order_id` = $order_id" );
	}


	/**
	 * Change Status order
	 *
	 * @param $order_id
	 * @param $new_status
	 */
	public static function change_status_order( $order_id, $new_status ) {
		global $wpdb;

		$wpdb->update(
			'z_order',
			array( 'status' => $new_status ),
			array( 'id' => $order_id ),
			array( '%d' ),
			array( '%d' )
		);
	}


}