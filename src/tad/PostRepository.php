<?php

class tad_PostRepository extends tad_AbstractPostRepository {

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