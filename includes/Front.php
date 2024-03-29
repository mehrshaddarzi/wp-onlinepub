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

		//Add shortcode Show User Dashboard
		add_shortcode( 'user-dashboard', array( $this, 'user_dashboard_card' ) );

		//Add Script
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );

		//Show Factor
		add_action( 'wp', array( $this, 'show_factor' ) );

		//Send Request Noticiation
		add_action( 'wp', array( $this, 'check_new_notification' ) );
	}

	/**
	 * Register Asset
	 */
	public function wp_enqueue_style() {

		//Jquery Growl
		//@see https://github.com/ksylvest/jquery-growl
		wp_enqueue_style( 'jquery-growl', WP_Online_Pub::$plugin_url . '/asset/growl/jquery.growl.css', array(), WP_Online_Pub::$plugin_version, 'all' );
		wp_enqueue_script( 'jquery-growl', WP_Online_Pub::$plugin_url . '/asset/growl/jquery.growl.js', array( 'jquery' ), WP_Online_Pub::$plugin_version, false );

		//Native Plugin
		wp_enqueue_style( self::$asset_name, WP_Online_Pub::$plugin_url . '/asset/style.css', array(), WP_Online_Pub::$plugin_version, 'all' );
		wp_enqueue_script( self::$asset_name, WP_Online_Pub::$plugin_url . '/asset/script.js', array( 'jquery' ), WP_Online_Pub::$plugin_version, false );
		wp_localize_script( self::$asset_name, 'wps_online_js', array(
			'ajax'          => home_url() . '/?wp_online_pub_check_notification=yes&time=' . current_time( 'timestamp' ),
			'is_login_user' => ( is_user_logged_in() ? 1 : 0 )
		) );
	}


	/**
	 * ShortCode Show Card Dashboard
	 */
	public function user_dashboard_card() {
		global $wpdb;

		$user_id = get_current_user_id();

		//Get Number Order
		$count_order = $wpdb->get_var( "SELECT COUNT(*) FROM `z_order` WHERE `user_id` = $user_id" );

		//Get Order dar hale ejra
		$count_order_continue = $wpdb->get_var( "SELECT COUNT(*) FROM `z_order` WHERE `user_id` = $user_id and `status` != 9" );

		//Get Order Takmil Shode
		$count_order_complete = $wpdb->get_var( "SELECT COUNT(*) FROM `z_order` WHERE `user_id` = $user_id and `status` = 9" );

		//Get New Payam
		$new_ticket = $wpdb->get_var( "SELECT COUNT(*) FROM `z_ticket` WHERE `user_id` = {$user_id} and `sender` = 'admin' and `read_user` =0" );

		//Get Factor
		$factor = $wpdb->get_var( "SELECT COUNT(*) FROM `z_factor` WHERE `user_id` = {$user_id}" );

		//Kol Pardakht
		$pardakht = $wpdb->get_var( "SELECT SUM(price) FROM `z_factor` WHERE `user_id` = {$user_id} AND `payment_status` = 2" );

		//Faktor dar entezar pardakht
		$factor_pardakht_nashode = $wpdb->get_var( "SELECT COUNT(*) FROM `z_factor` WHERE `user_id` = {$user_id} AND `payment_status` = 1" );

		//Create Empty Text
		$text = '
			<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
			<style>
				.card {
				    width: 33%;
				    display: inline-block;
				    min-height: 100px;
				    background: #e3e3e3;
				    color: #fff;
				    padding: 15px;
				    margin-bottom: 15px;
				    transition: 1s all;
				    cursor: default;
				}
				.card:hover {
					background: #636363;
					opacity: 0.5;
				}
				.card i {
				    font-size: 38px;
   					 padding: 15px;
				}
				.card .title {
				font-size: 16px;
				}
				.card .number {
				    font-size: 23px;
				    display: block;
				    margin-top: 10px;
				    padding-right: 20px;
				}
				
	.card-red  {
      color: white;
      background-color: #ff4a43; }
   
    .card-hotpink  {
      color: white;
      background-color: #ff3385; }

    .card-drank  {
      color: white;
      background-color: #4b9c4e; }

    .card-greensea  {
      color: white;
      background-color: #2f6acd; }

    .card-cyan  {
      color: white;
      background-color: #22beef; }
  
    .card-green  {
      color: white;
      background-color: #a2d200; }

    .card-orange  {
      color: white;
      background-color: #f5bf19; }

    .card-amethyst  {
      color: white;
      background-color: #9040bd; }
      
      
     @media only screen and (max-width: 1100px) {
			.card {
				    width: 48%;
				}
		}
		
      @media only screen and (max-width: 830px) {
			.card {
				    width: 99%;
				}
		}
      
			</style>
			
			<div class="dashboard-card">
			
				<div class="card card-red">
					<div class="pull-right">
						<div class="title">
						تعداد سفارش
						</div>	
						<div class="number">
						' . number_format_i18n( $count_order ) . '
						</div>
					</div>
					<div class="pull-left">
						<i class="fa fa-pencil"></i>
					</div>
					<div class="clearfix"></div>
				</div>
				
				<div class="card card-hotpink">
					<div class="pull-right">
						<div class="title">
						سفارشات در حال اجرا
						</div>	
						<div class="number">
						' . number_format_i18n( $count_order_continue ) . '
						</div>
					</div>
					<div class="pull-left">
						<i class="fa fa-cog"></i>
					</div>
					<div class="clearfix"></div>
				</div>
				
				<div class="card card-drank">
					<div class="pull-right">
						<div class="title">
						سفارشات تکمیل شده
						</div>	
						<div class="number">
						' . number_format_i18n( $count_order_complete ) . '
						</div>
					</div>
					<div class="pull-left">
						<i class="fa fa-thumb-tack"></i>
					</div>
					<div class="clearfix"></div>
				</div>
				
				
				<div class="card card-orange">
					<div class="pull-right">
						<div class="title">
						پیام جدید
						</div>	
						<div class="number">
						' . number_format_i18n( $new_ticket ) . '
						</div>
					</div>
					<div class="pull-left">
						<i class="fa fa-comments-o"></i>
					</div>
					<div class="clearfix"></div>
				</div>
				
				
				<div class="card card-amethyst">
					<div class="pull-right">
						<div class="title">
						تعداد فاکتور
						</div>	
						<div class="number">
						' . number_format_i18n( $factor ) . '
						</div>
					</div>
					<div class="pull-left">
						<i class="fa fa-file-text-o"></i>
					</div>
					<div class="clearfix"></div>
				</div>
				
				
				<div class="card card-greensea">
					<div class="pull-right">
						<div class="title">
						فاکتور در انتظار پرداخت
						</div>	
						<div class="number">
						' . number_format_i18n( $factor_pardakht_nashode ) . '
						</div>
					</div>
					<div class="pull-left">
						<i class="fa fa-wpforms"></i>
					</div>
					<div class="clearfix"></div>
				</div>

			</div>
		';

		return $text;
	}

	/**
	 * Check New Notification
	 */
	public static function check_new_notification() {
		global $wpdb;

		if ( isset( $_GET['wp_online_pub_check_notification'] ) and isset( $_GET['time'] ) and is_user_logged_in() ) {

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
			$alert = $wpdb->get_row( "SELECT * FROM `z_ticket` WHERE `user_id` = {$user_id} and `sender` = 'admin' and `read_user` =0", ARRAY_A );
			if ( null !== $alert ) {
				$t      = 'شما یک پیام جدید در سفارش';
				$t      .= ' ' . $alert['chat_id'] . ' ';
				$t      .= 'دارید';
				$result = array(
					'exist' => 'yes',
					'title' => 'پیام جدید',
					'text'  => $t,
					'url'   => add_query_arg( array( 'order' => $alert['chat_id'] ), get_the_permalink( WP_Online_Pub::$option['user_panel'] ) )
				);
			}

			//Check New Factor
			$factor = $wpdb->get_row( "SELECT * FROM `z_factor` WHERE `user_id` = $user_id AND `read_user` = 0 ORDER BY `id` DESC LIMIT 1", ARRAY_A );
			if ( null !== $factor ) {

				$t = 'شما یک فاکتور جدید در سفارش';
				$t .= ' ' . $factor['order_id'] . ' ';
				$t .= 'دارید';

				$result = array(
					'exist' => 'yes',
					'title' => 'فاکتور جدید',
					'text'  => $t,
					'url'   => add_query_arg( array( 'view_factor' => $factor['id'], 'redirect' => 'user', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() )
				);
			}

			//Send Data
			wp_send_json( $result );
			exit;
		}
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
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
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
<div class="col-md-8 col-md-offset-2 hidden-print">
<div class="col-sm-8">
<div style="line-height: 40px;">
';

			$factor = Helper::get_factor( $_GET['view_factor'] );
			if ( $factor['payment_status'] == 1 ) {

				echo 'شما میتوانید از طریق دو روش زیر فاکتور را پرداخت نمایید :';
				echo '<a style="display: block;margin: 30px auto;   width: 50%;" href="' . add_query_arg( array( 'payment_factor' => $factor['id'], 'order' => $factor['order_id'], '_pay_code' => wp_create_nonce( 'payment_factor_price' ) ), home_url() ) . '" class="btn btn-danger">پرداخت آنلاین با کارت های عضو سیستم شتاب</a>';
				echo 'و یا مبلغ را به یکی از حساب های بانکی زیر واریز نموده و سپس فرم را تکمیل کنید.';
				echo '<br>';
				for ( $i = 1; $i <= 2; $i ++ ) {
					if ( WP_Online_Pub::$option[ 'acc_' . $i ] != "" ) {
						echo '<span class="text-primary">' . WP_Online_Pub::$option[ 'acc_' . $i ] . '</span><br>';
					}
				}

				echo '
<div style="width:40%">
<form action="' . add_query_arg( array( 'order' => $factor['order_id'] ), get_the_permalink( WP_Online_Pub::$option['user_panel'] ) ) . '" method="post" onsubmit="return confirm(\'آیا از صحت اطلاعات اطمینان حاصل دارید ?\');">
<input type="hidden" name="add_new_fish_bank" value="' . wp_create_nonce( 'add_fish_security' ) . '">
<input type="hidden" name="factor_id" value="' . $_GET['view_factor'] . '">
  <div class="form-group">
    <label for="exampleInputEmail1">شماره فیش واریزی</label>
    <input type="text" class="form-control" style="text-align: left; direction:ltr;" name="fish_bank" required="required" />
  </div>
   <div class="form-group">
    <label for="exampleInputEmail1">تاریخ واریز</label>
    <input type="text" class="form-control" name="date_bank" style="text-align: left; direction:ltr;" value="' . date_i18n( "Y-m", time() ) . '-xx" required="required" />
  </div>
  <button type="submit" class="btn btn-warning">ارسال اطلاعات</button>
</form>
</div>
<br><br>
';
			} else {

				echo 'وضعیت : ';
				echo '<span class="text-danger">پرداخت شده</span>';
				echo '<br>';
				$payment_inf = $wpdb->get_row( "SELECT * FROM `z_payment` WHERE `factor_id` = {$factor['id']} and `status` = 2", ARRAY_A );
				if ( null !== $payment_inf ) {
					echo 'پرداخت بصورت : ';
					echo Helper::get_type_payment( $payment_inf['type'] );
					if ( ! empty( $payment_inf['comment'] ) ) {
						$comment = Helper::get_serialize( $payment_inf['comment'] );
						if ( $payment_inf['type'] == 1 ) {
							if ( isset( $comment['payid'] ) ) {
								echo '<br /><span>شناسه پرداخت : ' . Helper::show_value( $comment['payid'] ) . '</span>';
							}
						} else {
							echo '<br /><span>شماره فیش واریزی : ' . Helper::show_value( $comment['fish'] ) . '</span><br /><span>تاریخ پرداخت : ' . Helper::show_value( $comment['date'] ) . '</span><br />';
						}
					}

				}


			}

			echo '	
</div>	
</div>
<div class="col-sm-4 text-left">
<button class="btn btn-default"  onClick="window.print()" type="submit"><i class="fa fa-print"></i> پرینت فاکتور</button>
</div>
<div class="clearfix"></div>
	</div>
	';


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
		$text .= '
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

		//Show Status Of Payment
		if ( isset( $_GET['check_payment_status'] ) and isset( $_GET['payment_factor'] ) and isset( $_GET['pay_id'] ) ) {

			$get_payment = Helper::get_payment( $_GET['pay_id'] );
			if ( $get_payment !== null ) {
				if ( $get_payment['status'] == 2 ) {
					$comment = Helper::get_serialize( $get_payment['comment'] );
					$text    .= '<div class="admin_notice suc"> 
					پرداخت شما با موفقیت انجام شد.
					<br />
					شناسه پرداخت : 
					' . $comment['payid'] . '
					</div>';
				} else {
					$text .= '<div class="admin_notice err"> پرداخت شما موفقیت آمیز نبوده است.لطفا دوباره تلاش کنید.</div>';
				}
			}
		}

		//Add Fish Bank
		if ( isset( $_POST['add_new_fish_bank'] ) and isset( $_POST['fish_bank'] ) and isset( $_POST['factor_id'] ) and isset( $_POST['date_bank'] ) ) {

			//Check Nonce
			if ( ! wp_verify_nonce( $_POST['add_new_fish_bank'], 'add_fish_security' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			}

			//SaveToDB
			$comment = array(
				'fish' => $_POST['fish_bank'],
				'date' => $_POST['date_bank']
			);
			$factor  = Helper::get_factor( $_POST['factor_id'] );
			$wpdb->insert(
				'z_payment',
				array(
					'user_id'   => get_current_user_id(),
					'type'      => 2,
					'status'    => 1,
					'factor_id' => $_POST['factor_id'],
					'price'     => $factor['price'],
					'date'      => current_time( 'mysql' ),
					'comment'   => serialize( $comment )
				)
			);

			//*******************************************Push Notification To Admin
			//Send Sms
			$arg = array( "factor_id" => $_POST['factor_id'], "user_name" => Helper::get_user_full_name( get_current_user_id() ) );
			WP_Online_Pub::send_sms( 'admin', '', 'send_to_admin_at_new_fish', $arg );

			//Send Email
			$subject = "فیش جدید برای فاکتور  " . $_POST['factor_id'];
			$content = '<p>';
			$content .= 'مدیر گرامی ، کاربر با نام ';
			$content .= Helper::get_user_full_name( get_current_user_id() );
			$content .= ' برای فاکتور با شناسه ';
			$content .= $_POST['factor_id'];
			$content .= ' یک فیش ارسال کرده است. ';
			$content .= '</p>';
			$content .= '<br /><br />';
			$content .= '<p>با تشکر</p>';
			$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';
			WP_Online_Pub::send_mail( 'admin', $subject, $content );

			$text .= '<div class="admin_notice suc"> اطلاعات فیش بانکی شما با موفقیت ثبت گردید.</div>';
		}

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
			$arg = array( "order_id" => $_POST['add_new_ticket'], "user_name" => Helper::get_user_full_name( get_current_user_id() ) );
			WP_Online_Pub::send_sms( 'admin', '', 'send_to_admin_at_ticket_from_user', $arg );

			//Send Email
			$subject = "تیکت جدید کاربر برای سفارش  " . $_POST['chat_id'];
			$content = '<p>';
			$content .= 'مدیر گرامی ، کاربر با نام ';
			$content .= Helper::get_user_full_name( get_current_user_id() );
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
		if ( isset( $_GET['order'] ) and is_numeric( $_GET['order'] ) and Helper::check_order_for_user( $_GET['order'], $user_id ) === true ) {

			//disable Title
			echo '
			<style>
			.t-page {
			display: none;
			}
			
			</style>
			
			';


			//Get Order
			$row = $wpdb->get_row( "SELECT * FROM `z_order` WHERE `id` = {$_GET['order']}", ARRAY_A );
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
						<div class="pull-right">پیش فاکتور (پیش پرداخت)</div>
						<div class="pull-left">+</div>
						<div class="clearfix"></div>
					</div>
					<div class="content">
					';
				$query = $wpdb->get_results( "SELECT * FROM `z_factor` WHERE `order_id` = {$_GET['order']} and `type` = 1 ORDER BY `id` DESC", ARRAY_A );
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
								$status = 'پرداخت بصورت : ';
								$status .= Helper::get_type_payment( $payment_inf['type'] );
								if ( ! empty( $payment_inf['comment'] ) ) {
									$comment = Helper::get_serialize( $payment_inf['comment'] );
									if ( $payment_inf['type'] == 1 ) {
										if ( isset( $comment['payid'] ) ) {
											$status .= '<br /><span>شناسه پرداخت : ' . Helper::show_value( $comment['payid'] ) . '</span>';
										}
									} else {
										$status .= '<br /><span>شماره فیش واریزی : ' . Helper::show_value( $comment['fish'] ) . '</span><br /><span>تاریخ پرداخت : ' . Helper::show_value( $comment['date'] ) . '</span><br />';
									}
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
					<td style="background: #ff0000;"><a style="color: #fff;" href="' . add_query_arg( array( 'view_factor' => $row['id'], 'redirect' => 'user', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() ) . '" target="_blank">' . ( $row['payment_status'] == 2 ? 'مشاهده فاکتور' : 'مشاهده و پرداخت فاکتور' ) . '</a></td>
					</tr>
					';

						$z ++;
					}

					$text .= '
					</tbody>
					</table>
					';

					$text .= '<br><div style="text-align: center;">مبلغ قابل پرداخت در پیش فاکتور ۵۰ درصد مبلغ جمع کل می باشد.</div>';
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
				$count_unread_msg = $wpdb->get_var( "SELECT COUNT(*) FROM `z_ticket` WHERE `chat_id` = {$_GET['order']} and `sender` = 'admin' and `read_user` =0" );
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

				$text .= Ticket::instance()->showchat( $_GET['order'] );

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
				$query = $wpdb->get_results( "SELECT * FROM `z_factor` WHERE `order_id` = {$_GET['order']} and `type` = 2 ORDER BY `id` DESC", ARRAY_A );
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
								$status = 'پرداخت بصورت : ';
								$status .= Helper::get_type_payment( $payment_inf['type'] );
								if ( ! empty( $payment_inf['comment'] ) ) {
									$comment = Helper::get_serialize( $payment_inf['comment'] );
									if ( $payment_inf['type'] == 1 ) {
										if ( isset( $comment['payid'] ) ) {
											$status .= '<br /><span>شناسه پرداخت : ' . Helper::show_value( $comment['payid'] ) . '</span>';
										}
									} else {
										$status .= '<br /><span>شماره فیش واریزی : ' . Helper::show_value( $comment['fish'] ) . '</span><br /><span>تاریخ پرداخت : ' . Helper::show_value( $comment['date'] ) . '</span><br />';
									}
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
					<td style="background: #ff0000;"><a style="color: #fff;" href="' . add_query_arg( array( 'view_factor' => $row['id'], 'redirect' => 'user', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() ) . '" target="_blank">' . ( $row['payment_status'] == 2 ? 'مشاهده فاکتور' : 'مشاهده و پرداخت فاکتور' ) . '</a></td>
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
		if ( ! isset( $_GET['order'] ) ) {


			//Get List Factor
			$query = $wpdb->get_results( "SELECT * FROM `z_order` WHERE `user_id` = $user_id ORDER BY `id` DESC", ARRAY_A );
			if ( count( $query ) > 0 ) {

				$text = '<div id="sticky-list-wrapper_12" class="sticky-list-wrapper" style="font-size: 14px;">';
				$text .= '
					<style>
					.profile-content-inside .sidebar-profile {
			            display: none !important;
					}
					.profile-content-inside .content-profile {
			            width: 100% !important;
					}
					</style>
					';
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
<td style="background: #ff0000;"><a style="color: #fff;" href="' . add_query_arg( array( 'order' => $row['id'] ), $page_link ) . '">جزئیات و پیگیری</a></td>
</tr>
';
				}

				$text .= '</tbody ></table ></div >';
				//$text .= '</div>';

			} else {
				$text .= '<div style="text-align:center;">شما هیچ سفارشی تا بحال ثبت نکرده اید</div>';
			}
			return $text;
		}


		$text .= '</div>';
		return $text;
	}


}