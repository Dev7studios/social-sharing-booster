<?php
/*
Plugin Name: Social Sharing Booster
Plugin URI: http://dev7studios.com/plugins/social-sharing-booster
Description: Automatically make your content look the best it can when it is shared on Facebook, Twitter, Pinterest, Google+ etc.
Version: 1.0.0
Author: Dev7studios
Author URI: http://dev7studios.com
*/

class Dev7SocialSharingBooster {

    private $plugin_path;
    private $plugin_url;
    private $wpsf;
    private $dev7_store_url = 'http://dev7studios.com';
    private $dev7_item_name = 'Social Sharing Booster WordPress Plugin';
    private $plugin_version = '1.0.0';
    private $plugin_author = 'Dev7studios';
    private $plugin_conflicts = 'no-conflict';

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        load_plugin_textdomain( 'dev7-ssb', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

        // Settings
        require_once( $this->plugin_path .'includes/wp-settings-framework.php' );
        $this->wpsf = new WordPressSettingsFramework( $this->plugin_path .'settings.php', 'dev7_ssb_settings' );
        // Metaboxes
        require_once( $this->plugin_path .'metaboxes.php' );

        add_action( 'init', array(&$this, 'init'), 9999 );
        add_action( 'admin_menu', array(&$this, 'admin_menu') );
        add_filter( $this->wpsf->get_option_group() .'_settings_validate', array(&$this, 'validate_settings') );
        add_action( 'wp_head', array(&$this, 'wp_head') );

        // Run plugin compatibility/conflict checks.
        add_action( 'admin_init', array( $this, 'plugin_compatibility_check' ) );

        if( !class_exists('EDD_SL_Plugin_Updater') ){
        	require_once( $this->plugin_path .'EDD_SL_Plugin_Updater.php' );
        }
        $license_key = wpsf_get_setting( 'dev7_ssb_settings', 'dev7studios', 'license_key' );
        if( $license_key ){
            $edd_updater = new EDD_SL_Plugin_Updater( $this->dev7_store_url, __FILE__, array(
                'version'   => $this->plugin_version,
                'license'   => $license_key,
                'item_name' => $this->dev7_item_name,
                'author'    => $this->plugin_author,
                'url'       => home_url()
            ) );
        }
    }

    public function init()
    {
        if( !class_exists('cmb_Meta_Box') ){
            require_once( $this->plugin_path .'includes/custom-metaboxes/init.php' );
        }
    }

    public function admin_menu()
    {
        add_menu_page( 'Social Sharing Booster', 'Social Sharing', 'manage_options', 'dev7-social-sharing-booster', array(&$this, 'settings'), 'dashicons-share-alt2', 120 );
    }

    public function settings()
    {
        ?>
        <div class="wrap">
            <h2>Social Sharing Booster</h2>
            <?php
            do_action( 'dev7_ssb_before_settings' );
            $this->wpsf->settings();
            do_action( 'dev7_ssb_after_settings' );
            ?>
        </div>
        <?php
    }

    public function validate_settings( $input )
	{
        $prefix = 'dev7_ssb_settings_';
        $input[$prefix .'dev7studios_license_key']  = filter_var( $input[$prefix .'dev7studios_license_key'], FILTER_SANITIZE_STRING );
        $input[$prefix .'general_site_title']       = filter_var( $input[$prefix .'general_site_title'], FILTER_SANITIZE_STRING );
        $input[$prefix .'general_tagline']          = filter_var( $input[$prefix .'general_tagline'], FILTER_SANITIZE_STRING );
        $input[$prefix .'facebook_admins']          = filter_var( $input[$prefix .'facebook_admins'], FILTER_SANITIZE_STRING );
        $input[$prefix .'facebook_app_id']          = filter_var( $input[$prefix .'facebook_app_id'], FILTER_SANITIZE_STRING );
        $input[$prefix .'facebook_publisher']       = filter_var( $input[$prefix .'facebook_publisher'], FILTER_SANITIZE_URL );
        $input[$prefix .'twitter_site_username']    = filter_var( $input[$prefix .'twitter_site_username'], FILTER_SANITIZE_STRING );
        $input[$prefix .'twitter_creator_username'] = filter_var( $input[$prefix .'twitter_creator_username'], FILTER_SANITIZE_STRING );
    	return $input;
	}

