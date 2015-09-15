<?php


class tad_Post implements tad_PostInterface {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var array|null|WP_Post
	 */
	protected $post;

	/**
	 * @var array
	 */
	protected $postarr;

	/**
	 * @var array
	 */
	protected $post_meta = array();

	/**
	 * @var array
	 */
	protected $terms = array();

	/**
	 * @var
	 */
	protected $did_fetch_meta = false;

	/**
	 * @var bool
	 */
	protected $did_fetch_terms = false;

	/**
	 * @var bool
	 */
	protected $post_updated = false;

	/**
	 * @var bool
	 */
	protected $meta_updated = false;

	/**
	 * @vari bool
	 */
	protected $terms_updated = false;

	/**
	 * @var WP_Post
	 */
	protected $original_post;

	/**
	 * @var bool
	 */
	private $new;

	/**
	 * tadval_LogInterface constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id, $new = false ) {
		Arg::_( $id, 'ID' )->is_numeric();

		$this->id      = $id;
		$this->post    = get_post( $id );
		$this->postarr = (array) $this->post;
		$this->new     = $new;
	}

	public function __destruct() {
		if ( $this->new ) {
			wp_delete_post( $this->id, true );
		}
	}

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
	public function get( $key, $default = null ) {
		$this->fetch_meta();
		$this->fetch_terms();

		if ( array_key_exists( $key, $this->postarr ) || $alias = array_key_exists( $key, $this->get_column_aliases() ) ) {
			if ( ! empty( $alias ) ) {
				$key = $this->get_column_from_alias( $key );
			}

			return $this->post->$key;
		}
		if ( array_key_exists( $key, $this->post_meta ) ) {
			$single = in_array( $key, $this->get_single_meta_keys() );

			return $this->get_meta( $key, $single, $default );
		}
		if ( array_key_exists( $key, $this->terms ) ) {
			$single = in_array( $key, $this->get_single_term_keys() );
			$terms  = $this->get_terms( $key, $default );

			return $single ? reset( $terms ) : $terms;
		}

		return $default;
	}

	/**
	 * Sets a value on the post object.
	 *
	 * If the key does not refer to a defined WP_Post property than the value will be assigned to the post as
	 * a meta value.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ) {
		return $this->__set( $key, $value );
	}

	/**
	 * Explicit magic method implementation.
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value ) {
		$this->fetch_meta();
		$this->fetch_terms();

		if ( array_key_exists( $key, $this->postarr ) || $alias = array_key_exists( $key, $this->get_column_aliases() ) ) {
			if ( ! empty( $alias ) ) {
				$key = $this->get_column_from_alias( $key );
			}
			if ( in_array( $key, $this->get_write_protected_data_fields() ) ) {
				return;
			}
			$this->post->$key   = $value;
			$this->post_updated = true;
		} else if ( array_key_exists( $key, $this->terms ) ) {
			$this->set_terms( $value, $key );
			$this->terms_updated = true;
		} else {
			$this->set_meta( $key, $value );
			$this->meta_updated = true;
		}
	}

	/**
	 * Explicit magic method implementation.
	 *
	 * @param      $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Set or append a set of terms that are assigned to the post for the specified taxonomy.
	 *
	 * @param            $terms
	 * @param            $taxonomy
	 */
	public function set_terms( $terms, $taxonomy ) {
		$this->terms_updated = true;
		$terms               = is_array( $terms ) ? $terms : array( $terms );
		$this->fetch_terms();
		$this->terms[ $taxonomy ] = $terms;
	}

	/**
	 * Retrieves an array of terms assigned to the post for the specified taxonomy.
	 *
	 * @param       $taxonomy
	 * @param array $default An array of default taxonomy terms to return.
	 *
	 * @return array
	 */
	public function get_terms( $taxonomy, $default = array() ) {
		$this->fetch_terms( $taxonomy );
		if ( array_key_exists( $taxonomy, $this->terms ) ) {
			return empty( $this->terms[ $taxonomy ] ) ? $default : $this->terms[ $taxonomy ];
		}

		return $default;
	}

