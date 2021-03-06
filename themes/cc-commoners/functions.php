<?php

function cc_commoners_theme_setup () {
    register_nav_menus(
        array(
            'top'    => __( 'Top Menu', 'cc-commoners' ),
            'bottom' => __( 'Bottom Menu', 'cc-commoners' )
        )
    );
}

add_action( 'after_setup_theme', 'cc_commoners_theme_setup' );

function cc_commoners_widgets () {
    unregister_sidebar( 'sidebar-2' );
    unregister_sidebar( 'sidebar-3' );
    register_sidebar(
        array(
            'name'          => __( 'Footer Text', 'cc-commoners' ),
            'id'            => 'sidebar-footer-text',
            'description'   => __( 'Add widgets here to appear in your footer.', 'cc-commoners' ),
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '',
            'after_title'   => '',
        )
    );
}

add_action( 'widgets_init', 'cc_commoners_widgets', 11 );

function cc_commoners_theme_scripts () {
    wp_enqueue_script(
        'cc-commoners',
        get_theme_file_uri( '/assets/js/cc-commoners.js' ),
        array(),
        '1.0',
        true
    );
    // Theme stylesheet
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css'
    );
    wp_dequeue_style( 'cc-commoners' );
    wp_enqueue_style( 'cc-commoners-style-extra',
                      get_theme_file_uri( '/assets/css/extra.css' )
    );
    wp_enqueue_style(
        'load-font-awesome',
        'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css'
    );
    wp_enqueue_style(
        'load-roboto-condensed',
        'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i|Roboto+Condensed'
    );
}

add_action( 'wp_enqueue_scripts', 'cc_commoners_theme_scripts' );