    public function wp_head()
    {
        echo "<!-- Social Sharing Booster Begin -->\n";
        do_action( 'dev7_ssb_head_begin' );

        $title = '';
        $description = '';
        $type = 'website';
        $site_title = wpsf_get_setting( 'dev7_ssb_settings', 'general', 'site_title' );
        $tagline = wpsf_get_setting( 'dev7_ssb_settings', 'general', 'tagline' );
        $url = home_url( add_query_arg( null, null ) );
        $fb_admins = wpsf_get_setting( 'dev7_ssb_settings', 'facebook', 'admins' );
        $fb_app_id = wpsf_get_setting( 'dev7_ssb_settings', 'facebook', 'app_id' );
        $tw_card = 'summary';
        $tw_site_username = wpsf_get_setting( 'dev7_ssb_settings', 'twitter', 'site_username' );
        $tw_creator_username = wpsf_get_setting( 'dev7_ssb_settings', 'twitter', 'creator_username' );

        if( is_front_page() && is_home() ){
            // Default homepage
            $title = $site_title ? $site_title : get_bloginfo('name');
            $description = $tagline ? $tagline : get_bloginfo('description');
        } elseif( is_front_page() ){
            // Static homepage
            $title = $site_title ? $site_title : get_bloginfo('name');
            $description = $tagline ? $tagline : get_bloginfo('description');
        } elseif( is_home() ){
            // Blog page
            $title = $site_title ? $site_title : get_bloginfo('name');
            $description = $tagline ? $tagline : get_bloginfo('description');
        } elseif( is_archive() ){
            // Category, Tag, other Taxonomy Term, custom post type archive, Author and Date-based pages
            if( is_category() ){
                $title = single_cat_title( '', false );
                $description = single_cat_title( '', false ) .' '. __( 'Category', 'dev7-ssb' );
            } elseif( is_tag() ){
                $title = single_tag_title( '', false );
                $description = __( 'Tagged', 'dev7-ssb' ) .' '. single_tag_title( '', false );
            } elseif( is_day() ){
                $title = get_the_date();
                $description = __( 'Daily Archives for', 'dev7-ssb' ) .' '. get_the_date();
            } elseif( is_month() ){
                $title = get_the_date( 'F Y' );
                $description = __( 'Monthly Archives for', 'dev7-ssb' ) .' '. get_the_date( 'F Y' );
            } elseif( is_year() ){
                $title = get_the_date( 'Y' );
                $description = __( 'Yearly Archives for', 'dev7-ssb' ) .' '. get_the_date( 'Y' );
            } elseif( is_author() ){
                $title = get_the_author();
                $description = __( 'Authored by', 'dev7-ssb' ) .' '. get_the_author();
            } else {
                $title = single_term_title( '', false );
                $description = single_term_title( '', false );
            }
        } elseif( is_search() ){
            // Search
            $title = get_search_query();
            $description = __( 'Search Results for', 'dev7-ssb' ) .' '. get_search_query();
        } elseif( is_404() ){
            // 404
            $title = __( 'Page not found', 'dev7-ssb' );
            $description = $tagline ? $tagline : get_bloginfo('description');
        } else {
            // Posts & Pages
            global $post;
            $object = null;
            if( is_page() ){
                $object = get_page( $post->ID );
            } else {
                $object = get_post( $post->ID );
            }

            $title = get_the_title();
            $content = isset( $object->post_content ) ? $object->post_content : '';
            $description = trim( str_replace(array("\r", "\n"), '', substr( strip_tags( $content ), 0, 197 ) ) ) .'...';
            $ssb_type = get_post_meta( $post->ID, '_dev7_ssb_type', true );
            $type = $ssb_type ? $ssb_type : 'article';
            $tags = get_the_terms( $post->ID, 'post_tag' );
            $fb_publisher = wpsf_get_setting( 'dev7_ssb_settings', 'facebook', 'publisher' );

            echo $this->meta( 'article:author', get_the_author_meta( 'display_name', get_post_field( 'post_author', $post->ID ) ) );
            echo $this->meta( 'article:published_time', get_the_time( 'c', false, $post ) );
            echo $this->meta( 'article:modified_time', get_the_modified_time( 'c' ) );
            if( !empty($tags) ){
                foreach( $tags as $tag ) echo $this->meta( 'article:tag', $tag->name );
            }
            if( $fb_publisher ) echo $this->meta( 'article:publisher', $fb_publisher );

            // Images
            if( has_post_thumbnail( $post->ID ) ){
                $post_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
                echo $this->meta( 'og:image', $post_thumb[0] );
                echo $this->meta( 'og:image:width', $post_thumb[1] );
                echo $this->meta( 'og:image:height', $post_thumb[2] );

                $tw_card = 'summary_large_image';
                echo $this->meta( 'twitter:image:src', $post_thumb[0] );
                echo $this->meta( 'twitter:image:width', $post_thumb[1] );
                echo $this->meta( 'twitter:image:height', $post_thumb[2] );
            } elseif( is_attachment() ){
                if( wp_attachment_is_image( $post->ID ) ){
                    $image = wp_get_attachment_image_src( $post->ID, 'full' );
                    echo $this->meta( 'og:image', $image[0] );
                    echo $this->meta( 'og:image:width', $image[1] );
                    echo $this->meta( 'og:image:height', $image[2] );

                    $tw_card = 'photo';
                    echo $this->meta( 'twitter:image:src', $image[0] );
                    echo $this->meta( 'twitter:image:width', $image[1] );
                    echo $this->meta( 'twitter:image:height', $image[2] );
                }
            } else {
                $galleries = get_post_galleries( $post, false );
                if( !empty($galleries) ){
                    foreach( $galleries as $gallery ){
                        if( isset($gallery['ids']) && $gallery['ids'] ){
                            $ids = explode(',', $gallery['ids']);
                            $i = 0;
                            foreach( $ids as $image_id ){
                                $image = wp_get_attachment_image_src( $image_id, 'full' );
                                echo $this->meta( 'og:image', $image[0] );
                                echo $this->meta( 'og:image:width', $image[1] );
                                echo $this->meta( 'og:image:height', $image[2] );

                                echo $this->meta( 'twitter:image'. $i .':src', $image[0] );
                                echo $this->meta( 'twitter:image'. $i .':width', $image[1] );
                                echo $this->meta( 'twitter:image'. $i .':height', $image[2] );
                                $i++;
                            }
                        }
                    }
                    $tw_card = 'gallery';
                }
            }
        }

        echo $this->meta( 'og:title', apply_filters( 'dev7_ssb_og_title', $title ) );
        echo $this->meta( 'og:description', apply_filters( 'dev7_ssb_og_description', $description ) );
        echo $this->meta( 'og:type', apply_filters( 'dev7_ssb_og_type', $type ) );
        echo $this->meta( 'og:site_name', $site_title ? $site_title : get_bloginfo('name') );
        echo $this->meta( 'og:url', apply_filters( 'dev7_ssb_og_url', $url ) );
        echo $this->meta( 'og:locale', get_locale() );
        if( $fb_admins ) echo $this->meta( 'fb:admins', $fb_admins );
        if( $fb_app_id ) echo $this->meta( 'fb:app_id', $fb_app_id );
        echo $this->meta( 'twitter:card', $tw_card );
        echo $this->meta( 'twitter:title', $title );
        echo $this->meta( 'twitter:description', $description );
        if( $tw_site_username ) echo $this->meta( 'twitter:site', $tw_site_username );
        if( $tw_creator_username ) echo $this->meta( 'twitter:creator', $tw_creator_username );

        do_action( 'dev7_ssb_head_end' );
        echo "<!-- Social Sharing Booster End -->\n";
    }

