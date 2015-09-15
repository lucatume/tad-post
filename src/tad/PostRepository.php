<?php

class tad_PostRepository extends tad_AbstractPostRepository implements tad_PostRepositoryInterface {

	/**
	 * @return string
	 */
	public static function get_post_type() {
		return 'post';
	}

	/**
	 * @return string
	 */
	public static function get_post_class() {
		return 'tad_Post';
	}
}