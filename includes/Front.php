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
			if ( $_GET['redirect'] != "user" and $_GET['redirect'] != "xx-admin" ) {
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

			//Check Exist Factor id
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM `z_factor` WHERE `id` = {$_GET['view_factor']}" );
			if ( $count < 1 ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			}

			//Show Factor
			echo '<!DOCTYPE html>
<html lang="fa">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>نمایش فاکتور</title>
	<link href="' . WP_Online_Pub::$plugin_url . '/asset/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="' . WP_Online_Pub::$plugin_url . '/asset/bootstrap/bootstrap-rtl.min.css" rel="stylesheet">
	<link href="' . WP_Online_Pub::$plugin_url . '/asset/font.css" rel="stylesheet">
	<style>
	body {
	    background: #e3e3e3;
	    direction: rtl;
	    font-family: "IRANSans";
	    font-size: 13.5px !important;
    	line-height: 30px;
	}
	</style>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
';
			echo Helper::show_factor( $_GET['view_factor'] );
			echo '
<!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="' . WP_Online_Pub::$plugin_url . '/asset/bootstrap/bootstrap.min.js"></script>
</body>
</html>
';

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

		//Custom Css
		$text .='
		<style>
		.profile-content-inside .sidebar-profile {
   		 	display: none !important;
		}
		.profile-content-inside .content-profile {
    		width: 100% !important;
		}
		</style>
		';

		//Push Asset
		wp_enqueue_script( self::$asset_name );
		wp_enqueue_style( self::$asset_name );

		/**=======================================================================================
		 * Page Notice
		 *----------------------------------------------------------------------------------------*/

		//New Ticket
		if ( isset( $_POST['add_new_ticket'] ) ) {

			//Save To database
			$attachment = "";
			if ( $_FILES['ticket_attachment']['name'] !== '' ) {
				$attachment = Ticket::wp_upload_file( 'ticket_attachment' );
			}

			//No error Sentto Db
			$wpdb->insert(
				"z_ticket",
				array(
					'user_id'     => get_current_user_id(),
					'title'       => trim( $_POST['ticket_title'] ),
					'create_date' => current_time( 'mysql' ),
					'comment'     => $_POST['ticket_comment'],
					'sender'      => 'user',
					'read_admin'  => 0,
					'read_user'   => 1,
					'file'        => $attachment,
					'chat_id'     => $_POST['add_new_ticket'],
				)
			);

			//*******************************************Push Notification To Admin
			//Send Sms
			$arg         = array( "order_id" => $_POST['add_new_ticket'], "user_name" => Helper::get_user_full_name( get_current_user_id() ) );
			WP_Online_Pub::send_sms( 'admin', '', 'send_to_admin_at_ticket_from_user', $arg );

			//Send Email
			$subject = "تیکت جدید کاربر برای سفارش  " . $_POST['chat_id'];
			$content = '<p>';
			$content .= 'مدیر گرامی ، کاربر با نام ';
			$content .= Helper::get_user_full_name(  get_current_user_id() );
			$content .= 'برای سفارش با شناسه ';
			$content .= $_POST['add_new_ticket'];
			$content .= ' یک تیکت ارسال کرده است. ';
			$content .= '</p>';
			$content .= '<p>متن  : </p>';
			$content .= '<p>' . stripslashes( $_POST['ticket_comment'] ) . '</p>';
			$content .= '<br /><br />';
			$content .= '<p>با تشکر</p>';
			$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';
			WP_Online_Pub::send_mail( 'admin', $subject, $content );

			$text .= '<div class="admin_notice suc"> کاربر گرامی پیام شما با موفقیت برای کارشناسان ارسال گردید و بزودی بررسی خواهد شد.</div>';
		}

		/**=======================================================================================
		 * Show Order Page
		 *----------------------------------------------------------------------------------------*/
		if ( isset( $_GET['order_id'] ) and is_numeric( $_GET['order_id'] ) and Helper::check_order_for_user( $_GET['order_id'], $user_id ) === true ) {

			//Get Order
			$row = $wpdb->get_row( "SELECT * FROM `z_order` WHERE `id` = {$_GET['order_id']}", ARRAY_A );
			if ( null !== $row ) {
				$text  .= '<div class="status-order">وضعیت سفارش : ' . Helper::show_status( $row['status'] ) . '</div>';
				$entry = Gravity_Form::get_entry( $row['entry_id'] );

				//Show Order Detail
				$text      .= '
				<div class="order-accordion">
					<div class="title">
						<div class="pull-right">جزئیات سفارش : ' . $entry[ Gravity_Form::$title ] . '</div>
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

				//Pish Factor
				$text  .= '
				<div class="order-accordion">
					<div class="title">
						<div class="pull-right">پیش فاکتور</div>
						<div class="pull-left">+</div>
						<div class="clearfix"></div>
					</div>
					<div class="content">
					';
				$query = $wpdb->get_results( "SELECT * FROM `z_factor` WHERE `order_id` = {$_GET['order_id']} and `type` = 1 ORDER BY `id` DESC", ARRAY_A );
				if ( count( $query ) > 0 ) {

					$text .= '
					<table class="sticky-list">
					<tbody>
					<tr>
					<td>ردیف</td>
					<td>شماره فاکتور</td>
					<td>تاریخ ایجاد</td>
					<td>مبلغ (' . Helper::currency() . ')</td>
					<td>وضعیت پرداخت</td>
					<td></td>
					</tr>
					';
					$z    = 1;
					foreach ( $query as $row ) {

						//Show Factor Status
						$status = '-';
						if ( $row['payment_status'] == 2 ) {
							$payment_inf = $wpdb->get_row( "SELECT * FROM `z_payment` WHERE `factor_id` = {$row['id']} and `status` = 2", ARRAY_A );
							if ( null !== $payment_inf ) {
								$status  = 'پرداخت بصورت : ';
								$status  .= Helper::get_type_payment( $payment_inf['type'] );
								$comment = Helper::get_serialize( $payment_inf['comment'] );
								if ( $row['type'] == 1 ) {
									if ( isset( $comment['payid'] ) ) {
										$status .= '<br /><span>شناسه پرداخت : ' . Helper::show_value( $comment['payid'] ) . '</span>';
									}
								} else {
									$status .= '<br /><span>شماره فیش واریزی : ' . Helper::show_value( $comment['fish'] ) . '</span><br /><span>تاریخ پرداخت : ' . Helper::show_value( $comment['date'] ) . '</span><br />';
								}
							}
						}

						$text .= '
					<tr>
					<td>' . $z . '</td>
					<td>' . $row['id'] . '</td>
					<td>' . date_i18n( "j F Y ساعت H:i", strtotime( $row['date'] ) ) . '</td>
					<td>' . number_format( $row['price'] ) . ' ' . Helper::currency() . '</td>
					<td>' . $status . '</td>
					<td><a href="' . add_query_arg( array( 'view_factor' => $row['id'], 'redirect' => 'user', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() ) . '" target="_blank">' . ( $row['payment_status'] == 2 ? 'مشاهده فاکتور' : 'مشاهده و پرداخت فاکتور' ) . '</a></td>
					</tr>
					';

						$z ++;
					}

					$text .= '
					</tbody>
					</table>
					';
				} else {
					$text .= '<div style="text-align: center;">هیچ پیش فاکتوری برای این سفارش ایجاد نشده است.</div>';
				}
				$text .= '
				</div>
				</div>
				<div class="clearfix"></div>
				';


				//Online Chat
				$unread           = '';
				$count_unread_msg = $wpdb->get_var( "SELECT COUNT(*) FROM `z_ticket` WHERE `chat_id` = {$_GET['order_id']} and `sender` = 'admin' and `read_user` =0" );
				if ( $count_unread_msg > 0 ) {
					$unread = '<div class="unread_ticket">' . $count_unread_msg . '</div>';
				}

				$text .= '
				<div class="order-accordion">
					<div class="title">
						<div class="pull-right">پیام ها ' . $unread . '</div>
						<div class="pull-left">+</div>
						<div class="clearfix"></div>
					</div>
					<div class="content">';

				$text .= Ticket::instance()->showchat( $_GET['order_id'] );

				$text .= '
				</div>
				</div>
				<div class="clearfix"></div>
				';

				//Factor
				$text  .= '
				<div class="order-accordion">
					<div class="title">
						<div class="pull-right">فاکتور</div>
						<div class="pull-left">+</div>
						<div class="clearfix"></div>
					</div>
					<div class="content">
					';
				$query = $wpdb->get_results( "SELECT * FROM `z_factor` WHERE `order_id` = {$_GET['order_id']} and `type` = 2 ORDER BY `id` DESC", ARRAY_A );
				if ( count( $query ) > 0 ) {

					$text .= '
					<table class="sticky-list">
					<tbody>
					<tr>
					<td>ردیف</td>
					<td>شماره فاکتور</td>
					<td>تاریخ ایجاد</td>
					<td>مبلغ (' . Helper::currency() . ')</td>
					<td>وضعیت پرداخت</td>
					<td></td>
					</tr>
					';
					$z    = 1;
					foreach ( $query as $row ) {

						//Show Factor Status
						$status = '-';
						if ( $row['payment_status'] == 2 ) {
							$payment_inf = $wpdb->get_row( "SELECT * FROM `z_payment` WHERE `factor_id` = {$row['id']} and `status` = 2", ARRAY_A );
							if ( null !== $payment_inf ) {
								$status  = 'پرداخت بصورت : ';
								$status  .= Helper::get_type_payment( $payment_inf['type'] );
								$comment = Helper::get_serialize( $payment_inf['comment'] );
								if ( $row['type'] == 1 ) {
									if ( isset( $comment['payid'] ) ) {
										$status .= '<br /><span>شناسه پرداخت : ' . Helper::show_value( $comment['payid'] ) . '</span>';
									}
								} else {
									$status .= '<br /><span>شماره فیش واریزی : ' . Helper::show_value( $comment['fish'] ) . '</span><br /><span>تاریخ پرداخت : ' . Helper::show_value( $comment['date'] ) . '</span><br />';
								}
							}
						}

						$text .= '
					<tr>
					<td>' . $z . '</td>
					<td>' . $row['id'] . '</td>
					<td>' . date_i18n( "j F Y ساعت H:i", strtotime( $row['date'] ) ) . '</td>
					<td>' . number_format( $row['price'] ) . ' ' . Helper::currency() . '</td>
					<td>' . $status . '</td>
					<td><a href="' . add_query_arg( array( 'view_factor' => $row['id'], 'redirect' => 'user', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() ) . '" target="_blank">' . ( $row['payment_status'] == 2 ? 'مشاهده فاکتور' : 'مشاهده و پرداخت فاکتور' ) . '</a></td>
					</tr>
					';

						$z ++;
					}

					$text .= '
					</tbody>
					</table>
					';
				} else {
					$text .= '<div style="text-align: center;">هیچ فاکتوری برای این سفارش ایجاد نشده است.</div>';
				}
				$text .= '
				</div>
				</div>
				<div class="clearfix"></div>
				';
			}


		}


		/**=======================================================================================
		 * Show Factor List
		 *----------------------------------------------------------------------------------------*/
		if ( ! isset( $_GET['order_id'] ) ) {


			//Get List Factor
			$query = $wpdb->get_results( "SELECT * FROM `z_order` WHERE `user_id` = $user_id ORDER BY `id` DESC", ARRAY_A );
			if ( count( $query ) > 0 ) {

				$text .= '<div id="sticky-list-wrapper_12" class="sticky-list-wrapper" style="font-size: 14px;">';
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