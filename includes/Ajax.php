<?php

namespace WP_OnlinePub;

class Ajax {


	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		$list_function = array();

		foreach ( $list_function as $method ) {
			add_action( 'wp_ajax_' . $method, array( $this, $method ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $this, $method ) );
		}

	}


}