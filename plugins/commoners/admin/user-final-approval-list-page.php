<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function commoners_final_approval_status_for_vouch_counts( $counts ) {
    $yes = $counts['yes'];
    $no = $counts['no'];
    if ( ( $no == 0 )
         && ($yes >= COMMONERS_NUMBER_OF_VOUCHES_NEEDED ) ) {
        $status = 'Approved';
    } elseif ( $no > 0 ) {
        $status = 'Declined';
    } else {
        $status = 'Vouching';
    }
    return $status;
}

function commoners_list_applications_for_final_approval () {
    $user_entries = commoners_applicants_with_state(
        COMMONERS_APPLICATION_STATE_RECEIVED
    );
    foreach ($user_entries as $user_entry) {
        $user_id = $user_entry->ID;
        $user = get_user_by('ID', $user_id);
        // The last form the user filled out, so the time to use
        $vouchers_entry = commoners_application_vouchers($user_id);
        // The user entered a name here
        $details_entry = commoners_application_details($user_id);
        // The actual count of vouches
        $vouch_counts = commoners_applicantion_vouches_counts( $applicant_id );
        if ($vouch_counts['no'] > 0) {
            $no_style = 'font-weight: bold';
        }
        echo '<tr><td><a href="'
            . commoners_application_user_application_page_url( $user_id )
            . '">'
            . $user->user_nicename
            . '</a></td><td>'
            . commoners_final_approval_status_for_vouch_counts( $counts )
            . '</td><td>'
            . $vouch_counts['yes']
            . '</td><td style="' . $no_style . '">'
            . $vouch_counts['no']
            . '</td><td>'
            . rgar( $vouchers_entry, 'date_created' )
            .'</td></tr>';
    }
}

function commoners_application_final_approval_page () {
    ?>
<h1>Applicants for Final Approval</h1>
<table class="commoners-approval-table">
  <thead>
    <tr>
      <th>User</th>
      <th>Vouching Status</th>
      <th>Vouches For</th>
      <th>Vouches Against</th>
      <th>Application date</th>
    </tr>
  </thead>
  <tbody>
    <?php commoners_list_applications_for_final_approval(); ?>
  </tbody>
</table>
<p>This is the list of applicants currently being Vouched by existing
members.</p>
<p>Applicants need <b><?php echo COMMONERS_NUMBER_OF_VOUCHES_NEEDED; ?></b>
vouches for them and <b>zero</b> against them in order for you to approve
them.</p>
<p>If you are part of the application review team, once they have enough
vouches (or if their application should be refused for some reason), you should
review their profile page by clicking on the link to their username.</p>
<!-- move to stylesheet and queue -->
<style>
.commoners-approval-table {
    border-collapse: collapse;
    border-spacing: 10px 20px;
    text-align: left;
}
.commoners-approval-table tr {
  border: solid;
  border-width: 1px 0;
}
.commoners-approval-table td, th {
    padding: 8px 16px;
}
</style>
    <?php
}

function commoners_application_final_approval_menu () {
    add_users_page(
        'Global Network Final Approval',
        'Global Network Final Approval',
        'edit_users',
        'commoners-global-network-final-approval',
        'commoners_application_final_approval_page'
    );
}