    private function meta( $property, $content, $prop = 'property' )
    {
        if( $property == 'og:url' ){
            $content = esc_url( $content );
        } else {
            $content = esc_attr( $content );
        }

        $output = '<meta '. $prop .'="'. esc_attr( $property ) .'" content="'. $content .'" />' . "\n";
        return apply_filters( 'dev7_ssb_meta_tag', $output, $property, $content, $prop );
    }

    /**
     * Checks for plugin incompatibilities and pushes a notice if there is one.
     *
     * Runs based on a filter defined in __construct().
     *
     * @return void Ends up with a notice output.
     */
    public function plugin_compatibility_check() {

        // Suggestions to fix conflicts.
        $suggestions = array(
            'wp_seo' => __( "You need to <a href='admin.php?page=wpseo_social'>disable</a> the following WordPress SEO Options: <em>Add Open Graph meta data</em>, <em>Add Twitter card meta data</em>, and <em>Add Google+ specific post meta data</em>. ", 'dev7-ssb' ),
        );

        /**
         * A list of plugins to test.
         *
         * To simply test if a plugin is active, add an item to the array
         * like the following:
         *
         *     array(
         *         // The file of the plugin for is_plugin_active() test/
         *         'plugin'          => 'plugin-folder/plugin-file.php',
         *
         *         // The name to put in the notice.
         *         'plugin_name'     => 'The Plugin Name',
         *
         *         // Not required, but will test if a function exists.
         *         'function_exists' => 'a_function_in_the_plugins_files',
         *
         *         // For use on class based plugins (also not required).
         *         'class_exists'    => 'A_Class_In_The_Plugin',
         *
         *         // Offer up a suggestion on what to do, should match a key
         *         // in the `$suggestions` array.
         *         'suggestion'      => 'suggestion_key'
         *     ),
         *
         * To do specific tests (such as option conflicts), use something
         * like the following:
         *
         *     array(
         *         'plugin'      => 'wordpress-seo/wp-seo.php',
         *         'plugin_name' => 'The Plugin Name',
         *         'suggestion'  => 'suggestion_key'
         *
         *         // A test that you have defined.
         *         'test'        => 'name_of_test',
         *     ),
         *
         * You will have to add the test to
         * Dev7SocialSharingBooster::plugin_compatibility_test().
         *
         * @var array
         */
        $incompatible_plugins = array(

            // WordPress SEO (Yoast).
            array(
                'plugin'          => 'wordpress-seo/wp-seo.php',
                'plugin_name'     => 'WordPress SEO (Yoast)',
                'suggestion'      => $suggestions['wp_seo'],
                'test'            => 'wp_seo',
            ),
        );

        // Go through possible plugins and perform all the tests.
        foreach ( $incompatible_plugins as $incompatible_plugin ) {
            if ( 'conflict' == $this->plugin_compatibility_test( $incompatible_plugin ) ) {

                // Convert to an array when we find plugins.
                if ( ! is_array( $this->plugin_conflicts ) ) {
                    $this->plugin_conflicts = array();
                }

                // Tell this plugin there is a conflict with this plugin.
                $this->plugin_conflicts[] = $incompatible_plugin;
            }
        }

        // Add the notice if there are conflicts.
        if ( 'no-conflict' != $this->plugin_conflicts ) {
            add_action( 'admin_notices', array( $this, 'plugin_compatibility_notice' ), 99 );
        }
    }

