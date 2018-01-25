<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function ccgn_final_approval_status_for_vouch_counts( $counts ) {
    $yes = $counts['yes'];
    $no = $counts['no'];
    if ( ( $no == 0 )
         && ($yes >= CCGN_NUMBER_OF_VOUCHES_NEEDED ) ) {
        $status = 'Vouched';
    } elseif ( $no > 0 ) {
        $status = 'Declined';
    } else {
        $status = 'Vouching';
    }
    return $status;
}

function ccgn_final_approval_status_for_vote_counts( $counts ) {
    $yes = $counts['yes'];
    $no = $counts['no'];
    if ( ( $no == 0 )
         && ($yes >= CCGN_NUMBER_OF_VOTES_NEEDED ) ) {
        $status = 'Approved';
    } elseif ( $no > 0 ) {
        $status = 'Declined';
    } else {
        $status = 'Voting';
    }
    return $status;
}

function ccgn_list_applications_for_final_approval () {
    $user_entries = ccgn_applicants_with_state(
        CCGN_APPLICATION_STATE_VOUCHING
    );
    foreach ($user_entries as $user_entry) {
        $user_id = $user_entry->ID;
        $user = get_user_by('ID', $user_id);
        // The last form the user filled out, so the time to use
        $vouchers_entry = ccgn_application_vouchers($user_id);
        // The actual count of vouches
        $vouch_counts = ccgn_application_vouches_counts( $user_id );
        // If the user is not a Final Approver,
        // and the applicant does not have enough positive votes,
        // or they have enough Vouches against that they must be rejected
        // do not show.
        // The Final Approver needs to see applicants who have been Vouched
        // against, or whose applications have stalled, in order to handle
        // those cases.
        if( ( ( $vouch_counts['no'] > CCGN_NUMBER_OF_VOUCHES_AGAINST_ALLOWED )
              || ( $vouch_counts['yes'] < CCGN_NUMBER_OF_VOUCHES_NEEDED) )
            && ( ! ccgn_current_user_is_final_approver() ) ) {
            continue;
        }
        // If the user has been asked to Vouch for the applicant and they
        // are not the Final Approver, they should not see the entry as they
        // cannot Vote for them.
        // Final Approvers cannot vote either, but they must be able to see
        // the user.
        if ( ccgn_vouching_request_exists( $user_id, get_current_user_id() )
             && ( ! ccgn_current_user_is_final_approver() ) ) {
            continue;
        }
        if ( $vouch_counts[ 'no' ] > 0 ) {
            $vouch_no_style = ' style="font-weight: bold"';
        }
        $vote_counts = ccgn_application_votes_counts( $user_id );
        if ($vote_counts[ 'no' ] > 0) {
            $vote_no_style = ' style="font-weight: bold"';
        }
        echo '<tr><td><a href="'
            . ccgn_application_user_application_page_url( $user_id )
            . '">'
            . $user->user_nicename
            . '</a></td><td>'
            . ccgn_applicant_type_desc( $user_id )
            . '</td><td>';
        if ( ccgn_current_user_is_final_approver() ) {
            echo ccgn_final_approval_status_for_vouch_counts( $vouch_counts )
                . '</td><td>'
                . $vouch_counts[ 'yes' ]
                . '</td><td' . $vouch_no_style . '>'
                . $vouch_counts[ 'no' ]
                . '</td><td>';
        }
        echo ccgn_final_approval_status_for_vote_counts( $vote_counts )
            . '</td><td>'
            . $vote_counts[ 'yes']
            . '</td><td' . $vote_no_style . '>'
            . $vote_counts[ 'no' ]
            . '</td><td>'
            . $vouchers_entry[ 'date_created' ]
            .'</td></tr>';
    }
}

function ccgn_application_approval_page () {
    ?>
<h1>Membership Applications for Approval</h1>
<table class="ccgn-approval-table">
  <thead>
    <tr>
      <th>User</th>
      <th>Type</th>
<?php if ( ccgn_current_user_is_final_approver() ) { ?>
      <th>Vouching Status</th>
      <th>Vouches For</th>
      <th>Vouches Against</th>
<?php } ?>
      <th>Voting Status</th>
      <th>Votes For</th>
      <th>Votes Against</th>
      <th>Application date</th>
    </tr>
  </thead>
  <tbody>
    <?php ccgn_list_applications_for_final_approval(); ?>
  </tbody>
</table>
<p>This is the list of applicants currently being Vouched by existing
members and voted on by the Membership Council.</p>
<p>Applicants need <b><?php echo CCGN_NUMBER_OF_VOUCHES_NEEDED; ?></b>
vouches for them and <b>zero</b> against them.</p>
<p>You can review the guidelines for reviewing applications here: <a href="https://github.com/creativecommons/global-network-strategy/blob/master/docs/Guide_for_approve_new_members.md">https://github.com/creativecommons/global-network-strategy/blob/master/docs/Guide_for_approve_new_members.md</a>.</p>
<!-- move to stylesheet and queue -->
<style>
.ccgn-approval-table {
    border-collapse: collapse;
    border-spacing: 10px 20px;
    text-align: left;
}
.ccgn-approval-table tr {
  border: solid;
  border-width: 1px 0;
}
.ccgn-approval-table td, th {
    padding: 8px 16px;
}
</style>
    <?php
}

function ccgn_application_final_approval_menu () {
    add_menu_page(
        'Global Network',
        'Global Network',
        'ccgn_list_applications',
        'global-network-application-approval',
        'ccgn_application_approval_page'
    );
    // And as the first submenu item, with a more descriptive name
    add_submenu_page(
        'global-network-application-approval',
        'Application Approval',
        'Application Approval',
        'ccgn_list_applications',
        'global-network-application-approval',
        'ccgn_application_approval_page'
    );
}
