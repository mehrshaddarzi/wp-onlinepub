<?php

namespace WP_OnlinePub\WP_List_Table;

use WP_OnlinePub\Admin_Page;
use WP_OnlinePub\Helper;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Payment extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'payment',
			'plural'   => 'payments',
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
		$per_page     = $this->get_items_per_page( 'payment_per_page', 10 );
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
		$tbl = 'z_payment';
		$sql = "SELECT * FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
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
			$search  = sanitize_text_field( $_GET['s'] );
			$where[] = "`factor_id` LIKE '%{$search}%'";
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
		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_payment';
		$wpdb->delete( $tbl, array( 'id' => $id ), array( '%d' ) );
	}


	/**
	 * Returns the count of records in the database.
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_payment';
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
		_e( 'هیچ پرداختی در دسترس نیست.', 'wp-statistics-actions' );
	}

	/**
	 *  Associative array of columns
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'user'    => __( 'نام پرداخت کننده', 'wp-statistics-actions' ),
			'date'    => __( 'تاریخ پرداخت', 'wp-statistics-actions' ),
			'type'    => __( 'نوع پرداخت', 'wp-statistics-actions' ),
			'factor'  => __( 'برای فاکتور', 'wp-statistics-actions' ),
			'price'   => __( 'مبلغ ' . Helper::currency(), 'wp-statistics-actions' ),
			'status'  => __( 'وضعیت', 'wp-statistics-actions' ),
			'comment' => __( 'توضیحات', 'wp-statistics-actions' )
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

		//Default unknown Column Value
		$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics-actions' ) . '</span>';

		switch ( $column_name ) {
			case 'user' :

				$actions['id'] = '<span class="text-muted">' . $item['id'] . '#</span>';

				// row actions to Delete
				if ( $item['status'] == 1 ) {
					$actions['trash'] = '<a onclick="return confirm(\'آیا مطمئن هستید ؟\')" href="' . add_query_arg( array( 'page' => 'payment', 'action' => 'delete', '_wpnonce' => wp_create_nonce( 'delete_action_nonce' ), 'del' => $item['id'] ), admin_url( "admin.php" ) ) . '">' . __( 'حذف مشخصات این پرداخت', 'wp-statistics-actions' ) . '</a>';
				}

				return '<div>' . Helper::get_user_full_name( $item['user_id'] ) . ' <br /> ' . Helper::get_user_mobile( $item['user_id'] ) . '<br />' . Helper::get_user_email( $item['user_id'] ) . '</div>' . $this->row_actions( $actions );
				break;

			case 'date' :
				$date                   = date_i18n( "j F Y", strtotime( $item['date'] ) );
				$actions['create_time'] = date_i18n( "H:i:s", strtotime( $item['date'] ) );

				return $date . $this->row_actions( $actions );
				break;
			case 'type' :

				return '<span class="text-danger">' . Helper::get_type_payment( $item['type'] ) . '</span>';
				break;
			case 'factor' :

				return '<span class="text-primary">' . $item['factor_id'] . '</span><br /><a href="' . admin_url( 'admin.php?page=factor&s=' . $item['factor_id'] ) . '" target="_blank">مشاهده جزئیات</a>';
				break;
			case 'price' :

				return number_format_i18n( $item['price'] ) . ' ' . Helper::currency();
				break;
			case 'status' :

				$t   = Helper::get_payment_status( $item['status'] ) . '<br>';
				$p_d = Helper::get_order_by_payment( $item['id'] );
				if ( $item['status'] == 1 ) {
					$t .= '<a target="_blank" href="' . Admin_Page::admin_link( 'factor', array( 'top' => 'change-payment-status', 'order_id' => $p_d['order']['id'], 'order_status' => $p_d['order']['status'], 'status' => $p_d['factor']['payment_status'], 'factor_id' => $item['factor_id'], 'payment_id' => $item['id'] ) ) . '">تغییر وضعیت</a>';
				}
				return $t;
				break;
			case 'comment' :

					if ( $item['comment'] == "" ) {
						return $unknown;
					} else {
						$comment = Helper::get_serialize( $item['comment'] );
						if ( $item['type'] == 1 ) {
							if ( isset( $comment['payid'] ) ) {
								return '
                                  <span>شناسه پرداخت : ' . Helper::show_value( $comment['payid'] ) . '</span>
                                ';
							}
						} else {
							return '
                                  <span>شماره فیش واریزی : ' . Helper::show_value($comment['fish'] ). '</span><br />
                                  <span>تاریخ پرداخت : ' . Helper::show_value($comment['date']) . '</span><br />
                                ';
						}
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
			'user' => array( 'user_id', false ),
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
            <input type="search" placeholder="<?php echo __( "شماره فاکتور", 'wp-statistics-actions' ); ?>" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" autocomplete="off"/>
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}

	/**
	 * Bulk and Row Actions
	 */
	public function process_bulk_action() {

		// Row Action Delete
		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_action_nonce' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			} else {
				self::delete_action( absint( $_GET['del'] ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'payment', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
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

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'payment', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}

	}

}