<?php

namespace WP_OnlinePub;

use WP_Online_Pub;

class Gravity_Form {

	/**
	 * Gravity Form Option
	 */
	public static $gravity_opt;

	/**
	 * Order Form Id
	 */
	public static $order_form_id;

	/**
	 * Custom Field ID for Push to table
	 */
	public static $title;
	public static $order_type;

	/**
	 * Hidden Field id in Entry Table
	 */
	public static $hidden_field_id;

	/**
	 * Gravity_Form constructor.
	 */
	public function __construct() {

		if ( get_option( 'wp_online_pub_gravity' ) ) {
			//Get Gravity Option
			self::$gravity_opt = get_option( 'wp_online_pub_gravity' );

			//Set Order Form
			self::$order_form_id   = self::$gravity_opt['order'];
			self::$title           = self::$gravity_opt['title'];
			self::$order_type      = self::$gravity_opt['type'];
			self::$hidden_field_id = explode( ",", self::$gravity_opt['hidden_field'] );
		}

		//Save Order After Push Form
		add_action( 'gform_after_submission_' . self::$order_form_id, array( $this, 'after_submission_order_form' ), 10, 2 );


	}

	/**
	 * Get Entry From Gravity
	 *
	 * @param $entry_id
	 * @return array
	 */
	public static function get_entry( $entry_id ) {
		return \GFAPI::get_entry( $entry_id );
	}

	/**
	 * Get List Of Form From Gravity Form
	 */
	public static function get_forms_list() {
		$list  = array();
		$forms = \GFAPI::get_forms();
		foreach ( $forms as $form ) {
			$list[ $form['id'] ] = $form['title'];
		}

		return $list;
	}

	/**
	 * Remove Entry By id
	 *
	 * @param $entry_id
	 * @return bool
	 */
	public static function remove_entry( $entry_id ) {
		return \GFAPI::delete_entry( $entry_id );
	}

	/**
	 * Get List Of Entry With From Label
	 *
	 * @param $entry_id
	 * @param array $hide_field_type
	 * @param array $hide_field_id
	 * @return array
	 */
	public static function get_entry_table( $entry_id, $hide_field_type = array(), $hide_field_id = array() ) {

		//Create empty Object
		$obj = array();

		//Get This Entry
		$entry = self::get_entry( $entry_id );

		//Get List Of field
		$form = \GFAPI::get_form( Gravity_Form::$order_form_id );
		foreach ( $form['fields'] as $field ) {

			$label = $field->label;
			if ( ! empty( $label ) and isset( $entry[ $field->id ] ) and ! empty( $entry[ $field->id ] ) and ! in_array( $field->type, $hide_field_type ) and ! in_array( $field->id, $hide_field_id ) ) {
				$value = $entry[ $field->id ];
				if ( $field->type == "fileupload" ) {
					$list_file = json_decode( $value );
					$x         = 1;
					$value     = '';
					foreach ( $list_file as $f ) {
						$value .= '<a href="' . $f . '" target="_blank">دریافت فایل ' . $x . ' </a>';
						$x ++;
						if ( $x >= count( $list_file ) ) {
							$value .= '<br>';
						}
					}
				}

				$obj[] = array( "name" => esc_html( $label ), "value" => $value );
			}
		}

		return $obj;
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