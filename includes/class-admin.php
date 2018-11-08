<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Coming_Soon_Admin {

	/**
	 * Sets up the class.
	 *
	 * @access public
	 * @since  1.0
	 */
	public function __construct() {
      
        // Render the Custom Status checkbox.
        add_action( 'edd_meta_box_settings_fields', array( $this, 'render_option' ), 100 );

        // Add a votes widget to the WordPress dashboard.
        add_action( 'wp_dashboard_setup', array( $this, 'votes_dashboard_widget' ) );

        // Load any admin scripts.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        // Save download with _edd_coming_soon_votes meta_key
        add_action( 'edd_save_download', array( $this, 'save_download' ), 10, 2 );

        // Appends the "Coming Soon" text and vote count to the price column on the admin price column.
        add_filter( 'edd_download_price', array( $this, 'admin_price_column' ), 20, 2 );

        // Appends the "Coming Soon" text and vote count to the price column on the admin price column, for variable priced downloads.
        add_filter( 'edd_price_range', array( $this, 'admin_price_column' ), 20, 2 );

        // Save our new metabox fields.
        add_filter( 'edd_metabox_fields_save', array( $this, 'metabox_fields_save' ) );
		    
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
            <p><?php printf( _n( '%s person wants this %s', '%s people want this %s', "<strong>$count</strong>", edd_get_label_singular( true ), 'edd-coming-soon' ), "<strong>$count</strong>", edd_get_label_singular( true ) ); ?></p>

        </div>
    <?php
    }

    /**
     * Votes dashboard widget.
     *
     * Displays the total number of votes for each "coming soon" product.
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
     * @since 1.3.0
     */
    public function votes_dashboard_widget() {
        wp_add_dashboard_widget( 'edd_coming_soon_votes_widget', sprintf( __( 'Most Wanted Coming Soon %s', 'edd-coming-soon' ), edd_get_label_plural() ), array( $this, 'votes_widget' ) );
    }
    
    /**
     * Scripts.
     *
     * @since 1.0
     */
    public function admin_scripts( $hook ) {
        global $post;

        if ( is_object( $post ) && $post->post_type != 'download' ) {
            return;
        }

        wp_enqueue_script( 'edd-cp-admin-scripts', EDD_COMING_SOON_URL . 'js/edd-coming-soon-admin.js', array( 'jquery' ), EDD_COMING_SOON_VERSION );
    }
    
    /**
     * Save downloads with _edd_coming_soon_votes meta key set to 0.
     *
     * @since   1.3.1
     * @return
     */
    public function save_download( $post_id, $post ) {

        $count = edd_coming_soon()->get_votes( $post_id );

        // update count on save if no count currently exists
        if ( edd_coming_soon_voting_enabled( $post_id ) && ! $count ) {
            update_post_meta( $post_id, '_edd_coming_soon_votes', 0 );
        }

    }
    
    /**
     * Append custom status text to normal prices and price ranges within the admin price column
     *
     * @return string	The text to display
     *
     * @since 1.2
     */
    public function admin_price_column( $price, $download_id ) {
        
        // Check if the download is "coming soon".
        $cs_active = edd_coming_soon_is_active( $download_id );

        // Check if voting is enabled for the download.
        $votes_enabled = edd_coming_soon_voting_enabled( $download_id );

        // Check whether voting is enabled for the shortcode.
        $votes_sc_enabled = (boolean) get_post_meta( $download_id, 'edd_cs_vote_enable_sc', true );

        // Get the vote count.
        $votes = get_post_meta( $download_id, '_edd_coming_soon_votes', true );

        // Append the custom status text to the price.
        $price .= '<br />' . edd_coming_soon()->get_custom_status_text();

        // Output the vote count to the price.
        if ( $cs_active && ( $votes_enabled || $votes_sc_enabled ) ) {
            $price .= '<br /><strong>' . __( 'Votes: ', 'edd-coming-soon' ) . $votes . '</strong>';
        }

        // Return the price.
        return $price;

    }
                    
    /**
     * Hook into EDD save filter and add the new fields
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

}

new EDD_Coming_Soon_Admin();
