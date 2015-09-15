<?php


interface tad_PostRepositoryInterface {

	/**
	 * @param null $id
	 *
	 * @return bool|tadval_PostProxyInterface
	 */
	public static function create( $id = null );

	/**
	 * @return string
	 */
	public static function get_post_type();

	/**
	 * @return string
	 */
	public static function get_post_class();
}