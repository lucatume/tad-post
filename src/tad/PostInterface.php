<?php


interface tad_PostInterface {

	/**
	 * Gets a value from the post object.
	 *
	 * If the key does not refer to a defined WP_Post property then a single meta with that name will be fetched.
	 *
	 * @param $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	public function get( $key, $default = null );

	/**
	 * Sets a value on the post object.
	 *
	 * If the key does not refer to a defined WP_Post property than the value will be assigned to the post as
	 * a meta value.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value );

	/**
	 * Set or append a set of terms that are assigned to the post for the specified taxonomy.
	 *
	 * @param            $terms
	 * @param            $taxonomy
	 */
	public function set_terms( $terms, $taxonomy );

	/**
	 * Retrieves an array of terms assigned to the post for the specified taxonomy.
	 *
	 * @param       $taxonomy
	 * @param array $default An array of default taxonomy terms to return.
	 *
	 * @return array
	 */
	public function get_terms( $taxonomy, $default = array() );

	/**
	 * Append or set the meta for the post.
	 *
	 * @param string $key
	 * @param array  $values
	 *
	 */
	public function set_meta( $key, array $values );

	/**
	 * Get the meta for the post
	 *
	 * @param            $key
	 * @param            $single
	 *
	 * @param array      $default
	 *
	 * @return array|mixed|string
	 */
	public function get_meta( $key, $single, $default = array() );

	/**
	 * Fetches the terms assigned to the post for each taxonomy registered for the post.
	 *
	 * @param bool|false $force Whether to force or not a database read. Will override modifications.
	 */
	public function fetch_terms( $force = false );

	/**
	 * Triggers the reading of the custom post fields from the database
	 *
	 * @param bool|false $force Whether to force or not a database read. Will override modifications.
	 */
	public function fetch_meta( $force = false );

	/**
	 * Persists the changes made to the post to the database.
	 */
	public function sync();

	/**
	 * Rolls back the post data, meta and terms to the original state.
	 */
	public function rollback();

	/**
	 * Rolls back the post data to the state on the database.
	 */
	public function rollback_data();

	/**
	 * Rolls back the post meta to the state on the database.
	 */
	public function rollback_meta();

	/**
	 * Rolls back the post terms to the state on the database.
	 */
	public function rollback_terms();

	/**
	 * @return array
	 */
	public function get_single_meta_keys();

	/**
	 * @return array
	 */
	public function get_single_term_keys();

	/**
	 * @return array
	 */
	public function get_column_aliases();
}