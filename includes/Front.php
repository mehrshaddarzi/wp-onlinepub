<?php

namespace WP_OnlinePub;


class Front {

	/**
	 * constructor.
	 */
	public function __construct() {

		//ShortCode List Order User
		add_shortcode( 'user-order', array( $this, 'user_order_list' ) );
	}

	/**
	 * User Order List
	 */
	public function user_order_list() {
		global $wpdb;

		//Create Empty Text Object
		$text = '<div class="user-order">';


		//Show Custom Order Detail


		//Show All Factor
		if ( ! isset( $_GET['order_id'] ) ) {


			//Get List Factor
			$user_id = 1000;
			$query = $wpdb->get_results( "SELECT * FROM `z_order` WHERE `user_id` = $user_id ORDER BY `id` DESC" , ARRAY_A);
			if(count($query) >0) {

				$text = '<div id="sticky-list-wrapper_12" class="sticky-list-wrapper">';
				$text .= '<table class="sticky-list">
<thead>
<tr>
<th class="sort header-تاریخ" data - sort = "sort-0" > شناسه</th >
<th class="sort header-تاریخ" data - sort = "sort-0" > تاریخ</th >
<th class="sort header-نوع-سفارش" data - sort = "sort-1" > نوع سفارش </th >
<th class="sort header-عنوان-سفارش" data - sort = "sort-2" > عنوان سفارش </th >
<th class="sort header-وضعیت" data - sort = "sort-3" > وضعیت</th >
<th class="sort header-وضعیت" data - sort = "sort-3" ></th >
</tr>
</thead>
<tbody class="list">
';

				foreach ( $query as $row )
				{

				}

				$text .= '</tbody ></table ></div >';
				$text .= '</div>';

			} else {

				echo '<div style="text-align:center;">شما هیچ سفارشی تا بحال ثبت نکرده اید</div>';

			}




		}


		$text .= '</div>';
		return $text;
	}


}