<?php

return [
    'done' => 'Done',
    'invalid_request' => 'Invalid request.',//error message is intentionally vague
    'operation_cannot_be_done' => 'Cannot perform this operation.',//unknown reasons
    'cash_out' => [
        'not_enough_gems' => 'You don\'t have enough gems to register this cash out request.',
    ],
    'game' => [
        'less_than_three_games_error' => 'Followed games cannot be less than three.'
    ],
    'play' => [
        'participants_are_missing' => 'Participants of the match are not determined yet.',
        'next_match_started_scores_not_editable' => 'Next match has already started. Scores are not editable.',
        'too_many_forfeits' => 'Too many participants has forfeited the game.',
    ],
    'shop' => [
        'out_of_stock' => 'We are out of stock for this item at the moment.',
        'not_enough_gems' => 'You don\'t have enough gems to buy this product.',
    ],
    'team' => [
        'captain_cannot_be_removed' => 'Captain cannot be removed',
        'captain_must_be_in_the_team' => 'New captain must be a member of the team.',
        'captain_was_not_changed' => 'Captain was not changed.',//database error
        'not_a_team_member' => 'Specified player is not a member of this team.',//when we're selecting a new captain
        'team_was_not_removed' => 'Team was not removed.', //database error
        'user_not_a_team_member' => 'User is not a member of the team.',//when captain shares team gems
        'insufficient_gems' => 'Insufficient gems.',//when captain shares team gems
    ],
    'tournament' => [
        'team_not_found' => 'Team not found',//captain requests to join the tournament with a team, but the team has been deleted with another device
        'not_enough_players_in_team' => 'Your team does not have enough players.',
        'not_a_participant' => 'You are not a participant of this tournament.',//when a non-participant tries to do something to the tournament that only participants can do (like leaving the tournament or checking in)
        'not_enough_privileges' => 'You don\'t have enough privileges to do this action on behalf of your team.',
        'cannot_leave_already_started_tournament' => 'Participants cannot leave an already started tournament.',
        'not_accepted' => 'You haven\'t got accepted in this tournament yet.',//user tries to check-in before being accepted
        'check_in_is_not_allowed' => 'You cannot check in for an already started tournament.',
        'tournament_is_not_finished' => 'This tournament is not over yet.',//error on events like releasing gems
        'gems_already_released' => 'Gems are already release.',//re-releasing gems error
        'new_bracket_error' => 'Could not create a new bracket for this tournament.',

    ],
    'password' => [
        'token_was_sent' => 'Token was sent to the provided email address.',
        'invalid_token_or_email_address' => 'Invalid token or email address.',//verify forgotten password token
        'user_was_not_found' => 'User was not found.',//reset password by token error
        'wrong_current_password' => 'Current password is wrong.',//reset password error
        'password_was_changed' => 'Your password is updated.',
    ],
    'invitation' => [
        'join_url_is_not_valid' => 'Join URL is not valid.',
        'invited_to_team' => ':identifier was invited to join the :team_title team.',
        'invited_to_tournament' => ':identifier was invited to join the :tournament_title tournament.',
    ],
    'user' => [
        'logged_out' => 'You are logged out.',//response of successful logout
        'user_was_deleted' => 'User was deleted successfully.',
        'user_identifier_already_been_set' => 'You have already set the :key field.',//set missing identifier error message
    ],
    'policy' => [
        'cash_out_read_access' => 'You do not have access to this cash out request.',
        'dispute_close_access' => 'You do not have administrative access to close this dispute',
        'tournament_edit_access' => 'You do not have administrative access to edit this tournament',
        'started_tournament_match_edit_access' => 'This match cannot be edited because it has already started.',
        'organization_edit_access' => 'You are not an admin for this organization.',
        'organization_add_staff_access' => 'You do not have administrative access to add staff to this organization',
        'play_edit_access' => 'You are not authorized to update this play.',
        'team_invite_access' => 'Only captains can invite new members.',
        'team_share_gem_access' => 'Only captains can share gems.',
        'team_remove_member_access' => 'Only captains can remove a member.',
        'team_delete_access' => 'Only captains can delete their teams.',
        'team_promote_member_access' => 'Only captains can promote members.',
        'team_edit_access' => 'Only captains can update the team.',
        'team_join_url_view_access' => 'Only team members can access to the join url.',
        'team_join_url_edit_access' => 'Only captains can set team\'s join URL.',
        'team_cancel_invitation_access' => 'Only captain can cancel invitations.',
        'tournament_create_access' => 'You are not a member of this organization.',
        'tournament_create_bracket_access' => 'You do not have administrative access to create a bracket this tournament.',
        'tournament_add_participant_access' => 'You cannot add participants to this tournament.',
        'tournament_update_participant_status_access' => 'You cannot update participant status for this tournament.',
        'tournament_send_invitation_access' => 'You cannot invite participants.',
    ],
];
