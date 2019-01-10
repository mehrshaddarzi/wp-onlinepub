<?php

namespace WP_OnlinePub;

use WP_Online_Pub;

class Front {

	/**
	 * Asset Script name
	 */
	public static $asset_name = 'user-order';


	/**
	 * constructor.
	 */
	public function __construct() {

		//ShortCode List Order User
		add_shortcode( 'user-order', array( $this, 'user_order_list' ) );

		//Add Script
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );

		//Show Factor
		add_action( 'wp', array( $this, 'show_factor' ) );
	}

	/**
	 * Register Asset
	 */
	public function wp_enqueue_style() {

		wp_register_style( self::$asset_name, WP_Online_Pub::$plugin_url . '/asset/style.css', array(), WP_Online_Pub::$plugin_version, 'all' );
		wp_register_script( self::$asset_name, WP_Online_Pub::$plugin_url . '/asset/script.js', array( 'jquery' ), WP_Online_Pub::$plugin_version, false );
	}


	/**
	 * Show Factor
	 */
	public function show_factor() {
		global $wpdb;

		if ( isset( $_GET['view_factor'] ) and isset( $_GET['redirect'] ) and isset( $_GET['_security_code'] ) ) {

			//Check Nonce
			if ( ! wp_verify_nonce( $_GET['_security_code'], 'view_factor_access' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			}

			//Security Redirec
			if ( $_GET['redirect'] != "user" || $_GET['redirect'] != "xx-admin" ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			}

			//Check Security Factor For this User
			if ( $_GET['redirect'] == "user" ) {
				$user_id = get_current_user_id();
				$count   = $wpdb->get_var( "SELECT COUNT(*) FROM `z_factor` WHERE `id` = {$_GET['view_factor']} AND `user_id` = {$user_id}" );
				if ( $count < 1 ) {
					die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
				}
			}

			//Show Factor
			echo 'this_is';





			exit;
		}
	}

	/**
	 * User Order List
	 */
	public function user_order_list() {
		global $wpdb;

		//Create Empty Text Object
		$text      = '<div class="user-order">';
		$page_id   = get_queried_object_id();
		$page_link = get_the_permalink( $page_id );
		$user_id   = get_current_user_id();

		//Push Asset
		wp_enqueue_script( self::$asset_name );
		wp_enqueue_style( self::$asset_name );

		//Show Custom Order Detail
		if ( isset( $_GET['order_id'] ) and is_numeric( $_GET['order_id'] ) and Helper::check_order_for_user( $_GET['order_id'], $user_id ) === true ) {

			//Get Order
			$row = $wpdb->get_row( "SELECT * FROM `z_order` WHERE `id` = {$_GET['order_id']}", ARRAY_A );
			if ( null !== $row ) {
				$text  .= '<div class="status-order">وضعیت سفارش : ' . Helper::show_status( $row['id'] ) . '</div>';
				$entry = Gravity_Form::get_entry( $row['entry_id'] );

				$text      .= '
				<div class="order-accordion">
					<div class="title">
						<div class="pull-right">' . $entry[ Gravity_Form::$title ] . '</div>
						<div class="pull-left">+</div>
						<div class="clearfix"></div>
					</div>
					<div class="content">
					<table class="sticky-list">
					<tbody>
					';
				$entry_tbl = Gravity_Form::get_entry_table( $row['entry_id'], array( "hidden" ), Gravity_Form::$hidden_field_id );
				foreach ( $entry_tbl as $k => $v ) {
					$text .= '
		                <tr>
		                    <td>' . $v['name'] . '</td>
		                    <td>' . $v['value'] . '</td>
		                </tr>';
				}
				$text .= '
				</tbody>
				</table>
				</div>
				</div>
				<div class="clearfix"></div>
				';


			}


		}


		//Show All Factor
		if ( ! isset( $_GET['order_id'] ) ) {


			//Get List Factor

			$query = $wpdb->get_results( "SELECT * FROM `z_order` WHERE `user_id` = $user_id ORDER BY `id` DESC", ARRAY_A );
			if ( count( $query ) > 0 ) {

				$text = '<div id="sticky-list-wrapper_12" class="sticky-list-wrapper">';
				$text .= '<table class="sticky-list">
<thead>
<tr>
<th class="sort header-شناسه" data - sort = "sort-0" > شناسه</th >
<th class="sort header-تاریخ" data - sort = "sort-0" > تاریخ</th >
<th class="sort header-نوع-سفارش" data - sort = "sort-1" > نوع سفارش </th >
<th class="sort header-عنوان-سفارش" data - sort = "sort-2" > عنوان سفارش </th >
<th class="sort header-وضعیت" data - sort = "sort-3" > وضعیت</th >
<th class="sort header-وضعیت" data - sort = "sort-3" ></th >
</tr>
</thead>
<tbody class="list">
';

				foreach ( $query as $row ) {
					$entry = Gravity_Form::get_entry( $row['entry_id'] );
					$text  .= '
<tr>
<td>' . $row['id'] . '</td>
<td>' . date_i18n( "Y/m/d", $row['date'] ) . '</td>
<td>' . $entry[ Gravity_Form::$order_type ] . '</td>
<td>' . $entry[ Gravity_Form::$title ] . '</td>
<td>' . Helper::show_status( $row['status'] ) . '</td>
<td><a href="' . add_query_arg( array( 'order_id' => $row['id'] ), $page_link ) . '">جزئیات و پیگیری</a></td>
</tr>
';
				}

				$text .= '</tbody ></table ></div >';
				$text .= '</div>';

			} else {
				$text .= '<div style="text-align:center;">شما هیچ سفارشی تا بحال ثبت نکرده اید</div>';
			}

		}


		$text .= '</div>';
		return $text;
	}


}