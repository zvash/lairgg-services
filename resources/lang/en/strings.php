<?php

return [
    'done' => 'Done',
    'invalid_request' => 'Invalid request.',//error message is intentionally vague
    'operation_cannot_be_done' => 'Cannot perform this operation.',//unknown reasons
    'cash_out' => [
        'not_enough_gems' => 'The requested amount to cash out exceeds your balance.',
    ],
    'game' => [
        'less_than_three_games_error' => 'Please select a minimum of three games.'
    ],
    'play' => [
        'participants_are_missing' => 'Participant of the match is not determined yet.',
        'next_match_started_scores_not_editable' => 'You cannot edit the score once the next match has started.',
        'too_many_forfeits' => 'Only one side can forfeit a game.',
    ],
    'shop' => [
        'out_of_stock' => 'This item is out of stock.',
        'not_enough_gems' => 'You don\'t have enough Gems.',
    ],
    'team' => [
        'captain_cannot_be_removed' => 'Captain cannot be removed',
        'captain_must_be_in_the_team' => 'New captain must be a member of the team.',
        'captain_was_not_changed' => 'Captain was not changed.',//database error
        'not_a_team_member' => 'Player is not in this team.',//when we're selecting a new captain
        'team_was_not_removed' => 'Team was not removed.', //database error
        'user_not_a_team_member' => 'Player is not in this team.',//when captain shares team gems
        'insufficient_gems' => 'The requested amount to share exceeds your prize.',//when captain shares team gems
    ],
    'tournament' => [
        'team_not_found' => 'Team not found',//captain requests to join the tournament with a team, but the team has been deleted with another device
        'not_enough_players_in_team' => 'Your team does not have enough players.',
        'not_a_participant' => 'You are not a participant of this tournament.',//when a non-participant tries to do something to the tournament that only participants can do (like leaving the tournament or checking in)
        'not_enough_privileges' => 'Only the captain can take this action.',
        'cannot_leave_already_started_tournament' => 'You cannot leave a tournament once it has started.',
        'not_accepted' => 'You have not been approved yet.',//user tries to check-in before being accepted
        'check_in_is_not_allowed' => 'You cannot check-in for a tournament once it has started.',
        'tournament_is_not_finished' => 'This tournament is not finished yet.',//error on events like releasing gems
        'gems_already_released' => 'Gems have already been released.',//re-releasing gems error
        'new_bracket_error' => 'Could not create a new bracket for this tournament.',

    ],
    'password' => [
        'token_was_sent' => 'Security code was sent to the provided email address.',
        'invalid_token_or_email_address' => 'Invalid security code or email address.',//verify forgotten password token
        'user_was_not_found' => 'User was not found.',//reset password by token error
        'wrong_current_password' => 'Current password is wrong.',//reset password error
        'password_was_changed' => 'Your password is updated.',
    ],
    'invitation' => [
        'join_url_is_not_valid' => 'Join link is not valid.',
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
        'organization_edit_access' => 'You do not have administrative access to edit this organization.',
        'organization_add_staff_access' => 'You do not have administrative access to add staff to this organization',
        'play_edit_access' => 'You do not have administrative access to update this play.',
        'team_invite_access' => 'Only the captain can invite new members.',
        'team_share_gem_access' => 'Only the captain can share Gems.',
        'team_remove_member_access' => 'Only the captain can remove members.',
        'team_delete_access' => 'Only the captain can delete the team.',
        'team_promote_member_access' => 'Only the captain can promote members.',
        'team_edit_access' => 'Only the captain can edit the team.',
        'team_join_url_view_access' => 'Only team members can access the join link.',
        'team_join_url_edit_access' => 'Only the captain can set the invite link.',
        'team_cancel_invitation_access' => 'Only the captain can cancel invitations.',
        'tournament_create_access' => 'You are not a member of this organization.',
        'tournament_create_bracket_access' => 'You do not have administrative access to create a bracket for this tournament.',
        'tournament_add_participant_access' => 'You do not have administrative access to add participants to this tournament.',
        'tournament_update_participant_status_access' => 'You do not have administrative access to update participant status for this tournament.',
        'tournament_send_invitation_access' => 'You do not have administrative access to invite participants.',
        'match_set_ready_access' => 'You do not have access to update this match.'
    ],
];