	/**
	 * Append or set the meta for the post.
	 *
	 * @param string $key
	 * @param array  $values
	 *
	 */
	public function set_meta( $key, array $values ) {
		$this->meta_updated      = true;
		$values                  = is_array( $values ) ? $values : $values;
		$this->post_meta[ $key ] = $values;
	}

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
	public function get_meta( $key, $single, $default = array() ) {
		$this->fetch_meta();

		if ( array_key_exists( $key, $this->post_meta ) && ! empty( $this->post_meta[ $key ] ) ) {
			return $single ? reset( $this->post_meta[ $key ] ) : $this->post_meta[ $key ];
		}

		return $default;
	}

	/**
	 * Fetches the terms assigned to the post for each taxonomy registered for the post.
	 *
	 * @param bool|false $force Whether to force or not a database read. Will override modifications.
	 */
	public function fetch_terms( $force = false ) {
		if ( ! $this->did_fetch_terms || $force ) {
			$taxonomies = get_object_taxonomies( $this->post, 'names' );
			foreach ( $taxonomies as $taxonomy ) {
				$this->terms[ $taxonomy ] = wp_get_object_terms( $this->id, $taxonomy, array( 'fields' => 'names' ) );
			}
			$this->did_fetch_terms = true;
		}
	}

	/**
	 * Triggers the reading of the custom post fields from the database
	 *
	 * @param bool|false $force Whether to force or not a database read. Will override modifications.
	 */
	public function fetch_meta( $force = false ) {
		if ( ! $this->did_fetch_meta || $force ) {
			$this->post_meta      = get_post_meta( $this->id );
			$this->did_fetch_meta = true;
		}
	}

	/**
	 * Persists the changes made to the post to the database.
	 */
	public function sync() {
		$this->new = false;
		if ( $this->post_updated ) {
			wp_update_post( $this->post );
			$this->post_updated = false;
			clean_post_cache( $this->id );
		}
		if ( $this->meta_updated ) {
			if ( ! empty( $this->post_meta ) ) {
				foreach ( $this->post_meta as $key => $value ) {
					if ( in_array( $key, $this->get_single_meta_keys() ) ) {
						update_post_meta( $this->id, $key, $value );
					} else {
						delete_post_meta( $this->id, $key );
						foreach ( $value as $val ) {
							add_post_meta( $this->id, $key, $val, false );
						}
					}
				}
			}
			$this->meta_updated = false;
		}
		if ( $this->terms_updated ) {

			if ( ! empty( $this->terms ) ) {

				foreach ( $this->terms as $taxonomy => $terms ) {

					wp_set_object_terms( $this->id, $terms, $taxonomy, false );
				}
			}
			$this->terms_updated = false;
		}
	}

	/**
	 * Rolls back the post data, meta and terms to the original state.
	 */
	public function rollback() {
		$this->rollback_data();
		$this->rollback_meta();
		$this->rollback_terms();
	}

	/**
	 * Rolls back the post data to the state on the database.
	 */
	public function rollback_data() {
		$this->post = get_post( $this->id );
	}

	/**
	 * Rolls back the post meta to the state on the database.
	 */
	public function rollback_meta() {
		$this->fetch_meta( true );
	}

	/**
	 * Rolls back the post terms to the state on the database.
	 */
	public function rollback_terms() {
		$this->fetch_terms( true );
	}

	/**
	 * @return array
	 */
	public function get_single_meta_keys() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_single_term_keys() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_column_aliases() {
		return array();
	}

	/**
	 * @param $alias
	 *
	 * @return mixed|null
	 */
	protected function get_column_from_alias( $alias ) {
		$aliases = $this->get_column_aliases();

		return $aliases[ $alias ];
	}

	/**
	 * @return array
	 */
	protected function get_write_protected_data_fields() {
		return array( 'ID', 'comment_count', 'guid', 'post_modified', 'post_modified_gmt' );
	}
}