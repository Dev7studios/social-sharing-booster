<?php

add_filter( 'wpsf_register_settings', 'dev7_ssb_settings' );
function dev7_ssb_settings( $wpsf_settings ) {

    $wpsf_settings[] = array(
        'section_id' => 'general',
        'section_title' => __( 'General Settings', 'dev7-ssb' ),
        'section_description' => __( 'Settings that apply to all publishers.', 'dev7-ssb' ),
        'section_order' => 5,
        'fields' => array(
            array(
                'id' => 'site_title',
                'title' => __( 'Site Title', 'dev7-ssb' ),
                'desc' => __( 'Override the default WordPress Site Title used in open graph tags (og:site_name)', 'dev7-ssb' ),
                'placeholder' => get_bloginfo('name'),
                'type' => 'text'
            ),
            array(
                'id' => 'tagline',
                'title' => __( 'Tagline', 'dev7-ssb' ),
                'desc' => __( 'Override the default WordPress Tagline used in open graph tags (og:description)', 'dev7-ssb' ),
                'placeholder' => get_bloginfo('description'),
                'type' => 'text'
            ),
            /*array(
                'id' => 'fallback_image_id',
                'title' => __( 'Fallback Image ID', 'dev7-ssb' ),
                'desc' => __( 'Used as a fallback for posts and pages that don\'t have any featured/attached images', 'dev7-ssb' ),
                'placeholder' => '123',
                'type' => 'text'
            ),
            array(
                'id' => 'fallback_image_url',
                'title' => __( 'Fallback Image URL', 'dev7-ssb' ),
                'desc' => __( 'Used as a fallback for posts and pages that don\'t have any featured/attached images', 'dev7-ssb' ),
                'placeholder' => 'http://example.com/my-image.jpg',
                'type' => 'text'
            ),*/
        )
    );

    $wpsf_settings[] = array(
        'section_id' => 'facebook',
        'section_title' => __( 'Facebook Settings', 'dev7-ssb' ),
        'section_description' => __( 'Settings that apply to Facebook.', 'dev7-ssb' ),
        'section_order' => 10,
        'fields' => array(
            array(
                'id' => 'admins',
                'title' => __( 'Admin(s)', 'dev7-ssb' ),
                'desc' => __( 'Comma separated list of Facebook User ID\'s used for Facebook Insights data (fb:admins)', 'dev7-ssb' ),
                'placeholder' => '1234,5678',
                'type' => 'text'
            ),
            array(
                'id' => 'app_id',
                'title' => __( 'App ID', 'dev7-ssb' ),
                'desc' => __( 'Facebook Application ID used for Facebook Insights data (fb:app_id)', 'dev7-ssb' ),
                'placeholder' => '123456789',
                'type' => 'text'
            ),
            array(
                'id' => 'publisher',
                'title' => __( 'Publisher', 'dev7-ssb' ),
                'desc' => __( 'A Facebook page URL or ID of the publishing entity (article:publisher)', 'dev7-ssb' ),
                'placeholder' => 'https://www.facebook.com/exampleuser',
                'type' => 'text'
            ),
        )
    );

    $wpsf_settings[] = array(
        'section_id' => 'twitter',
        'section_title' => __( 'Twitter Settings', 'dev7-ssb' ),
        'section_description' => __( 'Settings that apply to Twitter Cards.', 'dev7-ssb' ),
        'section_order' => 15,
        'fields' => array(
            array(
                'id' => 'site_username',
                'title' => __( 'Website @username', 'dev7-ssb' ),
                'desc' => __( 'The Twitter @username for your website/company (twitter:site)', 'dev7-ssb' ),
                'placeholder' => '@example',
                'type' => 'text'
            ),
            array(
                'id' => 'creator_username',
                'title' => __( 'Author @username', 'dev7-ssb' ),
                'desc' => __( 'The Twitter @username for the content creator (twitter:creator)', 'dev7-ssb' ),
                'placeholder' => '@example',
                'type' => 'text'
            ),
        )
    );

    return $wpsf_settings;
}
