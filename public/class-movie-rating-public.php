<?php

/**
 * The public-facing functionality of the plugin.
 */

class Movie_Rating_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_shortcode( 'movie_slider', array( $this, 'mr_movie_slider' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_mr_submit_rating', array( $this, 'mr_submit_rating' ) );
		add_action( 'wp_ajax_nopriv_mr_submit_rating', array( $this, 'mr_submit_rating' ) );
	}

	public function enqueue_styles() {
		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css/movie-rating-public.css', 
			array(), 
			$this->version, 
			'all' 
		);
		
		wp_enqueue_style( 
			'font-awesome', 
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', 
			array(), 
			'6.5.0' 
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/movie-rating-public.js', 
			array( 'jquery' ), 
			$this->version, 
			false 
		);

		wp_localize_script( 
			$this->plugin_name, 
			'mr_ajax', 
			array( 
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'mr_rating_nonce' ),
				'user_ip' => $this->get_user_ip() // Add user IP for identification
			) 
		);
	}

	
	private function get_user_ip() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	public function mr_movie_slider( $atts = array() ) {
		
		$atts = shortcode_atts( array(
			'genre' => '',
			'limit' => 10
		), $atts, 'movie_slider' );

		$args = array(
			'post_type'      => 'movie',
			'posts_per_page' => intval( $atts['limit'] ),
			'post_status'    => 'publish'
		);

		if ( ! empty( $atts['genre'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'genre',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['genre'] )
				)
			);
		}

		$movies = new WP_Query( $args );
		$user_ip = $this->get_user_ip();

		ob_start();
		
		if ( $movies->have_posts() ) : ?>
			
			<div class="mr-movie-slider-wrapper">
				<button class="mr-slider-btn mr-slider-left" type="button">
					<i class="fas fa-chevron-left"></i>
				</button>
				
				<div class="mr-movie-container">
					<?php while ( $movies->have_posts() ) : $movies->the_post(); 
						$movie_id = get_the_ID();
						$release_date = get_post_meta( $movie_id, '_mr_release_date', true );
						$poster_url = get_post_meta( $movie_id, '_mr_poster_url', true );
						$movie_link = get_post_meta( $movie_id, '_mr_movie_link', true );
						
						
						$ratings = get_post_meta( $movie_id, '_mr_ratings', true ) ?: array();
						$user_ratings = get_post_meta( $movie_id, '_mr_user_ratings', true ) ?: array();
						
						$average = count( $ratings ) ? round( array_sum( $ratings ) / count( $ratings ), 1 ) : 0;
						$total_votes = count( $ratings );
						
						
						$user_rating = isset($user_ratings[$user_ip]) ? intval($user_ratings[$user_ip]) : 0;
						
						
						$movie_genres = get_the_terms( $movie_id, 'genre' );
						$genre_names = array();
						if ( $movie_genres && ! is_wp_error( $movie_genres ) ) {
							foreach ( $movie_genres as $genre ) {
								$genre_names[] = $genre->name;
							}
						}
					?>
						<div class="mr-movie-card">
							<div class="mr-movie-image">
								<?php if ( $poster_url ) : ?>
									<img src="<?php echo esc_url( $poster_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" />
								<?php elseif ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium' ); ?>
								<?php else : ?>
									<div class="mr-no-poster">No Poster Available</div>
								<?php endif; ?>
								
								<?php if ( $movie_link ) : ?>
									<div class="mr-imdb-tag">
										<a href="<?php echo esc_url( $movie_link ); ?>" target="_blank">
											<i class="fas fa-external-link-alt"></i>
											LINK
										</a>
									</div>
								<?php endif; ?>
							</div>

							<div class="mr-movie-info">
								<h3 class="mr-movie-title"><?php echo esc_html( get_the_title() ); ?></h3>
								
								<?php if ( ! empty( $genre_names ) ) : ?>
									<div class="mr-genres">
										<?php foreach ( $genre_names as $genre_name ) : ?>
											<span class="mr-genre-tag"><?php echo esc_html( $genre_name ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
								
								<?php if ( $release_date ) : ?>
									<p class="mr-release-date">Release: <?php echo esc_html( date( 'M Y', strtotime( $release_date ) ) ); ?></p>
								<?php endif; ?>
								
								<div class="mr-stars <?php echo $user_rating > 0 ? 'user-rated' : 'clickable'; ?>" 
								     data-movie="<?php echo esc_attr( $movie_id ); ?>"
								     data-user-rating="<?php echo esc_attr( $user_rating ); ?>"
								     data-average="<?php echo esc_attr( $average ); ?>">
									<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
										<i class="fa-star <?php echo ($i <= ($user_rating > 0 ? $user_rating : $average)) ? 'fas' : 'far'; ?>" 
										   data-rating="<?php echo esc_attr( $i ); ?>"></i>
									<?php endfor; ?>
								</div>
								
								<p class="mr-average">
									Rating: <span class="mr-rating-value"><?php echo esc_html( $average ); ?></span> 
									(<span class="mr-vote-count"><?php echo esc_html( $total_votes ); ?></span> votes)/5
									<?php if ( $user_rating > 0 ) : ?>
										<br><small class="user-rating-text" style="color: #0073aa;">Your rating: <?php echo esc_html( $user_rating ); ?>/5</small>
									<?php endif; ?>
								</p>
							</div>
						</div>
					<?php endwhile; ?>
				</div>

				<button class="mr-slider-btn mr-slider-right" type="button">
					<i class="fas fa-chevron-right"></i>
				</button>
			</div>

		<?php else : ?>
			
			<div class="mr-no-movies">
				<p>No movies found<?php echo ! empty( $atts['genre'] ) ? ' in the "' . esc_html( $atts['genre'] ) . '" genre' : ''; ?>.</p>
			</div>
			
		<?php endif;

		wp_reset_postdata();
		return ob_get_clean();
	}

	
	public function mr_submit_rating() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'mr_rating_nonce' ) ) {
		wp_send_json_error( 'Security check failed' );
	}

	$movie_id = intval( $_POST['movie_id'] );
	$rating   = intval( $_POST['rating'] );
	$user_ip  = $this->get_user_ip();

	if ( $rating < 1 || $rating > 5 ) {
		wp_send_json_error( 'Invalid rating' );
	}

	$ratings = get_post_meta( $movie_id, '_mr_ratings', true ) ?: array();
	$user_ratings = get_post_meta( $movie_id, '_mr_user_ratings', true ) ?: array();

	
	if ( isset($user_ratings[$user_ip]) ) {
		
		$old_rating = $user_ratings[$user_ip];
		$key = array_search($old_rating, $ratings);
		if ($key !== false) {
			$ratings[$key] = $rating;
		}
		$message = 'Rating updated successfully!';
	} else {
	
		$ratings[] = $rating;
		$message = 'Thank you for rating this movie!';
	}

	
	$user_ratings[$user_ip] = $rating;

	update_post_meta( $movie_id, '_mr_ratings', $ratings );
	update_post_meta( $movie_id, '_mr_user_ratings', $user_ratings );

	$total_ratings = count( $ratings );
	$sum           = array_sum( $ratings );
	$new_average   = round( $sum / $total_ratings, 1 );

	wp_send_json_success( array( 
		'average'     => $new_average,
		'total_votes' => $total_ratings,
		'user_rating' => $rating,
		'message'     => $message
	) );
}
}