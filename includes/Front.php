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

		echo '<div id="sticky-list-wrapper_12" class="sticky-list-wrapper"><table class="sticky-list"><thead><tr><th class="sort header-تاریخ" data-sort="sort-0">تاریخ</th><th class="sort header-نوع-سفارش" data-sort="sort-1">نوع سفارش</th><th class="sort header-عنوان-سفارش" data-sort="sort-2">عنوان سفارش</th><th class="sort header-وضعیت" data-sort="sort-3">وضعیت</th></tr></thead><tbody class="list"><tr class="is_read"><td class="sort-0  stickylist-hidden">۱۹/۱۰/۱۳۹۷</td><td class="sort-1  stickylist-select">طراحی و صفحه آرایی کتاب</td><td class="sort-2  stickylist-text">عنوان سفارش من</td><td class="sort-3  stickylist-select">درحال بررسی</td></tr></tbody></table></div>';





	}


}