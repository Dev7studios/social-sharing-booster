<?php

add_filter( 'cmb_meta_boxes', 'dev7_ssb_metaboxes' );
function dev7_ssb_metaboxes( array $meta_boxes ) {

	$prefix = '_dev7_ssb_';

	$meta_boxes['dev7_ssb'] = array(
		'id'         => 'dev7_ssb',
		'title'      => __( 'Social Sharing Booster', 'dev7-ssb' ),
		'pages'      => array('post','page'),
		'context'    => 'normal',
		'priority'   => 'low',
		'show_names' => true,
		'fields'     => array(
			array(
				'name'    => __( 'Type', 'dev7-ssb' ),
				'desc'    => __( 'Override the type of the object. Defaults to "article" (og:type)', 'dev7-ssb' ),
				'default' => 'article',
				'id'      => $prefix . 'type',
				'type' 	  => 'text_small',
			),
		),
	);

	return $meta_boxes;
}
