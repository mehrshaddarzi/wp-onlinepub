<?php

namespace WP_OnlinePub;

class Helper {

	/**
	 * Get User Mobile
	 * @param bool $user_id
	 * @return
	 */
	public static function get_user_mobile( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return get_user_meta( $user_id, 'mobile_phone', true );
	}

	/**
	 * Get User email
	 *
	 * @param bool $user_id
	 * @return string
	 */
	public static function get_user_email( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		//setup user data
		$user_info = get_userdata( $user_id );
		return $user_info->user_email;
	}

	/**
	 * Get User Name
	 *
	 * @param bool $user_id
	 * @return string
	 */
	public static function get_user_full_name( $user_id = false ) {
		$user_info = get_userdata( $user_id );

		//check display name
		if ( $user_info->display_name != "" ) {
			return $user_info->display_name;
		}

		//Check First and Last name
		if ( $user_info->first_name != "" ) {
			return $user_info->first_name . " " . $user_info->last_name;
		}

		//return Username
		return $user_info->user_login;
	}

}