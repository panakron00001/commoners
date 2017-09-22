<?php
/*
  Plugin Name: CC Commoners
  Plugin URI: http://github.com/creativecommons/commoners
  Description: Buddypress extensions for commoners.creativecommons.org .
  Author: Creative Commons Corporation
  Version: 2.0
  Author URI: http://github.com/creativecommons/
  License: GPLv2 or later at your option.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

////////////////////////////////////////////////////////////////////////////////
// Includes
////////////////////////////////////////////////////////////////////////////////

defined( 'COMMONERS_PATH' )
    or define( 'COMMONERS_PATH', plugin_dir_path( __FILE__ ) );


// Buddypress UI behaviour configuration

require_once(COMMONERS_PATH . 'includes/registration-settings.php');
require_once(COMMONERS_PATH . 'includes/registration-integration.php');
require_once(COMMONERS_PATH . 'includes/registration-levels.php');

// Configuration options for this plugin

require_once(COMMONERS_PATH . 'admin/options-emails.php');

// Tracking user membership application state

require_once(COMMONERS_PATH . 'includes/registration-state.php');

// Interfacing with GravityForms

require_once(COMMONERS_PATH . 'includes/gravityforms-interaction.php');

// Sign-up form workflow for application

require_once(COMMONERS_PATH . 'public/autocomplete-members.php');

require_once(COMMONERS_PATH . 'includes/registration-form-emails.php');
require_once(COMMONERS_PATH . 'public/registration-form-shortcode.php');

// User page application interface for admins

require_once(COMMONERS_PATH . 'admin/user-application-page.php');
require_once(COMMONERS_PATH . 'admin/user-pre-approve-list-page.php');
require_once(COMMONERS_PATH . 'admin/user-final-approval-list-page.php');

// Vouching UI for existing members to vouch for new applicant

require_once(COMMONERS_PATH . 'includes/vouching-applicant-profile.php');
require_once(COMMONERS_PATH . 'public/vouching-form-shortcode.php');

////////////////////////////////////////////////////////////////////////////////
// CAS / WordPress registration
////////////////////////////////////////////////////////////////////////////////

// CAS User Registration handling

add_action( 'user_register', 'commoners_user_level_register' );

// Plugin-specific User roles

register_activation_hook(
    __FILE__,
    'commoners_add_roles_on_plugin_activation'
);

////////////////////////////////////////////////////////////////////////////////
// Buddypress
////////////////////////////////////////////////////////////////////////////////

// Remove settings we want to hide because they clash with CAS signup

add_action( 'bp_setup_nav', 'commoners_remove_settings', 15 );

add_filter( 'bp_core_get_user_domain', '_bp_core_get_user_domain', 10, 4 );
add_filter( 'bp_core_get_userid', '_bp_core_get_userid', 10, 2 );
add_filter(
    'bp_get_activity_parent_content',
    '_bp_get_activity_parent_content',
    10,
    1
);
add_action(
    'bp_get_activity_action_pre_meta',
    '_bp_get_activity_action_pre_meta'
);
add_filter( 'bp_core_get_userid_from_nicename', '_bp_core_get_userid', 10, 2 );
add_filter( 'bp_xprofile_get_groups', 'commoners_filter_role_groups' );

// Must be called after commoners_vouching_add_tabs
add_action( 'bp_setup_nav', 'commons_not_logged_in_ui', 150 );

// Don't let unvouched users set their profiles

add_action( 'bp_ready', '_bp_remove_profile_options_if_unvouched' );
add_action( 'bp_core_setup_globals', '_bp_set_default_component' );

////////////////////////////////////////////////////////////////////////////////
// Registration Form
////////////////////////////////////////////////////////////////////////////////

// After each form in the Member sign-up process is submitted,
// we update the user's application stage/state

add_action(
    'gform_after_submission',
    'commoners_registration_form_submit_handler',
    10,
    2
);

// The shortcode to display the sign-up workflow forms.
// The exact form (or other content) displayed depends on the user's
// application state/stage.

add_shortcode(
    'commoners-signup-form',
    'commoners_registration_shortcode_render'
);

add_filter( 'gform_validation', 'commoners_vouching_form_post_validate' );

// Filter applicant form submissions to hook in state changes

add_filter( 'gform_validation', 'commoners_registration_post' );

// Autocompletion of member usernames

add_action( 'wp_ajax_the_ajax_hook', 'the_action_function' );
add_action( 'wp_ajax_nopriv_the_ajax_hook', 'the_action_function' );

add_action(
    'wp_ajax_autocomplete_members',
    commoners_ajax_autocomplete_members
);
add_action(
    'wp_ajax_nopriv_autocomplete_members',
    commoners_ajax_autocomplete_members
);

////////////////////////////////////////////////////////////////////////////////
// Form for existing members vouching for new applicant
////////////////////////////////////////////////////////////////////////////////

// The shortcode to display the vouching form.

add_shortcode(
    'commoners-vouching-form',
    'commoners_vouching_shortcode_render'
);

////////////////////////////////////////////////////////////////////////////////
// Admin pages and menus
////////////////////////////////////////////////////////////////////////////////


//add_action( 'parse_request', 'commoners_vouching_url_handler' );

////////////////////////////////////////////////////////////////////////////////
// Add admin settings, menus etc.
////////////////////////////////////////////////////////////////////////////////

if ( is_admin() ){
    // Remove various BuddyPress settings in various circumstances
    add_action(
        'wp_before_admin_bar_render',
        '_bp_admin_bar_remove_some_menu_items'
    );
    add_action('admin_menu', 'commoners_application_users_menu');
    add_action('admin_menu', 'commoners_application_pre_approval_menu');
    add_action('admin_menu', 'commoners_application_final_approval_menu');
    // add_action( 'admin_menu', 'add_mymenu' );
    add_action( 'admin_menu', 'commoners_settings_emails_register' );
    add_filter('user_row_actions', 'commoners_application_user_link', 10, 2);
    // Filter applicant user page form approve/declines to hook in user profile
    // creation and notification email sending.
    add_action(
        'gform_after_submission',
        'commoners_application_users_page_form_submit_handler',
        10,
        2
    );
}