    /**
     * Performs activated plugin and specific tests on suspect plugins.
     *
     * @param array $incompatible_plugin The plugin data being tested.
     *
     * @return string `conflict` if the plugin is active or shows conflicts, otherwise `no-conflict`.
     */
    private function plugin_compatibility_test( $incompatible_plugin ) {

        /**
         * WordPress SEO Conflict Tests.
         */
        if ( isset( $incompatible_plugin['test'] ) && 'wp_seo' == $incompatible_plugin['test'] ) {

            // No conflicts if the plugin isn't even active.
            if ( 'inactive' == $this->is_conflicting_plugin_active( $incompatible_plugin ) ) {
                return 'no-conflict';
            }

            // Get WP SEO's options.
            $wp_seo_social_options = get_option( 'wpseo_social' );

            // If any of these options are set to true, then we have a conflict.
            $bad_options_on = array(
                'opengraph',
                'googleplus',
                'twitter',
            );

            // Force the conflict message if any of these are true.
            foreach ( $bad_options_on as $bad_option_on ) {
                if ( isset( $wp_seo_social_options[ $bad_option_on ] ) && true == $wp_seo_social_options[ $bad_option_on ] ) {
                    return 'conflict';
                }
            }

            // No conflicts.
            return 'no-conflict';
        }

        /**
         * Plugin Activate Tests.
         *
         * Skipped if one of the above tests are preferred.
         *
         * Does a blanket test if the plugin is active, as the only solution
         * would be to de-activate it.
         *
         * If the plugin is active, return as being in conflict.
         */
        if( 'active' == $this->is_conflicting_plugin_active( $incompatible_plugin ) ) {
            return 'conflict';
        } else {
            return 'no-conflict';
        }

    }

