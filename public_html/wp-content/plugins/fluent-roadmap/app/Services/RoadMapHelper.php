<?php

namespace FluentRoadmap\App\Services;


use FluentCrm\App\Models\Tag;
use FluentCrm\App\Models\Lists;

class RoadMapHelper
{
    public static function getRoadMapSettings()
    {
        $settings = fluent_boards_get_option('roadmap_settings', []);

        $defaults = [
            'enable_new_idea_submission'     => 'yes',
            'new_idea_require_auth'         => 'yes',
            'enable_new_comment_submission'  => 'yes',
            'new_idea_comment_require_auth' => 'yes',
            'enable_new_vote_submission'     => 'yes',
            'new_idea_vote_require_auth'    => 'yes',
            'auth_html' => "<p>Please login to vote, comment and add new ideas.</p> <a href='{login_url}'>Login</a>",
        ];

        if (defined('FLUENTCRM')) {

            $defaults['add_user_to_crm_new_idea_submission'] = 'no';
            $defaults['crm_tags'] = [];
            $defaults['crm_lists'] = [];

        } else {
            unset($settings['add_user_to_crm_new_idea_submission']);
            unset($settings['crm_tags']);
            unset($settings['crm_lists']);
        }

        

        return wp_parse_args($settings, $defaults);
    }
}
