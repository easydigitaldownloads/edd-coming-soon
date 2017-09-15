<?php
/*
Plugin Name: Easy Digital Downloads - Coming Soon
Plugin URI: https://easydigitaldownloads.com/downloads/edd-coming-soon/
Description: Allows "custom status" downloads (not available for purchase) and allows voting on these downloads in Easy Digital Downloads
Version: 1.3.3
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Contributors: easydigitaldownloads, sc0ttkclark, julien731
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php

Text Domain: edd-coming-soon
Domain Path: languages
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EDD_Coming_Soon' ) ) {

	final class EDD_Coming_Soon {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of EDD_Coming_Soon exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * The version number
		 *
		 * @since 1.0
		 */
		private $version = '1.3.3';

		/**
		 * Main EDD_Coming_Soon Instance
		 *
		 * Insures that only one instance of EDD_Coming_Soon exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 * @static
		 * @static var array $instance
		 * @return The one true EDD_Coming_Soon
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Coming_Soon ) ) {

				self::$instance = new EDD_Coming_Soon;
				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->hooks();

			}

			return self::$instance;

		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd-coming-soon' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd-coming-soon' ), '1.0' );
		}

		/**
		 * Constructor Function
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Reset the instance of the class
		 *
		 * @since 1.0
		 * @access public
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function setup_constants() {

			// Plugin constants
			if ( ! defined( 'EDD_COMING_SOON' ) ) {
				define( 'EDD_COMING_SOON', $this->version );
			}

			if ( ! defined( 'EDD_COMING_SOON_URL' ) ) {
				define( 'EDD_COMING_SOON_URL', plugin_dir_url( __FILE__ ) );
			}

			if ( ! defined( 'EDD_COMING_SOON_DIR' ) ) {
				define( 'EDD_COMING_SOON_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'EDD_COMING_SOON_INCLUDES' ) ) {
				define( 'EDD_COMING_SOON_INCLUDES', EDD_COMING_SOON_DIR . trailingslashit( 'includes' ) );
			}

		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'edd_coming_soon_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale   = apply_filters( 'plugin_locale',  get_locale(), 'edd-coming-soon' );
			$mofile   = sprintf( '%1$s-%2$s.mo', 'edd-coming-soon', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-coming-soon/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-coming-soon/ folder
				load_textdomain( 'edd-coming-soon', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-coming-soon/languages/ folder
				load_textdomain( 'edd-coming-soon', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-coming-soon', false, $lang_dir );
			}
		}

		/**
		* Loads the initial files needed by the plugin.
		*
		* @since 1.3.3
		*/
		public function includes() {
			require_once( EDD_COMING_SOON_INCLUDES . 'functions.php' );
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function hooks() {

			add_action( 'edd_meta_box_settings_fields', array( $this, 'render_option' ), 100 );
			add_action( 'edd_download_after', array( $this, 'display_text' ) );
			add_action( 'edd_pre_add_to_cart', array( $this, 'pre_add_to_cart' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'init', array( $this, 'increment_votes' ) );
			add_action( 'edd_save_download', array( $this, 'save_download' ), 10, 2 );
			add_action( 'wp_dashboard_setup', array( $this, 'votes_dashboard_widget' ) );
			add_action( 'wp_footer', array( $this, 'voting_progress' ) );

			add_filter( 'edd_metabox_fields_save', array( $this, 'metabox_fields_save' ) );
			add_filter( 'edd_download_price', array( $this, 'admin_price_column' ), 20, 2 );
			add_filter( 'edd_price_range', array( $this, 'admin_price_column' ), 20, 2 );
			add_filter( 'the_content', array( $this, 'single_download' ) );
			add_filter( 'edd_purchase_download_form', array( $this, 'purchase_download_form' ), 10, 2 );

			/**
			 * Vote shortcode.
			 *
			 * The shortcode adds the voting button on any page.
			 * It takes two attributes: id and description.
			 * The shortcode should be used as follows:
			 *
			 * [edd_cs_vote id="XX"]
			 *
			 * [edd_cs_vote id="XX" description="no"]
			 *
			 * @since  1.3.0
			 * @param  id  ID of the product to vote for
			 * @param  description   Show/hide the description text above the button. Set to "no" to hide description
			 */
			add_shortcode( 'edd_cs_vote', array( $this, 'get_vote_form' ) );

		}



		/**
		 * Render the Custom Status checkbox
		 *
		 * @param int 	$post_id Post ID
		 *
		 * @since 1.0
		 */
		public function render_option( $post_id ) {
			$coming_soon      = (boolean) get_post_meta( $post_id, 'edd_coming_soon', true );
			$vote_enable      = (boolean) get_post_meta( $post_id, 'edd_cs_vote_enable', true );
			$vote_enable_sc   = (boolean) get_post_meta( $post_id, 'edd_cs_vote_enable_sc', true );
			$coming_soon_text = get_post_meta( $post_id, 'edd_coming_soon_text', true );
			$count            = intval( get_post_meta( $post_id, '_edd_coming_soon_votes', true ) );

			// Default
			$default_text = apply_filters( 'edd_cs_coming_soon_text', __( 'Coming Soon', 'edd-coming-soon' ) );
		?>
			<p>
				<label for="edd_coming_soon">
					<input type="checkbox" name="edd_coming_soon" id="edd_coming_soon" value="1" <?php checked( true, $coming_soon ); ?> />
					<?php _e( 'Enable Coming Soon / Custom Status download', 'edd-coming-soon' ); ?>
				</label>
			</p>

			<div id="edd_coming_soon_container"<?php echo $coming_soon ? '' : ' style="display:none;"'; ?>>
				<p>
					<label for="edd_coming_soon_text">
						<input class="large-text" type="text" name="edd_coming_soon_text" id="edd_coming_soon_text" value="<?php echo esc_attr( $coming_soon_text ); ?>" />
						<?php echo sprintf( __( 'Custom Status text (default: <em>%s</em>)', 'edd-coming-soon' ), $default_text ); ?>
					</label>
				</p>

				<p><strong><?php _e( 'Voting', 'edd-coming-soon' ); ?></strong></p>

				<p>
					<label for="edd_cs_vote_enable">
						<input type="checkbox" name="edd_cs_vote_enable" id="edd_cs_vote_enable" value="1" <?php checked( true, $vote_enable ); ?> />
						<?php _e( 'Enable voting', 'edd-coming-soon' ); ?>
					</label>
				</p>

				<p>
					<label for="edd_cs_vote_enable_sc">
						<input type="checkbox" name="edd_cs_vote_enable_sc" id="edd_cs_vote_enable_sc" value="1" <?php checked( true, $vote_enable_sc ); ?> />
						<?php printf( __( 'Enable voting in the %s shortcode', 'edd-coming-soon' ), '[downloads]' ); ?>
					</label>
				</p>

				<p><strong><?php _e( 'Votes', 'edd-coming-soon' ); ?></strong></p>
				<p><?php printf( __( '%s people want this %s', 'edd-coming-soon' ), "<strong>$count</strong>", edd_get_label_singular( true ) ); ?></p>
			</div>
		<?php
		}

		/**
		 * Hook into EDD save filter and add the download image fields
		 *
		 * @param array $fields 	Array of fields to save for EDD
		 *
		 * @return array 			Array of fields to save for EDD
		 *
		 * @since 1.0
		 */
		public function metabox_fields_save( $fields ) {

			$fields[] = 'edd_coming_soon';
			$fields[] = 'edd_coming_soon_text';
			$fields[] = 'edd_cs_vote_enable';
			$fields[] = 'edd_cs_vote_enable_sc';

			return $fields;

		}

		/**
		 * Append custom status text to normal prices and price ranges within the admin price column
		 *
		 * @return string	The text to display
		 *
		 * @since 1.2
		 */
		public function admin_price_column( $price, $download_id ) {

			// is coming soon download
			$cs_active = edd_coming_soon_is_active( $download_id );

			// voting enabled
			$votes_enabled = $this->voting_enabled( $download_id );

			// voting enabled in shortcode
			$votes_sc_enabled = (boolean) get_post_meta( $download_id, 'edd_cs_vote_enable_sc', true );

			// votes
			$votes = get_post_meta( $download_id, '_edd_coming_soon_votes', true );

			$price .= '<br />' . $this->get_custom_status_text();

			if ( $cs_active && ( $votes_enabled || $votes_sc_enabled ) ) {
				$price .= '<br /><strong>' . __( 'Votes: ', 'edd-coming-soon' ) . $votes . '</strong>';
			}

			return $price;
		}

		/**
		 * Get the custom status text
		 *
		 * @return string	The custom status text or default 'Coming Soon' text
		 *
		 * @since 1.2
		 */
		public function get_custom_status_text() {
			if ( ! edd_coming_soon_is_active( get_the_ID() ) )
				return;

			$custom_text = get_post_meta( get_the_ID(), 'edd_coming_soon_text', true );
			$custom_text = !empty ( $custom_text ) ? $custom_text : apply_filters( 'edd_cs_coming_soon_text', __( 'Coming Soon', 'edd-coming-soon' ) );

			// either the custom status or default 'Coming Soon' text

			// admin colum text
			if ( is_admin() ) {
				return apply_filters( 'edd_coming_soon_display_admin_text', '<strong>' . $custom_text . '</strong>' );
			} else {
				// front-end text.
				return apply_filters( 'edd_coming_soon_display_text', '<p><strong>' . $custom_text . '</strong></p>' );
			}
		}

		/**
		 * Display the coming soon text. Hooks onto bottom of shortcode.
		 * Hook this function to wherever you want it to display
		 *
		 * @since 1.2
		 */
		public function display_text() {
			echo $this->get_custom_status_text();
		}

		/**
		 * Append coming soon text after main content on single download pages
		 *
		 * @return $content The main post content
		 * @since 1.2
		*/
		public function single_download( $content ) {

			if ( is_singular( 'download' ) && is_main_query() ) {
				return $content . $this->get_custom_status_text();
			}

			return $content;

		}

		/**
		 * Remove the purchase form if it's not a Custom Status download
		 * Purchase form includes the buy button and any options if it's variable priced
		 *
		 * @param string  $purchase_form Form HTML
		 * @param array   $args          Arguments for display
		 *
		 * @return string Form HTML
		 *
		 * @since 1.0
		 */
		public function purchase_download_form( $purchase_form, $args ) {

			global $post;

			if ( edd_coming_soon_is_active( $args[ 'download_id' ] ) ) {

				if ( true === ( $vote_enable = (boolean) get_post_meta( $post->ID, 'edd_cs_vote_enable', true ) ) ) {

					/* Display the voting form on single page */
					if ( is_single( $post ) && 'download' == $post->post_type ) {

						return $this->get_vote_form();

					} else {

						/* Only display the form in the download shortcode if enabled */
						if ( true === ( $vote_enable_sc = (boolean) get_post_meta( $post->ID, 'edd_cs_vote_enable_sc', true ) ) ) {
							return $this->get_vote_form();
						} else {
							return '';
						}
					}

				} else {
					return '';
				}

			}

			return $purchase_form;
		}

		/**
		 * Prevent download from being added to cart (free or priced) with ?edd_action=add_to_cart&download_id=XXX
		 *
		 * @param int	$download_id Download Post ID
		 *
		 * @since 1.0
		 */
		public function pre_add_to_cart( $download_id ) {

			if ( edd_coming_soon_is_active( $download_id ) ) {
				$add_text = apply_filters( 'edd_coming_soon_pre_add_to_cart', __( 'This download cannot be purchased', 'edd-coming-soon' ), $download_id );

				wp_die( $add_text, '', array( 'back_link' => true ) );
			}

		}

		/**
		 * Scripts
		 *
		 * @since 1.0
		 */
		public function admin_scripts( $hook ) {
			global $post;

			if ( is_object( $post ) && $post->post_type != 'download' ) {
				return;
			}

			wp_enqueue_script( 'edd-cp-admin-scripts', EDD_COMING_SOON_URL . 'js/edd-coming-soon-admin.js', array( 'jquery' ), EDD_COMING_SOON );
		}

		/**
		 * Increment the votes count.
		 *
		 * Adds one more vote for the current "coming soon" product.
		 *
		 * @since   1.3.0
		 * @return  Status of the update
		 */
		public function increment_votes() {
			if ( ! isset( $_POST['edd_cs_pid'] ) || ! isset( $_POST['edd_cs_nonce'] ) || ! wp_verify_nonce( $_POST['edd_cs_nonce'], 'vote' ) ) {
				return false;
			}

			$product_id  = isset( $_POST['edd_cs_pid'] ) ? intval( $_POST['edd_cs_pid'] ) : false;
			$redirect_id = isset( $_POST['edd_cs_redirect'] ) ? intval( $_POST['edd_cs_redirect'] ) : $product_id;

			if ( false === $product_id ) {
				return false;
			}

			/* Get current votes count */
			$current = $new = intval( get_post_meta( $product_id, '_edd_coming_soon_votes', true ) );

			/* Increment the count */
			++$new;

			/* Update post meta */
			$update = update_post_meta( $product_id, '_edd_coming_soon_votes', $new, $current );

			/* Set a cookie to prevent multiple votes */
			if ( false !== $update ) {
				setcookie( "edd_cs_vote_$product_id", '1', time() + 60*60*30, '/' );
			}

			$redirect = get_permalink( $redirect_id ) . '#edd-cs-voted';

			/* Read-only redirect (to avoid resubmissions on page refresh) */
			wp_redirect( $redirect );
			exit;
		}

		/**
		 * Save downloads with _edd_coming_soon_votes meta key set to 0
		 *
		 * @since   1.3.1
		 * @return
		 */
		public function save_download( $post_id, $post ) {

			$count = $this->get_votes( $post_id );

			// update count on save if no count currently exists
			if ( $this->voting_enabled( $post_id ) && ! $count ) {
				update_post_meta( $post_id, '_edd_coming_soon_votes', 0 );
			}

		}

		/**
		 * Check if a download has voting enabled
		 *
		 * @since   1.3.1
		 * @return  boolean
		 */
		public function voting_enabled( $download_id = 0 ) {

			if ( ! $download_id ) {
				return;
			}

			$voting_enabled = get_post_meta( $download_id , 'edd_cs_vote_enable', true );

			if ( $voting_enabled ) {
				return true;
			}

			return false;
		}

		/**
		 * Get a download's total votes
		 *
		 * @since   1.3.1
		 * @return  int $count, 0 otherwise
		 */
		public function get_votes( $download_id = 0 ) {

			if ( ! $download_id ) {
				return;
			}

			$count = get_post_meta( $download_id , '_edd_coming_soon_votes', true );

			if ( $count ) {
				return $count;
			}

			return 0;

		}

		/**
		 * Get the voting form.
		 *
		 * The form will record a new vote for the current product. It is used
		 * both in purchase_download_form and in the vote shortcode.
		 *
		 * @since  1.3.0
		 * @return string Form markup
		 */
		public function get_vote_form( $atts = array() ) {
			global $post;

			$atts = shortcode_atts( array(
				'id'          => false,
				'description' => 'yes'
			), $atts, 'edd_cs_vote' );

			$id          = $atts['id'];
			$description = $atts['description'];

			// Get product ID
			if ( false !== $id ) {
				$pid = intval( $id );
			} elseif ( isset( $post ) ) {
				$pid = $post->ID;
			} else {
				return false;
			}

			// Check if the post is actually a download
			if ( 'download' != ( $post_type = get_post_type( $pid ) ) ) {
				return false;
			}

			$voted            = isset( $_COOKIE['edd_cs_vote_' . $pid] ) ? true : false;
			$vote_description = apply_filters( 'edd_cs_vote_description', __( 'Let us know you\'re interested by voting below.', 'edd-coming-soon' ) );
			$submission       = apply_filters( 'edd_cs_vote_submission', __( 'I want this', 'edd-coming-soon' ) );
			$vote_message     = apply_filters( 'edd_coming_soon_voted_message', sprintf( __( 'We heard you! Your interest for this %s was duly noted.', 'edd-coming-soon' ), edd_get_label_singular( true ) ) );

			ob_start();
			?>

			<?php if ( $voted ) : ?>

				<p id="edd-cs-voted" class="edd-cs-voted"><?php echo $vote_message; ?></p>

			<?php else : ?>

				<form role="form" method="post" action="<?php echo get_permalink( $post->ID ); ?>" class="edd-coming-soon-vote-form">

					<?php if ( 'no' != $description ) : ?>
						<p class="edd-cs-vote-description"><?php echo $vote_description; ?></p>
					<?php endif; ?>

					<input type="hidden" name="edd_cs_pid" value="<?php echo $pid; ?>">
					<input type="hidden" name="edd_cs_redirect" value="<?php echo $post->ID; ?>">
					<?php wp_nonce_field( 'vote', 'edd_cs_nonce', false, true ); ?>
					<button type="submit" class="edd-coming-soon-vote-btn" name="edd_cs_vote"><?php echo apply_filters( 'edd_cs_btn_icon', '<span class="dashicons dashicons-heart"></span>' ); ?> <?php echo $submission; ?></button>
				</form>

			<?php endif;

			return ob_get_clean();
		}

		/**
		 * Votes dashboard widget.
		 *
		 * Displays the total number of votes for each
		 * "coming soon" product.
		 *
		 * @since  1.3.0
		 * @return void
		 */
		public function votes_widget() {
			$args = array(
				'post_type'              => 'download',
				'post_status'            => 'any',
				'meta_key'               => '_edd_coming_soon_votes',
				'orderby'                => 'meta_value_num',
				'order'                  => 'DESC',
				'no_found_rows'          => false,
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'meta_query'             => array(
					array(
						'key'     => 'edd_coming_soon',
						'value'   => '1',
						'type'    => 'CHAR',
						'compare' => '='
					)
				)
			);

			$query = new WP_Query( $args );

			if ( ! empty( $query->posts ) ) {

				$alternate = ''; ?>

				<table class="widefat">
					<thead>
						<tr>
							<th width="80%"><?php echo edd_get_label_singular(); ?></th>
							<th width="20%"><?php _e( 'Votes', 'edd-coming-soon' ); ?></th>
						</tr>
					</thead>

					<?php foreach ( $query->posts as $post ):

						$votes     = intval( get_post_meta( $post->ID, '_edd_coming_soon_votes', true ) );
						$alternate = ( '' == $alternate ) ? 'class="alternate"' : '';
						?>

						<tr <?php echo $alternate; ?>>
							<td><?php echo $post->post_title; ?></td>
							<td style="text-align:center;"><?php echo $votes; ?></td>
						</td>

					<?php endforeach; ?>

				</table>

				<p><small><?php printf( __( '%s with no votes won\'t appear in the above list.', 'edd-coming-soon' ), edd_get_label_plural() ); ?></small></p>

			<?php } else {
				printf( __( 'Either there are no &laquo;Coming Soon&raquo; %s in the shop at the moment, or none of them received votes.', 'edd-coming-soon' ), edd_get_label_plural( true ) );
			}

		}

		/**
		 * Add a dashboard widget for votes.
		 *
		 * @since \1.3.0
		 */
		public function votes_dashboard_widget() {
			wp_add_dashboard_widget( 'edd_coming_soon_votes_widget', sprintf( __( 'Most Wanted Coming Soon %s', 'edd-coming-soon' ), edd_get_label_plural() ), array( $this, 'votes_widget' ) );
		}

		/**
		 * Add voting progress.
		 *
		 * This replaces the vote button label during
		 * the form submission in order to clearly show
		 * the visitor that his vote is being taken into account.
		 *
		 * @since  1.3.0
		 * @return void
		 */
		public function voting_progress() {

			if ( wp_script_is( 'jquery', 'done' ) ):

				$voting = apply_filters( 'edd_cs_voting_text', __( 'Voting...', 'edd-coming-soon' ) ); ?>

				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('.edd-coming-soon-vote-btn').on('click', function() {
							$(this).text('<?php echo $voting; ?>');
						});
					});
				</script>

			<?php endif;
		}

	}

	/**
	 * The main function responsible for returning the one true EDD_Coming_Soon
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $edd_coming_soon = edd_coming_soon(); ?>
	 *
	 * @since 1.0
	 * @return object The one true EDD_Coming_Soon Instance
	 */
	function edd_coming_soon() {

	    if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

	        if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
	            require_once 'includes/class-activation.php';
	        }

	        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
	        $activation = $activation->run();

	    } else {

	        return EDD_Coming_Soon::instance();

	    }

	}
	add_action( 'plugins_loaded', 'edd_coming_soon', 100 );

}