    /**
     * Detects if a suspect plugin is activated or not.
     *
     * Test more than just `is_plugin_active()` as sometimes code changes
     * or file structures move things around.
     *
     * @param array $incompatible_plugin A plugin that might be considered for conflicts.
     *
     * @return string `active` if the plugin is found activated, `inactive` if not.
     */
    private function is_conflicting_plugin_active( $incompatible_plugin ) {

        // Is the plugin active?
        if ( isset( $incompatible_plugin['plugin'] ) && is_plugin_active( $incompatible_plugin['plugin'] ) ) {
            return 'active';

        // What if they move the plugin files? Does this function exist?
        } elseif ( isset( $incompatible_plugin['function_exists'] ) && function_exists( $incompatible_plugin['function_exists'] ) ) {
            return 'active';

        // What if they renamed that function? Does this class exist still?
        } elseif ( isset( $incompatible_plugin['class_exists'] ) && class_exists( $incompatible_plugin['class_exists'] ) ) {
            return 'active';

        // This plugin does not appear to be active.
        } else {
            return 'inactive';
        }
    }

    /**
     * Output a notice when a plugin conflict is found.
     *
     * Notice added via `admin_notices`
     * in Dev7SocialSharingBooster::plugin_compatibility_check().
     *
     * @return void Outputs the notice.
     */
    public function plugin_compatibility_notice() {

        // Only if we have possible conflicting plugins...
        if ( is_array( $this->plugin_conflicts ) ) {
            echo '<div class="error">';

            // Track how many times we say the same suggestion.
            $last_suggestion = '';

            // Make a list of plugins.
            foreach ( $this->plugin_conflicts as $conflicted_plugin ) {

                // Build a string list of plugins.
                $plugins .= "{$conflicted_plugin['plugin_name']}, ";

                // Don't suggest the same suggestion twice.
                if ( $last_suggestion != $conflicted_plugin['suggestion'] ) {

                    // Build a string list of suggestions.
                    // Defined in Dev7SocialSharingBooster::plugin_compatibility_check().
                    $suggestions .= $conflicted_plugin['suggestion'];
                }

                // Set the last suggestion, so we don't output two.
                $last_suggestion = $conflicted_plugin['suggestion'];
            }

            // Remove trailing comma.
            $plugins = rtrim( $plugins, ", " );

            // Output the notice message.
            echo sprintf( __( '<p>Social Sharing Booster found conflicts with the following plugin(s): %s. %s</p>', 'dev7-ssb' ), "<strong>$plugins</strong>", $suggestions );
            echo '</div>';
        }
    }
}
new Dev7SocialSharingBooster();
