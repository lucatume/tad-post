<?php


class MyPost extends tad_Post {

	public function get_single_meta_keys() {
		return [ 'color', 'saturation' ];
	}

	public function get_single_term_keys() {
		return [ 'category' ];
	}

	public function get_column_aliases() {
		return [
			'name'        => 'post_title',
			'description' => 'post_content'
		];
	}

}


class tad_PostTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	public function postColumnsValues() {
		return [
			[ 'post_title', 'My new post' ],
			[ 'post_author', 0 ],
			[ 'post_date', '2015-01-03 18:00:00' ],
			[ 'post_date_gmt', '2015-01-03 18:00:00' ],
			[ 'post_content', 'lorem ipsum dolor' ],
			[ 'post_title', 'lorem ipsum dolor' ],
			[ 'post_excerpt', 'lorem ipsum dolor' ],
			[ 'post_status', 'pending' ],
			[ 'comment_status', 'closed' ],
			[ 'ping_status', 'closed' ],
			[ 'post_password', 'lorem ipsum dolor' ],
			[ 'post_name', 'lorem-ipsum-dolor' ],
			[ 'to_ping', 'http://theaveragedev.com' ],
			[ 'pinged', 'http://theaveragedev.com' ],
			[ 'post_content_filtered', 'lorem ipsum dolor' ],
			[ 'post_parent', 0 ],
			[ 'menu_order', 0 ],
			[ 'post_type', 'post' ],
			[ 'post_mime_type', '' ],
			[ 'comment_count', 0 ],
		];
	}

	/*
	 * @test
	 * it should allow accessing post columns as properties
	 * @dataProvider postColumnsValues
	 */
	public function it_should_allow_accessing_post_columns_as_properties( $key, $value ) {
		$sut = new tad_Post( $this->factory->post->create( [ $key => $value ] ) );
		new WP_Post();
		$this->assertEquals( $value, $sut->$key );
	}

	/**
	 * @test
	 * it should allow accessing post columns using get
	 * @dataProvider postColumnsValues
	 */
	public function it_should_allow_accessing_post_columns_using_get( $key, $value ) {
		$sut = new tad_Post( $this->factory->post->create( [ $key => $value ] ) );

		$this->assertEquals( $value, $sut->get( $key ) );
	}

	public function writeProtectedFields() {
		return [
			[ 'ID', 715 ],
			[ 'comment_count', 23 ],
			[ 'post_modified', '2015-01-06 22:00:00' ],
			[ 'post_modified_gmt', '2015-01-06 22:00:00' ],
			[ 'guid', 'http://theaveragedev.com/index.php?p=23' ],
		];
	}

	/**
	 * @test
	 * it should not allow setting protected fields
	 * @dataProvider writeProtectedFields
	 */
	public function it_should_not_allow_setting_protected_fields( $key, $input ) {
		$sut      = new tad_Post( $this->factory->post->create() );
		$original = $sut->get( $key );
		$sut->set( $key, $input );
		$this->assertEquals( $original, $sut->get( $key ) );
	}

	/**
	 * @test
	 * it should allow setting post data fields with property accessor
	 * @dataProvider postColumnsValues
	 */
	public function it_should_allow_setting_post_data_fields_with_property_accessor( $key, $value ) {
		$id  = $this->factory->post->create();
		$sut = new tad_Post( $id );

		$sut->$key = $value;
		$sut->sync();

		$this->assertEquals( $value, $sut->$key );
		$post = get_post( $id );
		$this->assertEquals( $value, $post->$key );
	}

	/**
	 * @test
	 * it should allow setting post data fields with set accessor
	 * @dataProvider postColumnsValues
	 */
	public function it_should_allow_setting_post_data_fields_with_set_accessor( $key, $value ) {
		$id  = $this->factory->post->create();
		$sut = new tad_Post( $id );

		$sut->set( $key, $value );
		$sut->sync();

		$this->assertEquals( $value, $sut->$key );
		$post = get_post( $id );
		$this->assertEquals( $value, $post->$key );
	}

	/**
	 * @test
	 * it should allow getting terms using property
	 */
	public function it_should_allow_getting_terms_using_property() {
		$id = $this->factory->post->create();
		$this->insert_post_categories();
		wp_set_object_terms( $id, [ 'foo', 'new' ], 'category' );
		$sut = new tad_Post( $id );

		$this->assertEquals( [ 'foo', 'new' ], $sut->category );
	}

	/**
	 * @test
	 * it should allow getting terms using get accessor
	 */
	public function it_should_allow_getting_terms_using_get_accessor() {
		$id = $this->factory->post->create();
		$this->insert_post_categories();
		wp_set_object_terms( $id, [ 'foo', 'new' ], 'category' );
		$sut = new tad_Post( $id );

		$this->assertEquals( [ 'foo', 'new' ], $sut->get( 'category' ) );
	}

	/**
	 * @test
	 * it should allow setting terms using property
	 */
	public function it_should_allow_setting_terms_using_property() {
		$id = $this->factory->post->create();
		$this->insert_post_categories();

		$sut           = new tad_Post( $id );
		$sut->category = array( 'foo', 'new' );
		$sut->sync();

		$this->assertEquals( [ 'foo', 'new' ], $sut->get( 'category' ) );
		$this->assertEquals( [ 'foo', 'new' ], wp_get_object_terms( $id, 'category', [ 'fields' => 'names' ] ) );
	}

	/**
	 * @test
	 * it should allow setting terms using get accessor
	 */
	public function it_should_allow_setting_terms_using_get_accessor() {
		$id = $this->factory->post->create();
		$this->insert_post_categories();

		$sut = new tad_Post( $id );
		$sut->set( 'category', array( 'foo', 'new' ) );
		$sut->sync();

		$this->assertEquals( [ 'foo', 'new' ], $sut->get( 'category' ) );
		$this->assertEquals( [ 'foo', 'new' ], wp_get_object_terms( $id, 'category', [ 'fields' => 'names' ] ) );
	}

	/**
	 * @test
	 * it should allow getting meta using property
	 */
	public function it_should_allow_getting_meta_using_property() {
		$id = $this->factory->post->create();

		$sut        = new tad_Post( $id );
		$sut->color = [ 'red' ];
		$sut->sync();

		$this->assertEquals( [ 'red' ], $sut->color );
		$this->assertEquals( [ 'red' ], get_post_meta( $id, 'color' ) );
	}

	/**
	 * @test
	 * it should allow getting meta using getter
	 */
	public function it_should_allow_getting_meta_using_getter() {
		$id = $this->factory->post->create();

		$sut = new tad_Post( $id );
		$sut->set( 'color', [ 'red' ] );
		$sut->sync();

		$this->assertEquals( [ 'red' ], $sut->get( 'color' ) );
		$this->assertEquals( [ 'red' ], get_post_meta( $id, 'color' ) );
	}

	/**
	 * @test
	 * it should allow setting multiple meta using property
	 */
	public function it_should_allow_setting_multiple_meta_using_property() {
		$id = $this->factory->post->create();

		$sut        = new tad_Post( $id );
		$meta       = [ 'red', 'blue', 23 ];
		$sut->color = $meta;
		$sut->sync();

		$this->assertEquals( $meta, $sut->color );
		$this->assertEquals( $meta, get_post_meta( $id, 'color' ) );
	}

	/**
	 * @test
	 * it should replace the meta when setting it
	 */
	public function it_should_replace_the_meta_when_setting_it() {
		$id = $this->factory->post->create();

		$sut        = new tad_Post( $id );
		$meta1      = [ 'red', 'blue', 23 ];
		$meta2      = [ 'green', 11 ];
		$sut->color = $meta1;
		$sut->sync();

		$this->assertEquals( $meta1, $sut->color );
		$this->assertEquals( $meta1, get_post_meta( $id, 'color' ) );

		$sut->color = $meta2;
		$sut->sync();

		$this->assertEquals( $meta2, $sut->color );
		$this->assertEquals( $meta2, get_post_meta( $id, 'color' ) );
	}

	/**
	 * @test
	 * it should delete the post if new and not synced
	 */
	public function it_should_delete_the_post_if_new_and_not_synced() {
		$id = $this->factory->post->create();

		$sut = new tad_Post( $id, true );

		$sut->__destruct();
		clean_post_cache( $id );

		$this->assertEmpty( get_post( $id ) );
	}

	/**
	 * @test
	 * it should rollback post data
	 */
	public function it_should_rollback_post_data() {
		$post = $this->factory->post->create_and_get();

		$sut = new tad_Post( $post->ID, true );

		$sut->set( 'post_title', 'Lorem' );
		$sut->set( 'post_content', 'dolor sit' );

		$sut->rollback();

		$original_post_title   = $post->post_title;
		$original_post_content = $post->post_content;

		$this->assertEquals( $original_post_title, $sut->post_title );
		$this->assertEquals( $original_post_content, $sut->post_content );
		$post = get_post( $post->ID );
		$this->assertEquals( $original_post_title, $post->post_title );
		$this->assertEquals( $original_post_content, $post->post_content );
	}

	/**
	 * @test
	 * it should rollback meta
	 */
	public function it_should_rollback_meta() {
		$post = $this->factory->post->create_and_get();

		$sut = new tad_Post( $post->ID, true );

		update_post_meta( $post->ID, 'color', 'green' );
		update_post_meta( $post->ID, 'saturation', 'full' );

		$sut->set( 'color', [ 'red' ] );
		$sut->set( 'saturation', [ 'mild' ] );

		$sut->rollback();

		$this->assertEquals( [ 'green' ], $sut->color );
		$this->assertEquals( [ 'full' ], $sut->saturation );
		$this->assertEquals( 'green', get_post_meta( $post->ID, 'color', true ) );
		$this->assertEquals( 'full', get_post_meta( $post->ID, 'saturation', true ) );
	}

	/**
	 * @test
	 * it should rollback terms
	 */
	public function it_should_rollback_terms() {
		$id = $this->factory->post->create();
		$this->insert_post_categories();

		$sut = new tad_Post( $id, true );

		wp_set_object_terms( $id, 'foo', 'category' );

		$sut->set( 'category', [ 'foo', 'new' ] );

		$sut->rollback();

		$this->assertEquals( [ 'foo' ], wp_get_object_terms( $id, 'category', [ 'fields' => 'names' ] ) );
		$this->assertEquals( [ 'foo' ], $sut->category );
	}

	/**
	 * @test
	 * it should allow aliasing the post columns
	 */
	public function it_should_allow_aliasing_the_post_columns() {
		$id = $this->factory->post->create();

		$sut = new MyPost( $id );

		$title   = 'Lorem';
		$content = 'More text';

		$sut->name        = $title;
		$sut->description = $content;

		$sut->sync();

		$post = get_post( $id );
		$this->assertEquals( $title, $sut->post_title );
		$this->assertEquals( $content, $sut->description );
		$this->assertEquals( $title, $sut->name );
		$this->assertEquals( $content, $sut->description );
		$this->assertEquals( $title, $post->post_title );
		$this->assertEquals( $content, $post->post_content );
	}

	/**
	 * @test
	 * it should allow default values in getter
	 */
	public function it_should_allow_default_values_in_getter() {
		$id  = $this->factory->post->create();
		$sut = new tad_Post( $id );

		$sut->set( 'category', [ ] );
		$sut->sync();
		$default = [ 'foo', 'bar' ];
		$this->assertEquals( $default, $sut->get( 'category', $default ) );

		$this->assertEquals( [ 'red' ], $sut->get( 'color', [ 'red' ] ) );
	}

	private function insert_post_categories() {
		wp_insert_term( 'new', 'category' );
		wp_insert_term( 'foo', 'category' );
	}
}