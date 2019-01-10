<?php

namespace WP_OnlinePub;

use WP_Online_Pub;

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
	 * Get Factor Detail By id
	 *
	 * @param $id
	 * @return array
	 */
	public static function get_factor( $id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM `z_factor` WHERE `id` = {$id}", ARRAY_A );
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
	 * @param bool $is_notification
	 */
	public static function change_status_order( $order_id, $new_status, $is_notification = false ) {
		global $wpdb;

		//Update in database
		$wpdb->update(
			'z_order',
			array( 'status' => $new_status ),
			array( 'id' => $order_id ),
			array( '%d' ),
			array( '%d' )
		);

		if ( $is_notification === true ) {

			//Get Order Detail
			$order = Helper::get_order( $order_id );

			//Send Sms
			$arg         = array( "order_id" => $order_id, "new_status" => Helper::show_status( $new_status ), "user_name" => Helper::get_user_full_name( $order['user_id'] ) );
			$user_mobile = Helper::get_user_mobile( $order['user_id'] );
			if ( $user_mobile != "" ) {
				WP_Online_Pub::send_sms( $user_mobile, '', 'send_to_user_at_change_status', $arg );
			}

			//Send Email
			$user_mail = Helper::get_user_email( $order['user_id'] );
			if ( $user_mail != "" ) {
				$subject = "تغییر وضعیت سفارش به شناسه " . $order_id;
				$content = '<p>';
				$content .= 'کاربر گرامی ';
				$content .= Helper::get_user_full_name( $order['user_id'] );
				$content .= '</p><p>';
				$content .= 'سفارش شما در سامانه نشر آنلاین تغییر وضعیت داده شد.';
				$content .= '</p>';
				$content .= '<p>شناسه سفارش : ' . $order_id . '</p>';
				$content .= '<p>وضعیت جدید : ' . Helper::show_status( $new_status ) . '</p>';

				WP_Online_Pub::send_mail( $user_mail, $subject, $content );
			}
		}
	}

	/**
	 * Price currency
	 */
	public static function currency() {
		return 'تومان';
	}

	/**
	 * Change Status Factor
	 *
	 * @param $factor_id
	 * @param $new_status
	 */
	public static function change_factor_status( $factor_id, $new_status ) {
		global $wpdb;

		//Update in database
		$wpdb->update(
			'z_factor',
			array( 'payment_status' => $new_status ),
			array( 'id' => $factor_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Get Type Factor
	 *
	 * @param int $type
	 * @return string
	 */
	public static function get_type_factor( $type = 1 ) {
		switch ( $type ) {
			case "1":
				return 'پیش فاکتور';
				break;
			case "2":
				return 'اصل';
				break;
		}
	}

	/**
	 * Get Type Factor
	 *
	 * @param int $status
	 * @return string
	 */
	public static function get_status_factor( $status = 1 ) {
		switch ( $status ) {
			case "1":
				return 'پرداخت نشده';
				break;
			case "2":
				return 'پرداخت شده';
				break;
		}
	}


	/**
	 * Get List Factor item
	 *
	 * @param $factor_id
	 * @return array
	 */
	public static function get_factor_items( $factor_id ) {
		global $wpdb;
		$list = array();

		$query = $wpdb->get_results( "SELECT * FROM `z_factor_item` WHERE `factor_id` = $factor_id ORDER BY `id` ASC", ARRAY_A );
		if ( count( $query ) > 0 ) {
			foreach ( $query as $row ) {
				$list[] = array(
					'name'  => $row['item'],
					'price' => $row['price'],
				);
			}
		}

		return $list;
	}
}