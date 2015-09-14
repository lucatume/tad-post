<?php


abstract class tad_AbstractPostRepository {

	abstract public static function get_post_type();

	abstract public static function get_post_class();

	/**
	 * @param null $id
	 *
	 * @return bool|tadval_PostProxyInterface
	 */
	public static function create( $id = null ) {
		Arg::_( $id, 'ID' )->is_numeric()->vel()->is_null();

		if ( empty( $id ) ) {
			$id = wp_insert_post( [ 'post_type' => tad_PostRepository::get_post_type() ], true );
			if ( is_wp_error( $id ) ) {
				return false;
			}
		}

		$class = tad_PostRepository::get_post_class();

		return new $class( $id );
	}
}