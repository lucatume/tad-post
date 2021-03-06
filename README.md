# TAD Post

*WordPress post object utility classes.*

## Installation
Dowload and unzip in the WordPress plugin folder or require it using [Composer](https://getcomposer.org/):

## Usage
The idea behind the class is to abstract how, in database terms, a post information is stored and handle post object with a little more agility.  
The class is in its early implementation but works on the simple concept:

1. lazy loading the post data, post meta and post terms frome the database
2. work locally on the post data, meta and terms
3. sync those terms back to the database using the `sync` method

This allows for transparent access to a post data, meta or terms with method calls like

	$car = new Acme\Car(23);

	// get the post title
	$name = $car->name;
	
	// get the post `color` meta
	$color = $car->color;
	
	// get the post `brand` taxonomy terms
	$brand = $car->brand;
	
	// Backbone like accessors with defaulting
	$title = $car->get('name', '');
	$color = $car->get('color','red');
	$cats = $car->get('brand', 'GM');

	// Property setters
	$car->name = 'Fastr 99';	
	$car->color = 'red';
	$car->brand = 'GM';
	
	// Backbone like setters
	$car->set('car_title', 'New title');
	$car->set('color', 'red');
	$car->set('category', 'GM');

### Code cost
The cost to have such a streamlined read and write access is the extension of the base class

    class Acme\Car extends tad_Post{
		
		public function get_column_aliases(){
			return array(
				'name' => 'post_title',
				'description' => 'post_content',
				'pitch' => 'post_excerpt',
				'availability' => 'post_status'
			);
		}	

        public function get_single_meta_keys() {
            return array('color');
        }

        public function get_single_term_keys() {
            return array('brand');
        }

    }

	
### Cascading access
**Beware**: read and write access happens in the post data, post terms and post meta cascading order. This means that a post that has the `category` taxonomy applied and a `category` meta key will return the `category` taxonomy terms when calling

	$post->category;
	
or

	$post->get('category',array());

Similarly a call like

	$post->set('color', '#ggg');
	
will try to write one of the post columns (none exists called "color") then to the "color" taxonomy if registered on the post, and will finally write to the "color" meta.

## Direct access to terms and meta values
Should direct access to terms or meta be neeeded then the following methods are available

	public function set_terms( $terms, $taxonomy );
	public function get_terms( $taxonomy, $default = array() );
	public function set_meta( $key, $value );
	public function get_meta( $key, $single );
	
## Sync
To persist any modification made to the post object to the database an explicit call to the `sync` method is required 

	$post->sync();
	
This allows for contextual modification to the post object without writing to the datbase.  
An example of this application might be one where a post object must pass thru a chain of processes each requiring an up to date version of the post data to work on where only the final step in the chain will conditionally write the post to the database.  
The `sync` method will, in any case, be conservative about database access and will not write anything if nothing changed.

## Rollback
If a later iteration on the post deems previous modifications as invalid then then rollback methods are available.

	$post->rollback();

will restore the post object to the initial state as just read from the database. Following along more granular rollback options are 

	$post->rollback_data();
	$post->rollback_meta();
	$post->roolback_terms();
	
## Post Repository and extension
While the post class can be used alone I've drafted the development of a repository pattern inspired Repository class to go along with the post.  
The class currently pack just one method, more to come

	tad_PostRepository::create($id); // get a new tad_Post object with data from the database
	
	tad_PostRepository::create(); // get a new and empty tad_Post object
	
A new post will be created when creating a new post as in the latter call; should the `sync` method not be called on that post later then the post will be deleted upon destruction (`__destruct` method).  
By default the `tad_PostRepository` class will create posts of the `post` post type and it's meant to be extended

	class Acme\CarsRepository extends tad_AbstractPostRepository{
		public static function get_post_type(){
			return 'car';
		}
		
		public static function get_post_class(){
			return 'Acme\Car';
		}
	}
	
	class Acme\Car extends tad_Post{
		// my custom post methods here
	}
