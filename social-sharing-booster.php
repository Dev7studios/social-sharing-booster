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

}
new Dev7SocialSharingBooster();
