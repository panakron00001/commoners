<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

////////////////////////////////////////////////////////////////////////////////
// FIXME:
// We should use the voucher's user id, not their username
// We can do this once we cache the user id on the "Choose Vouchers" form
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// Vouching form shortcode
// This uses constants from includes/gravityforms-interaction.php
////////////////////////////////////////////////////////////////////////////////

// Has applicant requested that voucher vouch for them?
// FIXME: The form contains the voucher usernames, not ids so look up by
// username until we cache the ids on save.

function ccgn_vouching_request_exists ( $applicant_id,
                                             $voucher_id ) {
    $result = false;
    $vouchers = ccgn_vouching_request_entry ( $applicant_id );
    foreach( CCGN_GF_VOUCH_VOUCHER_FIELDS as $field_id ) {
        if ( $vouchers[ $field_id ] == $voucher_id ) {
            $result = true;
        }
    }
    return $result;
}

// Render the form for User to vouch for Applicant.
// Firstly we check that the user is logged in and that we should display the
// form to this user.
// Then we render the Vouch For Applicant form, with the correct values filled
// out there (we will validate the userid server-side on submission).

function ccgn_vouching_shortcode_render ( $atts ) {
    // Only logged-in users can vouch.
    if ( ! is_user_logged_in() ) {
        wp_redirect( 'https://login.creativecommons.org/login?service='
                     . get_site_url()
                     . '/vouch/' );
        exit;
    }

    // We need an applicant to vouch for
    if ( ! isset( $_GET[ 'applicant_id' ] ) ) {
        echo _( '<p>No applicant specified to vouch for.</p>' );
        exit;
    }

    // Get applicant and voucher identifiers
    //FIXME: Get voucher id and use that once the form contains it
    $applicant_id = $_GET[ 'applicant_id' ];
    $voucher = wp_get_current_user();
    $voucher_id = get_current_user_id();

    // Render correct UI for state of vouching
    if ( ! ccgn_user_is_vouched( $voucher_id ) ) {
        echo _( "<p>You must be vouched before you can vouch for others.<p>" );
    } elseif ( ! ccgn_vouching_request_exists( $applicant_id,
                                              $voucher_id ) ) {
        echo _( "<p>Request couldn't be found.<p>" );
    } elseif ( ! ccgn_vouching_request_active ( $applicant_id ) ) {
        echo _( "<p>That person's application to become a Member of the Creative Commons Global Network has already been resolved.<p></p>Thank you!</p>" );
    } elseif( ! ccgn_vouching_request_open( $applicant_id,
                                                 $voucher_id ) ) {
        // This is a bit of a hack. It will be displayed when the page
        // refreshes after intially submitting the form AND if the user
        // re-visits it subsequently.
        // So we make sure it will read well in both cases.
        echo _( "<p>Thank you for responding to this request!<p>" );
    } else {
        if ( ccgn_user_is_institutional_applicant ( $user_id ) ) {
            echo _( "<i>Note that this is an institution applying to join the Global Network. We still need you to vouch for this institution as you would for an individual that you know.</i>" );
        }
        // We were going to pass this as the content of an HTML field in the
        // gravity form but this is easier
        echo ccgn_vouching_form_applicant_profile_text( $applicant_id );
        gravity_form(
            CCGN_GF_VOUCH,
            false,
            false,
            false,
            array(
                CCGN_GF_VOUCH_APPLICANT_ID => applicant_id
            )
        );
    }
}

// Make sure no-one tries to vouch for someone they haven't been asked to,
// or to double-vouch them.

function ccgn_vouching_form_post_validate ( $validation_result ) {
    $form = $validation_result['form'];
    if ( $form[ 'name' ] == CCGN_GF_VOUCH ) {
        $applicant_id = rgpost( CCGN_GF_VOUCH_APPLICANT_ID );
        $voucher_id = form[ 'created_by' ];
        // Don't check ccgn_vouching_request_active, as the user may be
        // responding after that is no longer true and we don't want to annoy
        // them.
        $ok = ccgn_vouching_request_exists (
            $applicant_id,
            $voucher_id
        ) && ccgn_vouching_request_open (
            $applicant_id,
            $voucher_id
        ) && ccgn_user_is_vouched( $voucher_id );

        if ( ! $ok ) {
            // set the form validation to false
            $validation_result['is_valid'] = false;
        }

        //Assign modified $form object back to the validation result
        $validation_result['form'] = $form;
    }
    return $validation_result;
}
