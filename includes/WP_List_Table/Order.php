<?php

namespace WP_OnlinePub\WP_List_Table;

use WP_Online_Pub;
use WP_OnlinePub\Admin_Page;
use WP_OnlinePub\Admin_Ui;
use WP_OnlinePub\Gravity_Form;
use WP_OnlinePub\Helper;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Order extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'order',
			'plural'   => 'orders',
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
		$per_page     = $this->get_items_per_page( 'order_per_page', 10 );
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
		$tbl = 'z_order';
		$sql = "SELECT * FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			//$join = "INNER JOIN `".$wpdb->prefix."users` ON `".$wpdb->prefix."users`.`ID` = `$tbl`.`user_id` WHERE `$tbl`.`title` LIKE '%{$search}%' OR `".$wpdb->prefix."users`.`display_name` LIKE '%{$search}%';";
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		//Check Order By
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		} else {
			$sql .= ' ORDER BY `id`';
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
			$search  = $_GET['s'];
			$where[] = "`title` LIKE '%{$search}%'";
		}

		return $where;
	}

	/**
	 * Delete a action record.
	 * @param int $id action ID
	 */
	public static function delete_action( $id ) {
		global $wpdb;

		//Get Order Detail
		$order = Helper::get_order( $id );

		//Remove Entry Gravity
		Gravity_Form::remove_entry( $order['entry_id'] );

		//Remove all factor and Factor item
		$query = $wpdb->get_results( "SELECT * FROM `z_factor` WHERE `order_id` = $id ORDER BY `id` DESC", ARRAY_A );
		if ( count( $query ) > 0 ) {
			foreach ( $query as $row ) {
				Helper::remove_factor( $row['id'] );
			}
		}

		//Remove All Ticket
		Helper::remove_ticket( $id );

		//Remove From Table
		$tbl = 'z_order';
		$wpdb->delete( $tbl, array( 'id' => $id ), array( '%d' ) );

	}


	/**
	 * Returns the count of records in the database.
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_order';
		$sql = "SELECT COUNT(*) FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Not Found Item Text
	 */
	public function no_items() {
		_e( 'هیچ سفارشی ای یافت نشد', '' );
	}

	/**
	 *  Associative array of columns
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => __( 'عنوان سفارش', 'wp-statistics-actions' ),
			'date'   => __( 'تاریخ ایجاد', 'wp-statistics-actions' ),
			'user'   => __( 'مشخصات کاربر', 'wp-statistics-actions' ),
			'status' => __( 'وضعیت', 'wp-statistics-actions' ),
			'factor' => __( 'فاکتور', 'wp-statistics-actions' ),
			'ticket' => __( 'پشتیبانی', 'wp-statistics-actions' ),
		);

		return $columns;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
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
		global $wpdb;

		//Default unknown Column Value
		$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics-actions' ) . '</span>';

		switch ( $column_name ) {
			case 'title' :

				// row actions to ID
				$actions['id'] = '<span class="text-muted">#شناسه ' . $item['id'] . '</span>';

				// row actions to edit
				//$actions['edit'] = '<a href="' . add_query_arg( array( 'page' => WP_Statistics_Actions::admin_slug, 'method' => 'edit', 'ID' => $item['ID'] ), admin_url( "admin.php" ) ) . '">' . __( 'Edit', 'wp-statistics-actions' ) . '</a>';

				//Row Action to Clone
				$actions['view'] = '<a href="' . admin_url() . 'admin.php?page=gf_entries&view=entry&id=' . Gravity_Form::$order_form_id . '&lid=' . $item['entry_id'] . '&order=ASC&filter&paged=1&pos=0&field_id&operator" target="_blank" class="text-success">' . __( 'نمایش جزئیات', 'wp-statistics-actions' ) . '</a>';

				// row actions to Delete
				$actions['trash'] = '<a onclick="return confirm(\'آیا مطمئن هستید ؟\')" href="' . add_query_arg( array( 'page' => 'order', 'action' => 'delete', '_wpnonce' => wp_create_nonce( 'delete_action_nonce' ), 'del' => $item['id'] ), admin_url( "admin.php" ) ) . '">' . __( 'حذف', 'wp-statistics-actions' ) . '</a>';

				return $item['title'] . $this->row_actions( $actions );
				break;

			case 'date' :
				$date                   = date_i18n( "j F Y", strtotime( $item['date'] ) );
				$actions['create_time'] = date_i18n( "H:i:s", strtotime( $item['date'] ) );

				return $date . $this->row_actions( $actions );
				break;
			case 'user' :

				return '<div>' . Helper::get_user_full_name( $item['user_id'] ) . ' <br /> ' . Helper::get_user_mobile( $item['user_id'] ) . '<br />' . Helper::get_user_email( $item['user_id'] ) . '</div>';
				break;
			case 'status' :

				return '<span class="text-danger">' . Helper::show_status( $item['status'] ) . '</span> <br/><a href="' . Admin_Page::admin_link( 'order', array( 'top' => 'change-status', 'status' => $item['status'], 'order_id' => $item['id'] ) ) . '">تغییر وضعیت</a>';
				break;
			case 'factor' :

				$link_show_factor = Admin_Page::admin_link( 'factor', array( "order" => $item['id'] ) );
				$create_factor    = Admin_Page::admin_link( 'factor', array( "method" => "add", "order_id" => $item['id'] ) );
				return '<a target="_blank" class="text-success" href="' . $link_show_factor . '">تعداد فاکتور : ' . Helper::get_number_factor_for_order( $item['id'] ) . '</a><br><a target="_blank" href="' . $create_factor . '"> ایجاد فاکتور جدید</a>';
				break;
			case 'ticket' :

				$create_ticket = Admin_Page::admin_link( 'ticket', array( "method" => "add", "user_id" => $item['user_id'], "order_id" => $item['id'] ) );

				$count_ticket = $wpdb->get_var( "SELECT COUNT(*) FROM `z_ticket` WHERE `chat_id` =" . $item['id'] );
				if ( $count_ticket > 0 ) {
					return '<a target="_blank" class="text-warning" href="' . Admin_Page::admin_link( 'ticket', array( "method" => "view", "chat_id" => $item['id'] ) ) . '">نمایش گفتگو </a><br>';

				} else {
					return '<a target="_blank" href="' . $create_ticket . '"> ایجاد گفتگو جدید</a>';

				}

				break;
		}
	}

	/**
	 * Columns to make sortable.
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'date' => array( 'date', false ),
			'user' => array( 'user_id', false )
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
            <input type="search" placeholder="<?php echo __( "جستجو عنوان سفارش", 'wp-statistics-actions' ); ?>" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" autocomplete="off"/>
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}

	/**
	 * Bulk and Row Actions
	 */
	public function process_bulk_action() {
		global $wpdb;

		//Content Action : Change Status Order
		if ( isset( $_POST['new-status'] ) ) {

			//push notification
			$is_push_notification = false;
			if ( $_POST['is-notification'] == "yes" ) {
				$is_push_notification = true;
			}

			//change status
			Helper::change_status_order( $_POST['order_id'], $_POST['new-status'], $is_push_notification );
			sleep( 1 );

			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'order', 'alert' => 'change-status' ), admin_url( "admin.php" ) ) ) );
			exit;
		}


		// Row Action Delete
		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_action_nonce' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			} else {
				self::delete_action( absint( $_GET['del'] ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'order', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
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

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'order', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}

	}

}