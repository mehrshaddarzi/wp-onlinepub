<?php

namespace WP_OnlinePub;

use WP_Online_Pub;

class Payment {

	/**
	 * Zarin Pal Code
	 */
	public static $payment_service;


	/**
	 * Payment constructor.
	 */
	public function __construct() {

		self::$payment_service = \WP_Online_Pub::$option['zarinpal'];
		add_action( 'wp', array( $this, 'SendZarinPal' ) );
		add_action( 'wp', array( $this, 'ReceiveZarinPal' ) );

	}


	/**
	 * Send Request
	 */
	public function SendZarinPal() {
		global $wpdb;

		if ( isset( $_GET['payment_factor'] ) and isset( $_GET['order'] ) and isset( $_GET['_pay_code'] ) ) {

			//Check Security Code
			if ( ! wp_verify_nonce( $_GET['_pay_code'], 'payment_factor_price' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			}

			//Set Url Back
			$url_back = add_query_arg( array( 'check_payment_status' => 'yes', 'order' => $_GET['order'] ), get_the_permalink( WP_Online_Pub::$option['user_panel'] ) );

			//Check Validation request
			$user_id   = get_current_user_id();
			$is_factor = $wpdb->get_var( "SELECT COUNT(*) FROM `z_factor` WHERE `user_id` = {$user_id} and `id` = {$_GET['payment_factor']} and `payment_status` =1" );
			if ( $is_factor > 0 ) {

				//Get Factor Detail
				$this_factor = Helper::get_factor( $_GET['payment_factor'] );

				//Load Nosoup
				include( WP_Online_Pub::$plugin_path . '/lib/nusoap/nusoap.php' );

				//init Data for Zarinpal
				$MerchantID  = self::$payment_service;
				$Amount      = $this_factor['price'];
				$Description = 'بابت پرداخت آنلاین فاکتور خرید به شماره ' . $_GET['payment_factor'];

				//Tax ZarinPal
				$price = $Amount;
				//if ( $this->redux['is_tax_zarinpal'] == "yes" ) {
				//	$price = round( $Amount + round( $Amount * 0.010 ) );
				//	}

				//Add Payment to database
				$wpdb->insert(
					'z_payment',
					array(
						'user_id'   => get_current_user_id(),
						'type'      => 1,
						'status'    => 1,
						'factor_id' => $_GET['payment_factor'],
						'price'     => $price,
						'date'      => current_time( 'mysql' )
					)
				);

				//Send Request To Zarinpal
				$idpay                    = $wpdb->insert_id;
				$CallbackURL              = $url_back . '&payment_factor=' . $_GET['payment_factor'] . '&pay_id=' . $idpay;
				$Email                    = ucfirst( Helper::get_user_email( get_current_user_id() ) );
				$Mobile                   = Helper::get_user_mobile( get_current_user_id() );
				$client                   = new \nusoap_client( 'https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl' );
				$client->soap_defencoding = 'UTF-8';
				$result                   = $client->call( 'PaymentRequest', array(
						array(
							'MerchantID'  => $MerchantID,
							'Amount'      => $price,
							'Description' => $Description,
							'Email'       => $Email,
							'Mobile'      => $Mobile,
							'CallbackURL' => $CallbackURL
						)
					)
				);

				//Update Order Id Pay
				$wpdb->update(
					'z_payment',
					array(
						'comment' => serialize( array( 'payid' => ltrim( $result['Authority'], '0' ) ) )
					),
					array( 'id' => $idpay )
				);

				//Check request zarinPal is True / False
				if ( $result['Status'] == 100 ) {
					//If Not ZarinGate, I Must Remove /zaringate from this url
					Header( 'Location: https://www.zarinpal.com/pg/StartPay/' . $result['Authority'] . '/ZarinGate' );
					exit;
				} else {
					wp_redirect( $url_back . '&check_payment_status=yes&payment_factor=' . $_GET['payment_factor'] . '&pay_id=' . $idpay );
					exit;
				}

			} else {
				wp_redirect( $url_back );
				exit;
			}

		}
	}


	public function ReceiveZarinPal() {
		global $wpdb;
		if ( isset( $_GET['payment_factor'] ) and isset( $_GET['pay_id'] ) and isset( $_GET['order'] ) and isset( $_GET['Status'] ) ) { //Status For Check from ZarinPal

			//Setup Url Back
			$url_back    = add_query_arg( array( 'check_payment_status' => 'yes', 'order' => $_GET['order'], 'payment_factor' => $_GET['payment_factor'], 'pay_id' => $_GET['pay_id'] ), get_the_permalink( WP_Online_Pub::$option['user_panel'] ) );
			$this_factor = Helper::get_factor( $_GET['payment_factor'] );

			//include Soap
			include( WP_Online_Pub::$plugin_path . '/lib/nusoap/nusoap.php' );
			$MerchantID = self::$payment_service;
			$Amount     = $this_factor['price']; //Amount will be based on Toman
			$Authority  = $_GET['Authority'];

			if ( $_GET['Status'] == 'OK' ) {
				// URL also Can be https://ir.zarinpal.com/pg/services/WebGate/wsdl
				$client                   = new \nusoap_client( 'https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl' );
				$client->soap_defencoding = 'UTF-8';
				$result                   = $client->call( 'PaymentVerification', array(
						array(
							'MerchantID' => $MerchantID,
							'Authority'  => $Authority,
							'Amount'     => $Amount
						)
					)
				);
				if ( $result['Status'] == 100 ) {

					//Update Factor Type Payment
					$wpdb->update(
						'z_factor',
						array(
							'payment_status' => 2,
						),
						array( 'id' => $_GET['payment_factor'] )
					);

					//Update Order Status
					$this_order = Helper::get_order( $this_factor['order_id'] );
					if ( $this_order['status'] <= 6 ) {
						$new_status_order = 3;
					} else {
						$new_status_order = 8;
					}
					$wpdb->update(
						'z_order',
						array(
							'status' => $new_status_order,
						),
						array( 'id' => $this_factor['order_id'] )
					);

					//Update Payment Type
					$wpdb->update(
						'z_payment',
						array(
							'status' => 2,
						),
						array( 'id' => $_GET['pay_id'] )
					);

					//Sms For User
					$arg         = array( "factor_id" => $_GET['payment_factor'], "factor_price" => $Amount, "user_name" => Helper::get_user_full_name( get_current_user_id() ) );
					$user_mobile = Helper::get_user_mobile( get_current_user_id() );
					if ( $user_mobile != "" ) {
						WP_Online_Pub::send_sms( $user_mobile, '', 'send_to_user_at_pay_online_factor', $arg );
					}

					//Email for User
					$user_mail = Helper::get_user_email( get_current_user_id() );
					if ( $user_mail != "" ) {
						$subject = "تایید پرداخت فاکتور  " . $_GET['payment_factor'];

						$content = '<p>';
						$content .= 'کاربر گرامی ';
						$content .= Helper::get_user_full_name( get_current_user_id() );
						$content .= '</p><p>';
						if ( $this_factor['factor_type'] == 1 ) {
							$content .= 'پیش فاکتور ';
						} else {
							$content .= 'فاکتور ';
						}
						$content .= " به شناسه ";
						$content .= $_GET['payment_factor'];
						$content .= " و مبلغ ";
						$content .= number_format( $Amount ) . ' ' . \WP_OnlinePub\Helper::currency() . ' ';
						$content .= 'با موفقیت پرداخت و تایید شد. ';
						$content .= '</p><br /><p>';
						$content .= '</p><br />';
						$content .= '<p>با تشکر</p>';
						$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';

						WP_Online_Pub::send_mail( $user_mail, $subject, $content );
					}
					sleep( 1 );

					//Sms for Admin
					WP_Online_Pub::send_sms( 'admin', '', 'send_to_admin_at_new_online_pay', $arg );

					//Email For Admin
					$subject = "پرداخت آنلاین موفق فاکتور  " . $_GET['payment_factor'];
					$content = '<p>';
					$content .= 'مدیر گرامی ، کاربری با نام ';
					$content .= Helper::get_user_full_name( get_current_user_id() );
					$content .= '</p><p>';
					if ( $this_factor['factor_type'] == 1 ) {
						$content .= 'پیش فاکتور ';
					} else {
						$content .= 'فاکتور ';
					}
					$content .= " به شناسه ";
					$content .= $_GET['payment_factor'];
					$content .= " و مبلغ ";
					$content .= number_format( $Amount ) . ' ' . \WP_OnlinePub\Helper::currency() . ' ';
					$content .= 'را موفقیت پرداخت کرد. ';
					$content .= '</p><br /><p>';
					$content .= '</p><br />';
					$content .= '<p>با تشکر</p>';
					$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';
					WP_Online_Pub::send_mail( 'admin', $subject, $content );

					wp_redirect( $url_back );
					exit;
				} else {
					//Problem in Payment
					wp_redirect( $url_back );
					exit;
				}
			} else {
				//Payment is canseled
				wp_redirect( $url_back );
				exit;
			}
		}
	}


}