<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

////////////////////////////////////////////////////////////////////////////////
//
////////////////////////////////////////////////////////////////////////////////

function ccgn_registration_email_sub($key, $value, $text) {
    return str_replace(
        "*|${key}|*",
        $value,
        $text
    );
}

function ccgn_registration_email_sub_names($applicant_name, $applicant_id,
                                           $voucher_name, $text) {
    $result = ccgn_registration_email_sub(
        'APPLICANT_NAME',
        $applicant_name,
        $text
    );
    $result = ccgn_registration_email_sub(
        'APPLICANT_ID',
        $applicant_id,
        $result
    );
    $result = ccgn_registration_email_sub(
        'VOUCHER_NAME',
        $voucher_name,
        $result
    );
    $result = ccgn_registration_email_sub(
        'SITE_URL',
        get_site_url(),
        $result
    );
    $result = ccgn_registration_email_sub(
        'APPLICANT_PROFILE_URL',
        bp_core_get_userlink($applicant_id, false, true),
        $result
    );
    return $result;
}

function ccgn_registration_email( $applicant_name, $applicant_id,
                                  $voucher_name, $to_address,
                                  $subject, $message ) {
    $subject_substituted = ccgn_registration_email_sub_names(
        $applicant_name,
        $applicant_id,
        $voucher_name,
        $subject
    );
    $message_substituted = ccgn_registration_email_sub_names(
        $applicant_name,
        $applicant_id,
        $voucher_name,
        $message
    );
    add_filter( 'wp_mail_from', 'ccgn_mail_from_address' );
    add_filter( 'wp_mail_from_name', 'ccgn_mail_from_name' );
    add_filter( 'wp_mail_content_type', 'ccgn_html_mail_content_type' );
    wp_mail(
        $to_address,
        $subject_substituted,
        $message_substituted
    );
    remove_filter( 'wp_mail_content_type', 'ccgn_html_mail_content_type' );
    remove_filter( 'wp_mail_from_name', 'ccgn_mail_from_name' );
    remove_filter( 'wp_mail_from', 'ccgn_mail_from_address' );
}

function ccgn_registration_email_to_applicant ( $applicant_id,
                                                $email_option ) {
    $applicant = get_user_by( 'ID', $applicant_id );
    $options = get_option( $email_option );
    $subject = $options[ 'subject' ];
    $message = $options[ 'message' ];
    ccgn_registration_email(
        $applicant->user_nicename,
        $applicant->ID,
        '',
        $applicant->user_email,
        $subject,
        $message
    );
}

function ccgn_registration_email_to_voucher ( $applicant_id,
                                              $voucher_id,
                                              $email_option ) {
    $applicant = get_user_by( 'ID', $applicant_id );
    $voucher = get_user_by( 'ID', $voucher_id );
    $options = get_option( $email_option );
    $subject = $options[ 'subject' ];
    $message = $options[ 'message' ];
    ccgn_registration_email(
        $applicant->user_nicename,
        $applicant->ID,
        $voucher->user_nicename,
        $voucher->user_email,
        $subject,
        $message
    );
}

function ccgn_registration_email_application_received ( $applicant_id ) {
    ccgn_registration_email_to_applicant(
        $applicant_id,
        'ccgn-email-received'
    );
}

function ccgn_registration_email_application_approved ( $applicant_id ) {
    ccgn_registration_email_to_applicant(
        $applicant_id,
        'ccgn-email-approved'
    );
}

function ccgn_registration_email_application_rejected ( $applicant_id ) {
    ccgn_registration_email_to_applicant(
        $applicant_id,
        'ccgn-email-rejected'
    );
}

function ccgn_registration_email_vouching_request ( $applicant_id,
                                                    $voucher_id ) {
    ccgn_registration_email_to_voucher(
        $applicant_id,
        $voucher_id,
        'ccgn-email-vouch-request'
    );
}


////////////////////////////////////////////////////////////////////////////////
// Use the name and address from our settings for emails
////////////////////////////////////////////////////////////////////////////////

//IMPLEMENTME: override only for scope of send above

function ccgn_mail_from_address( $old ) {
    return get_option( 'ccgn-email-sender' )[ 'address' ];
}

function ccgn_mail_from_name( $old ) {
 return get_option( 'ccgn-email-sender' )[ 'name' ];
}

function ccgn_html_mail_content_type () {
    return 'text/html';
}