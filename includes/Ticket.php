<?php

namespace WP_OnlinePub;

class Ticket {

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
	 * Table Name
	 */
	public static $tbl_prefix;

	/**
	 * Asset Link
	 */
	public static $asset;

	/**
	 * Ticket constructor.
	 */
	public function __construct() {

		//Set Table Prefix
		self::$tbl_prefix = 'z_';

		//set Asset Link
		self::$asset = \WP_Online_Pub::$plugin_url;

		//Setup List Ajax function
		$ajax_function = array(
			'ticket_system',
			'ticket_send_to_mysql',
			'showchat',
		);

		foreach ( $ajax_function as $method ) {
			add_action( 'wp_ajax_' . $method, array( $this, $method ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $this, $method ) );
		}

	}

	/*
	 * Get title Of Chat
	 */
	public function GetTitleticker( $chat_id ) {
		global $wpdb;
		$row = $wpdb->get_row( "SELECT * FROM `" . self::$tbl_prefix . "ticket` WHERE `chat_id` = " . $chat_id . " ORDER BY `id` ASC LIMIT 1", ARRAY_A );
		return $row['title'];
	}


	/*
	 * Is close Ticket
	 */
	public function is_close_ticket( $chat_id ) {
		global $wpdb;
		//Close ticket
		if ( $wpdb->get_var( "SELECT COUNT(*) FROM `" . self::$tbl_prefix . "ticket_close` WHERE `chat_id` = " . $chat_id ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Show Vaziat Ticket
	 */
	public function vaziat_ticket( $chat_id, $type_user = "user" ) {
		global $wpdb;

		//Close ticket
		if ( $this->is_close_ticket( $chat_id ) === true ) {
			return 'بسته شده';
		} else {
			$row = $wpdb->get_row( "SELECT * FROM `" . self::$tbl_prefix . "ticket` WHERE `chat_id` = " . $chat_id . " ORDER BY `id` DESC LIMIT 1", ARRAY_A );

			//Dar Entezare Pashokh
			if ( $row['sender'] == "user" and $row['read_admin'] == "0" ) {
				return 'در انتظار پاسخ';
			}

			//pasokh dade shod
			if ( $row['sender'] == "admin" and $row['read_admin'] == "1" ) {
				return 'پاسخ داده شد';
			}

			if ( $row['sender'] == "user" and $row['read_admin'] == "1" ) {
				return 'توسط مدیریت دیده شد';
			}
		}

	}


	/*
	 *close Ticket
	 */
	public function close_ticket( $chat_id ) {
		global $wpdb;
		$wpdb->insert(
			self::$tbl_prefix . "ticket_close",
			array(
				'chat_id' => $chat_id
			)
		);
	}


	/*
	 * Remove Ticket
	 */
	public function remove_ticket( $chat_id ) {
		global $wpdb;

		$wpdb->delete( self::$tbl_prefix . "ticket_close", array( 'chat_id' => $chat_id ) );
		$q = $wpdb->get_results( "SELECT * FROM `" . self::$tbl_prefix . "ticket` WHERE `chat_id` = " . $chat_id, ARRAY_A );
		foreach ( $q as $item ) {
			if ( $item['file'] !== "" ) {
				wp_delete_attachment( $item['file'], true );
			}
			$wpdb->delete( self::$tbl_prefix . "ticket", array( 'id' => $item['id'] ) );
		}
	}

	/**
	 *
	 * Upload file in Wordpress
	 *
	 * @param $file_id
	 * @return bool
	 */
	public static function wp_upload_file( $file_id ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		$attachment_id = media_handle_upload( $file_id, 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		} else {
			//add to Not Show in attachment list
			$opt = get_option( "hide_attachment_list" );
			if ( $opt ) {
				$opt[] = $attachment_id;
				update_option( "hide_attachment_list", $opt );
			}
			return $attachment_id;
		}
	}

	/*
	 * Ticket to databse
	 */
	public function ticket_send_to_mysql() {
		global $wpdb;
		if ( isset( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			//File
			$attachment = "";
			if ( $_FILES['ticket_attachment']['name'] !== '' ) {
				$attachment = self::wp_upload_file( 'ticket_attachment' );
			}

			//chat Id
			if ( $_POST['chat_id'] == "0" ) {
				$chatid = current_time( 'timestamp' );
			} else {
				$chatid = $_POST['chat_id'];
			}

			//No error Sentto Db
			$wpdb->insert(
				self::$tbl_prefix . "ticket",
				array(
					'user_id'     => get_current_user_id(),
					'title'       => trim( $_POST['ticket_title'] ),
					'create_date' => current_time( 'mysql' ),
					'comment'     => $_POST['ticket_comment'],
					'sender'      => 'user',
					'read_admin'  => 0,
					'read_user'   => 1,
					'file'        => $attachment,
					'chat_id'     => $chatid,
				)
			);

			echo "true";
			exit;
		}
		die();
	}


	/*
	 * Show chat
	 */
	public function showchat( $chat_id = false ) {
		global $wpdb;
		//if ( isset( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$result = array();

		if ( $chat_id === false ) {
			$chat_id = $_POST['chat_id'];
		}

		$export = '<div style="padding:30px; padding-top:8px;">
            <style>
            .log span { color:#d72626 !important; display:inline !important; }
            label { width: 100px;}
            .buttonText { font-size:11px; }
            label[for=ticket_attachment] { width:130px; }
            .media-body p { padding:0px; }
            </style>
            ';

		//BackTo Page
		//$export .= '<div class="text-left text-danger" id="back_to_main_ticket" style="cursor: pointer;">بازگشت <i class="fa fa-arrow-left"></i></div>';


		//List chat
		$query = $wpdb->get_results( "SELECT * FROM  `" . self::$tbl_prefix . "ticket` WHERE `chat_id` = $chat_id ORDER BY `id` ASC", ARRAY_A );
		if ( count( $query ) > 0 ) {

			$export .= '
	<div style="margin-top:4px; margin-bottom:10px; border-bottom:1px solid #e3e3e3; padding-bottom:5px;">
	<i class="fa fa-inbox"></i> تاریخچه گفتگو &nbsp;<span class="text-muted">(عنوان : ' . Ticket::instance()->GetTitleticker( $chat_id ) . ')</span>
</div>';

			foreach ( $query as $item ) {

				//Type
				$attachment = '';
				$class      = 'right';
				$icon       = "user";
				if ( $item['sender'] == "admin" ) {
					$icon  = "admin";
					$class = 'left';
				}

				$thumbnil = '
                  <div class="media-' . $class . '" style="padding:0px;">
                  <img class="media-object" style="margin-' . ( $item['sender'] == "admin" ? "right" : "left" ) . ': 10px;" src="' . self::$asset . '/asset/chat/' . $icon . '_chat.png" alt="#">
                  </div>
                    ';

				//attachment
				if ( $item['file'] !== "" ) {
					$attachment = '<p class="text-left font-11"><a href="' . wp_get_attachment_url( $item['file'] ) . '" target="_blank" ' . ( $item['sender'] == "user" ? 'class="text-primary"' : 'style="color:#fff"' ) . '><i class="fa fa-download"></i> ' . basename( get_attached_file( $item['file'] ) ) . '</a></p>';
				}

				//Readded All commet for User
				$wpdb->update(
					self::$tbl_prefix . "ticket",
					array(
						'read_user' => 1
					),
					array( 'id' => $item['id'] )
				);

				$export .= '
                    <div class="media" style="margin-bottom: 15px; width:85%; float:' . ( $item['sender'] == "admin" ? "left" : "right" ) . ';">
                  ' . ( $item['sender'] == "user" ? $thumbnil : "" ) . '
                  <div class="media-body" style="width: 95%; padding-right: 15px;background: ' . ( $item['sender'] == "user" ? '#f2f2f2' : "#25ae88; color:#fff;" ) . ';padding: 10px;border-radius: 5px;padding-left: 15px;">
                    <p class="rtl text-right font-11 ' . ( $item['sender'] == "user" ? 'text-danger' : "" ) . '">ارسال شده در تاریخ ' . date_i18n( 'Y/m/d ساعت H:i:s', $item['create_date'] ) . '</p>
                    <p class="rtl text-right font-11">' . $item['comment'] . '</p>
                    ' . $attachment . '
                  </div>
                  ' . ( $item['sender'] == "admin" ? $thumbnil : "" ) . '
                </div>
                <div class="clearfix"></div>
                    ';

			}

			$export .= '<div style="height:15px;"></div>';
		}

		if ( count( $query ) > 0 ) {
			$export .= '
			<div style="margin-top:20px; margin-bottom:10px; border-bottom:1px solid #e3e3e3; padding-bottom:5px;">
			<i class="fa fa-list-alt"></i> ارسال پاسخ
			</div>';
		}


		//check ticket is close
		if ( Ticket::instance()->is_close_ticket( $chat_id ) === false ) {

			$export .= '
<div class="loading-form-login"></div>
<div class="login-chilan">
<div class="group-input">

<form method="post" action="#" enctype="multipart/form-data" id="send_ticket">
<input type="hidden" name="chat_id" value="' . $chat_id . '">

<div class="form-group form-inline" style="' . ( count( $query ) > 0 ? 'display: none' : '' ) . '">
<label style="vertical-align: top; width: 120px;display: inline-block;">عنوان پیام </label>
<input type="hidden" name="ticket_title" value="">
</div>


<div class="form-group form-inline">
<label style="vertical-align: top; width: 120px;display: inline-block;">متن پیام </label>
<textarea style="    border: 1px solid #d6d6d6; font-size: 12px; min-height:150px; width: 320px;" name="ticket_comment" class="form-control rtl input-group" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'لطفا فیلد را پر کنید\')" required="required"></textarea>
</div>


<div class="form-group form-inline">
<label style="vertical-align: top;    width: 120px;display: inline-block;">فایل ضمیمه </label>
<input type="file" id="ticket_attachment" name="ticket_attachment" class="form-control ltr input-group filestyle" data-file="input-file">
<span style="margin-right:10px; font-size: 11px; display:block; color: #635555;" class="font-11">
حداکثر حجم فایل : 5 مگابایت , پسوند های قابل قبول شامل Zip,jpg,pdf
</span>

</div>

<div class="form-group form-inline">
<label></label>
<br />
<input class="btn btn-default" id="send-user-ticket" value="ارسال تیکت" type="submit" style="font-size:11px;">
</div>

</form>

</div>
</div>';
		} else {
			$export .= "<div style='margin-top:10px;'>این گفتگو بسته شده است</div>";
		}


		$export .= '</div>';
		return $export;
		//$result['html'] = $export;
		//wp_send_json( $result );
		//exit;
		//}
		//die();
	}

	/*
	 * Main View Ticket
	 */
	public function ticket_system() {
		global $wpdb;
		if ( isset( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$result = array();

			$export = '<div style="padding:30px; padding-top:8px;">
            <style>
            .log span { color:#d72626 !important; display:inline !important; }
            label { width: 100px;}
            .buttonText { font-size:11px; }
            label[for=ticket_attachment] { width:130px; }
            </style>
            ';

			//List ticket ha
			$user_id = get_current_user_id();
			$query   = $wpdb->get_results( "SELECT * FROM  `" . self::$tbl_prefix . "ticket` WHERE `user_id` = $user_id GROUP BY `chat_id` ORDER BY `id` DESC", ARRAY_A );
			if ( count( $query ) > 0 ) {

				$export .= '
	<div style="margin-top:20px; margin-bottom:10px; border-bottom:1px solid #e3e3e3; padding-bottom:5px;">
	<i class="fa fa-inbox"></i> صندوق پیام
	</div>';

				$export .= '
	<table class="table table-striped table-hover table-bordered" style="width: 100%;font-size: 12px;margin: 10px auto;">
	<tr>
	<td width="50" style="vertical-align:middle; text-align:center;">ردیف</td>
	<td style="vertical-align:middle; text-align:center;">شناسه گفتگو</td>
	<td style="vertical-align:middle; text-align:center;">تاریخ</td>
	<td style="vertical-align:middle; text-align:center;">عنوان</td>
	<td style="vertical-align:middle; text-align:center;">وضعیت</td>
	<td style="vertical-align:middle; text-align:center;">فایل ضمیمه</td>
	</tr>';
				$x      = 1;

				foreach ( $query as $item ) {

					$attachment = "-";
					if ( $item['file'] !== "" ) {
						$attachment = '<a href="' . wp_get_attachment_url( $item['file'] ) . '" target="_blank"><i class="fa fa-download" style="font-size: 16px;color: #5d5959;"></i></a>';
					}

					$export .= '
                        <tr>
                        <td style="vertical-align:middle; text-align:center;">' . $x . '</td>
                        <td style="vertical-align:middle; text-align:center;">' . $item['chat_id'] . '</td>
                        <td style="vertical-align:middle; text-align:center;"><span class="">' . date_i18n( 'Y/m/d ساعت H:i:s', $item['create_date'] ) . '</span></td>
                        <td style="vertical-align:middle; text-align:center;"><span style="cursor: pointer" class="text-primary" data-show-ticket="' . $item['chat_id'] . '">' . $item['title'] . '</span></td>
                        <td style="vertical-align:middle; text-align:center;"><span class="text-warning">' . Ticket::instance()->vaziat_ticket( $item['chat_id'], "user" ) . '</span></td>
                        <td style="vertical-align:middle; text-align:center;">' . $attachment . '</td>
                        </tr>';
					$x ++;
				}

				$export .= '</table>';
				$export .= '<div style="height:15px;"></div>';
			}


			$export .= '
	<div style="margin-top:20px; margin-bottom:10px; border-bottom:1px solid #e3e3e3; padding-bottom:5px;">
	<i class="fa fa-list-alt"></i> ارسال تیکت
	</div>';


			$export .= '
<div class="loading-form-login"></div>
<div class="login-chilan">
<div class="group-input">

<form method="post" action="#" enctype="multipart/form-data" id="send_ticket">

<input type="hidden" name="chat_id" value="0">

<div class="form-group form-inline">
<label>عنوان پیام  <span class="text-danger">*</span></label>
<input type="text" name="ticket_title" style="width: 300px;" class="form-control rtl input-group" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'لطفا فیلد را پر کنید\')" required="required">
</div>


<div class="form-group form-inline">
<label>متن پیام  <span class="text-danger">*</span></label>
<textarea style="font-size: 12px; min-height:150px; width: 300px;" name="ticket_comment" class="form-control rtl input-group" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'لطفا فیلد را پر کنید\')" required="required"></textarea>
</div>


<div class="form-group form-inline">
<label>فایل ضمیمه </label>
<input type="file" id="ticket_attachment" name="ticket_attachment" class="form-control ltr input-group filestyle" data-file="input-file">
<span style="margin-right:10px;" class="font-11">
حداکثر حجم فایل : 5 مگابایت , پسوند های قابل قبول شامل Zip,jpg,pdf
</span>

</div>

<div class="form-group form-inline">
<label></label>
<input class="btn btn-default" id="send-user-ticket" value="ارسال تیکت" type="submit" style="font-size:11px;">
</div>

</form>

</div>
</div>';


			$export .= '</div>';


			$result['html'] = $export;
			wp_send_json( $result );
			exit;
		}
		die();
	}


}