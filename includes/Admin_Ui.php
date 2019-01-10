<?php

namespace WP_OnlinePub;

class Admin_Ui {

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
	 * WP List Table static
	 *
	 * @param $obj
	 * @param $icon
	 * @param $title
	 * @param array $add_new_button
	 * @param bool $search
	 */
	public static function wp_list_table( $obj, $icon, $title, $add_new_button = array(), $search = false ) {
		?>
        <div class="wrap wps_actions">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-<?php echo $icon; ?>"></span> <?php echo $title; ?>
            </h1>
			<?php
			if ( count( $add_new_button ) > 0 ) {
				echo '<a href="' . $add_new_button['link'] . '" class="page-title-action">' . $add_new_button['name'] . '</a>';
			}
			?>
            <hr class="wp-header-end">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns">
                    <div>
                        <div class="meta-box-sortables ui-sortable">
							<?php $obj->views(); ?>
                            <form method="post" action="<?php echo remove_query_arg( array( 'alert' ) ); ?>">
								<?php
								if ( $search != false ) {
									$obj->search_box( __( "Search" ), 'nds-user-find' );
								}
								$obj->display();
								?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
		<?php

	}


}