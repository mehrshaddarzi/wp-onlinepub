<?php

namespace WP_OnlinePub\WP_List_Table;

use WP_Online_Pub;
use WP_OnlinePub\Helper;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Ticket extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'ticket',
			'plural'   => 'tickets',
			'ajax'     => false
		) );
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		//Column Option
		$this->_column_headers = $this->get_column_info();

		//Process Bulk and Row Action
		$this->process_bulk_action();

		//Prepare Data
		$per_page     = $this->get_items_per_page( 'ticket_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		//Create Pagination
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		//return items
		$this->items = self::get_actions( $per_page, $current_page );
	}

	/**
	 * Retrieve Items data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_actions( $per_page = 10, $page_number = 1 ) {
		global $wpdb;

		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_ticket';
		$sql = "SELECT * FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		//Check Order By
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' GROUP by `chat_id` ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		} else {
			$sql .= ' GROUP by `chat_id` ORDER BY `id`';
		}

		//Check Order Fields
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Conditional sql
	 */
	public static function conditional_sql() {
		//Where conditional
		$where = false;

		//Check Search
		if ( isset( $_GET['s'] ) and ! empty( $_GET['s'] ) ) {
			$search  = sanitize_text_field( $_GET['s'] );
			$where[] = "`comment` LIKE '%{$search}%'";
		}

		return $where;
	}

	/**
	 * Delete a action record.
	 *
	 * @param int $id action ID
	 */
	public static function delete_action( $id ) {
		global $wpdb;
		\WP_OnlinePub\Ticket::instance()->remove_ticket( $id );
	}

	/**
	 * Returns the count of records in the database.
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_ticket';
		$sql = "SELECT COUNT(*) FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		$sql .= ' GROUP by `chat_id`';
		return $wpdb->get_var( $sql );
	}

	/**
	 * Not Found Item Text
	 */
	public function no_items() {
		_e( 'هیچ موردی یافت نشد', 'wp-statistics-actions' );
	}

	/**
	 *  Associative array of columns
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'user'    => __( 'نام و نام خانوادگی', 'wp-statistics-actions' ),
			'title'   => __( 'عنوان', 'wp-statistics-actions' ),
			'comment' => __( 'متن', 'wp-statistics-actions' ),
			'date'    => __( 'آخرین بروزرسانی', 'wp-statistics-actions' ),
			'status'  => __( 'وضعیت', 'wp-statistics-actions' )
		);

		return $columns;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['chat_id']
		);
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {

		//Default unknown Column Value
		$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics-actions' ) . '</span>';

		switch ( $column_name ) {
			case 'user' :

				// row actions to edit
				$actions['view'] = '<a class="text-success" href="' . add_query_arg( array( 'page' => 'ticket', 'method' => 'view', 'chat_id' => $item['chat_id'] ), admin_url( "admin.php" ) ) . '">' . __( 'نمایش گفتگو', 'wp-statistics-actions' ) . '</a>';

				// row actions to Delete
				$actions['trash'] = '<a onclick="return confirm(\'آیا مطمئن هستید ؟\')"  href="' . add_query_arg( array( 'page' => 'ticket', 'action' => 'delete', '_wpnonce' => wp_create_nonce( 'delete_action_nonce' ), 'del' => $item['chat_id'] ), admin_url( "admin.php" ) ) . '">' . __( 'حذف', 'wp-statistics-actions' ) . '</a>';

				return '<div>' . Helper::get_user_full_name( $item['user_id'] ) . ' <br /> ' . Helper::get_user_mobile( $item['user_id'] ) . '<br />' . Helper::get_user_email( $item['user_id'] ) . '</div>' . $this->row_actions( $actions );
				break;

			case 'title' :
				return \WP_OnlinePub\Ticket::instance()->GetTitleticker( $item['chat_id'] );
				break;

			case 'comment' :
				return mb_substr( wp_strip_all_tags( strip_shortcodes( $item['comment'] ) ), 0, 80, "utf-8" );
				break;

			case 'date' :
				$date = date_i18n( "l j F Y", strtotime( $item['create_date'] ) );
				return $date;
				break;
			case 'status' :

				$t = '<span class="text-danger">';
				$t .= \WP_OnlinePub\Ticket::instance()->vaziat_ticket( $item['chat_id'], 'admin' );
				$t .= '</span>';
				$t .= '<br>';

				if ( \WP_OnlinePub\Ticket::instance()->is_close_ticket( $item['chat_id'] ) === false ) {
					$t .= '<a href="' . add_query_arg( array( 'page' => 'ticket', 'action_close_ticket' => $item['chat_id'] ), admin_url( "admin.php" ) ) . '">بستن تیکت</a>';
				} else {
					$t .= '<a href="' . add_query_arg( array( 'page' => 'ticket', 'action_open_ticket' => $item['chat_id'] ), admin_url( "admin.php" ) ) . '">باز کردن تیکت</a>';
				}

				return $t;
				break;
		}
	}

	/**
	 * Columns to make sortable.
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'user' => array( 'user_id', true ),
		);

		return $sortable_columns;
	}


	/**
	 * Returns an associative array containing the bulk action
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'حذف', 'wp-statistics-actions' ),
		);

		return $actions;
	}

	/**
	 * Search Box
	 *
	 * @param $text
	 * @param $input_id
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" placeholder="<?php echo __( "متن گفتگو", 'wp-statistics-actions' ); ?>" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" autocomplete="off"/>
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}

	/**
	 * Bulk and Row Actions
	 */
	public function process_bulk_action() {
		global $wpdb;


		//Content Action : Change Status Factor
		if ( isset( $_POST['add_ticket'] ) ) {

			//Save Ticket
			//File
			$attachment = "";
			if ( $_FILES['ticket_attachment']['name'] !== '' ) {
				$attachment = \WP_OnlinePub\Ticket::wp_upload_file( 'ticket_attachment' );
			}

			//No error Sentto Db
			$wpdb->insert(
				"z_ticket",
				array(
					'user_id'     => $_POST['user_id'],
					'title'       => trim( $_POST['ticket_title'] ),
					'create_date' => current_time( 'mysql' ),
					'comment'     => stripslashes( $_POST['ticket_comment'] ),
					'sender'      => 'admin',
					'read_admin'  => 1,
					'read_user'   => 0,
					'file'        => $attachment,
					'chat_id'     => $_POST['chat_id'],
				)
			);

			//push notification
			if ( $_POST['is-notification'] == "yes" ) {

				//Send Sms
				$arg         = array( "order_id" => $_POST['chat_id'], "user_name" => Helper::get_user_full_name( $_POST['user_id'] ) );
				$user_mobile = Helper::get_user_mobile( $_POST['user_id'] );
				if ( $user_mobile != "" ) {
					WP_Online_Pub::send_sms( $user_mobile, '', 'send_to_user_at_create_ticket', $arg );
				}

				//Send Email
				$user_mail = Helper::get_user_email( $_POST['user_id'] );
				if ( $user_mail != "" ) {
					$subject = "گفتگو جدید برای سفارش  " . $_POST['chat_id'];
					$content = '<p>';
					$content .= 'کاربر گرامی ';
					$content .= Helper::get_user_full_name( $_POST['user_id'] );
					$content .= '</p><p>';
					$content .= "گفتگوی جدید از طرف مدیریت در سامانه نشر آنلاین برای شما ایجاد شده است لطفا مشاهده کنید و نسبت به پاسخ آن اقدام نمایید. ";
					$content .= '</p><p>اطلاعات تیکت : </p>';
					$content .= '<p>عنوان گفتگو : ' . trim( $_POST['ticket_title'] ) . '</p>';
					$content .= '<p>متن گفتگو : </p>';
					$content .= '<p>' . stripslashes( $_POST['ticket_comment'] ) . '</p>';
					$content .= '<br /><br />';
					$content .= '<p>با تشکر</p>';
					$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';

					WP_Online_Pub::send_mail( $user_mail, $subject, $content );
				}

			}
			sleep( 1 );

			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'ticket', 'alert' => 'send-ticket' ), admin_url( "admin.php" ) ) ) );
			exit;
		}


		//Close Ticket
		if ( isset( $_GET['action_close_ticket'] ) ) {
			\WP_OnlinePub\Ticket::instance()->close_ticket( $_GET['action_close_ticket'] );
			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'ticket', 'alert' => 'close' ), admin_url( "admin.php" ) ) ) );
			exit;
		}

		//Open Ticket
		if ( isset( $_GET['action_open_ticket'] ) ) {
			$wpdb->delete( "z_ticket_close", array( 'chat_id' => $_GET['action_open_ticket'] ) );
			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'ticket', 'alert' => 'open' ), admin_url( "admin.php" ) ) ) );
			exit;
		}


		// Row Action Delete
		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_action_nonce' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			} else {
				self::delete_action( absint( $_GET['del'] ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'ticket', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}

		//Bulk Action Delete
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) ) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			if ( is_array( $delete_ids ) and count( $delete_ids ) > 0 ) {
				foreach ( $delete_ids as $id ) {
					self::delete_action( $id );
				}

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'ticket', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}


	}

}