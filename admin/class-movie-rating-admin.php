<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://deepa.dev.com
 * @since      1.0.0
 *
 * @package    Movie_Rating
 * @subpackage Movie_Rating/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Movie_Rating
 * @subpackage Movie_Rating/admin
 * @author     Deepa <deepasahoo12@gmail.com>
 */

class Movie_Rating_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_action( 'init', array( $this, 'mr_register_movie_cpt' ) );
		add_action( 'add_meta_boxes', array( $this, 'mr_add_movie_meta_box' ) );
		add_action( 'save_post', array( $this, 'mr_save_movie_meta' ) );
	}
	
	// Register Movies CPT
	public function mr_register_movie_cpt() {
		$args = array(
			'labels' => array(
				'name' => __( 'Movies' ),
				'singular_name' => __( 'Movie' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array( 'slug' => 'movie' ),
			'supports' => array( 'title', 'thumbnail' ),
			'menu_icon' => 'dashicons-video-alt2'
		);
		register_post_type( 'movie', $args );

		register_taxonomy(
			'genre',
			'movie',
			array(
				'label' => 'Genres',
				'hierarchical' => true,
				'rewrite' => array( 'slug' => 'genre' ),
			)
		);
	}

	public function mr_add_movie_meta_box() {
		add_meta_box(
			'mr_movie_details',
			'Movie Details',
			array( $this, 'mr_movie_details_callback' ),
			'movie'
		);
	}

	public function mr_movie_details_callback( $post ) {
		$release_date = get_post_meta( $post->ID, '_mr_release_date', true );
		$poster_url   = get_post_meta( $post->ID, '_mr_poster_url', true );
		$movie_link   = get_post_meta( $post->ID, '_mr_movie_link', true );
		?>
		<p>
			<label><strong>Release Date:</strong></label><br>
			<input type="date" name="mr_release_date" value="<?php echo esc_attr( $release_date ); ?>" style="width: 100%;">
		</p>
		<p>
			<label><strong>Movie Poster:</strong></label><br>
			<input type="text" id="mr_poster_url" name="mr_poster_url" value="<?php echo esc_url( $poster_url ); ?>" style="width: 70%;">
			<button type="button" class="button" id="mr_upload_poster">Upload</button>
		</p>
		<p>
			<label><strong>Movie Link (IMDB):</strong></label><br>
			<input type="url" name="mr_movie_link" value="<?php echo esc_url( $movie_link ); ?>" style="width: 100%;" placeholder="https://www.imdb.com/title/...">
		</p>
		
		
		<?php
	}

	public function mr_save_movie_meta( $post_id ) {
		if ( isset( $_POST['mr_release_date'] ) ) {
			update_post_meta( $post_id, '_mr_release_date', sanitize_text_field( $_POST['mr_release_date'] ) );
		}
		if ( isset( $_POST['mr_poster_url'] ) ) {
			update_post_meta( $post_id, '_mr_poster_url', esc_url_raw( $_POST['mr_poster_url'] ) );
		}
		if ( isset( $_POST['mr_movie_link'] ) ) {
			update_post_meta( $post_id, '_mr_movie_link', esc_url_raw( $_POST['mr_movie_link'] ) );
		}
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/movie-rating-admin.css', array(), $this->version, 'all' );
		
	}

	public function enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/movie-rating-admin.js', array( 'jquery' ), $this->version, false );
	}
}