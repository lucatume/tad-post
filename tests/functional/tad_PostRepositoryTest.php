<?php


class tad_PostRepositoryTest extends \WP_UnitTestCase {

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

	/**
	 * @test
	 * it should return a tad_Post on existing ID
	 */
	public function it_should_return_a_tad_post_on_existing_id() {
		$id = $this->factory->post->create();

		$post = tad_PostRepository::create( $id );

		$this->assertInstanceOf( 'tad_Post', $post );
		$this->assertEquals( get_the_title( $id ), $post->post_title );
	}

	/**
	 * @test
	 * it should return a tad_Post on non existing ID
	 */
	public function it_should_return_a_tad_post_on_non_existing_id() {
		$post = tad_PostRepository::create();

		$this->assertInstanceOf( 'tad_Post', $post );

	}

	/**
	 * @test
	 * it should return post as post type on base class
	 */
	public function it_should_return_post_as_post_type_on_default_class() {
		$this->assertEquals( 'post', tad_PostRepository::get_post_type() );
	}

	/**
	 * @test
	 * it should return tad_Post as post class on base class
	 */
	public function it_should_return_tad_post_as_post_class_on_base_class() {
		$this->assertEquals( 'tad_Post', tad_PostRepository::get_post_class() );
	}
}