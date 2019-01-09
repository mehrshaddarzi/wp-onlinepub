<?php

namespace WP_OnlinePub;

use WP_Online_Pub;

class Gravity_Form {

	/**
	 * Order Form Id
	 */
	public static $order_form_id = 12;

	/**
	 * Custom Field ID for Push to table
	 */
	public static $title = 10;
	public static $order_type = 78;

	/**
	 * Gravity_Form constructor.
	 */
	public function __construct() {

		//Save Order After Push Form
		add_action( 'gform_after_submission_' . self::$order_form_id, array( $this, 'after_submission_order_form' ), 10, 2 );


	}

	/**
	 * Get Entry From Gravity
	 *
	 * @param $entry_id
	 * @return
	 */
	public static function get_entry( $entry_id ) {
		return \GFAPI::get_entry( $entry_id );
	}

	/**
	 * Save Form To Order Tbl
	 *
	 * @param $entry
	 * @param $form
	 */
	public function after_submission_order_form( $entry, $form ) {
		global $wpdb;

		if ( isset( $entry['created_by'] ) and is_numeric( $entry['created_by'] ) and Helper::user_id_exists( $entry['created_by'] ) === true ) {

			//Save To Database
			$wpdb->insert(
				'z_order',
				array(
					'user_id'  => $entry['created_by'],
					'date'     => current_time( 'mysql' ),
					'entry_id' => $entry['id'],
					'form_id'  => $entry['form_id'],
					'title'    => $entry[ self::$title ],
					'status'   => 1,
				)
			);
			$order_id = $wpdb->insert_id;

			//Sms To Admin
			$arg = array( "order_id" => $order_id, "user_name" => Helper::get_user_full_name( $entry['created_by'] ) );
			WP_Online_Pub::send_sms( 'admin', '', "send_to_admin_at_new_order", $arg );

			//Sms To User
			$user_mobile = Helper::get_user_mobile( $entry['created_by'] );
			if ( $user_mobile != "" ) {
				WP_Online_Pub::send_sms( $user_mobile, '', 'send_to_user_at_new_order', $arg );
			}

			//Email to User
			$user_mail = Helper::get_user_email( $entry['created_by'] );
			$list      = self::get_entry( $entry['id'] );
			if ( $user_mail != "" ) {
				$subject = "ثبت سفارش جدید به شناسه " . $order_id;
				$content = '<p>';
				$content .= 'کاربر گرامی ';
				$content .= Helper::get_user_full_name( $entry['created_by'] );
				$content .= '</p><p>';
				$content .= 'سفارش شما با موفقیت در سامانه نشر آنلاین ثبت و برای بررسی اولیه به مدیریت ارسال گردید';
				$content .= '</p>';
				$content .= '<p>شناسه سفارش : ' . $order_id . '</p>';
				$content .= '<p>عنوان سفارش : ' . $entry[ self::$title ] . '</p>';
				$content .= '<p>نوع سفارش : ' . $list[ self::$order_type ] . '</p>';

				WP_Online_Pub::send_mail( $user_mail, $subject, $content );
			}

		}

	}


}