<?php
/*
  Copyright 2005 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once ( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 
    'core' . DIRECTORY_SEPARATOR . 'api.php' );

function theme_head()
{
    global $g_web_directory;
    $t_style = $g_web_directory . 'themes/clean/theme.css';
    print_css_link( $t_style );
}

function theme_body( $p_page_data )
{
    if ( false === $p_page_data )
    {
        # @@@ Handle not indexed (and auto index off)
        return;
    }

    global
        $g_note_add_page, $s_add_note_link,
        $s_manage, $s_admin,
        $g_web_directory,
        $g_theme;

    $t_notes = $p_page_data['notes'];
    $t_page = $p_page_data['page'];

    $t_page_id = $p_page_data['id'];

    $t_images_base = $g_web_directory . 'themes/' . $g_theme . '/images/';
    $prev_picture = $t_images_base . 'caret_left.gif';
    $next_picture = $t_images_base . 'caret_right.gif';

    if ( false === $p_page_data['preview'] )
    {
        $t_link_start = "<a href=\"$g_note_add_page?f_page_id=$t_page_id\">";
        $t_link_end = '</a>';
    }
    else
    {
        $t_link_start = $t_link_end = '';
    }

    #
    # HEADER
    #

    $t_about_page = config_get( 'about_page' );

    echo <<<HTML
<div class="webnotes">
    <h3 class="webnotes-headline">Notes</h3>
    <p class="webnotes-page">$t_page
    <span class="webnotes-dash">&mdash;</span> <span class="webnotes-add">${t_link_start}Add${t_link_end}</span>
    </p>
HTML;

    #
    # NOTES
    #

    if ( 0 === count( $t_notes ) )
    {
        echo <<<HTML
    <p class="webnotes-empty">There are no user contributed notes for this page.</p>
HTML;
    }
    else
    {
        for ( $i = 0; $i < count( $t_notes ); $i++ )
        {
            $t_moderation = '';
            $t_note_info = $t_notes[$i];

            if ( false === $p_page_data['preview'] )
            {
                if ( access_check_action( ACTION_NOTES_MODERATE ) )
                {
                    $t_url = $p_page_data['url'];
                    $t_moderation = '';

                    if ( $t_note_info['visible'] != NOTE_VISIBLE_ACCEPTED )
                    {
                        $t_moderation .= link_note_action( $t_note_info['id'], 'accept', $t_url, 
                            access_check_action( ACTION_NOTES_MODERATE_ACCEPT ) ) . ' ';
                    }
                    if ( $t_note_info['visible'] != NOTE_VISIBLE_PENDING )
                    {
                        $t_moderation .= link_note_action( $t_note_info['id'], 'queue', $t_url, 
                            access_check_action( ACTION_NOTES_MODERATE_QUEUE ) ) . ' ';
                    }
                    if ( $t_note_info['visible'] != NOTE_VISIBLE_DECLINED )
                    {
                        $t_moderation .= link_note_action( $t_note_info['id'], 'decline', $t_url, 
                            access_check_action( ACTION_NOTES_MODERATE_DECLINE ) ) . ' ';
                    }
                    if ( $t_note_info['visible'] != NOTE_VISIBLE_ARCHIVED )
                    {
                        $t_moderation .= link_note_action( $t_note_info['id'], 'archive', $t_url, 
                            access_check_action( ACTION_NOTES_MODERATE_ARCHIVE ) ) . ' ';
                    }
                    
                    $t_moderation .= link_note_action( $t_note_info['id'], 'edit', $t_url, 
                        access_check_action( ACTION_NOTES_EDIT ) );

                    if ( $t_note_info['visible'] != NOTE_VISIBLE_DELETED )
                    {
                        $t_moderation .= link_note_action( $t_note_info['id'], 'delete', $t_url, 
                            access_check_action( ACTION_NOTES_MODERATE_DELETE ) );
                    }
                }
            }

            if ( isset( $t_note_info['id'] ) && ( $t_note_info['id'] != 0 ) )
            {
                $t_id = (integer)$t_note_info['id'];
                $t_visibility = '';
                if ( NOTE_VISIBLE_ACCEPTED != $t_note_info['visible'] )
                {
                    $t_visibility = '(' . note_get_visibility_str( $t_note_info['visible'] ) . ') - ';
                }
                if ( access_check_action( ACTION_NOTES_MODERATE ) )
                {
                    $t_id_view = "<span class=\"webnotes-edit\"><span class=\"webnotes-dash\">&mdash;</span> $t_visibility $t_moderation</span>";
                }
                $t_id_bookmark = "<a name=\"$t_id\">$t_id</a>";
            }
            else
            {
                $t_id_view = '&nbsp;';
                $t_id_bookmark = '';
            }

            if ( isset( $t_note_info['email'] ) )
            {
                if ( access_check_action( ACTION_NOTES_MODERATE ) )
                {
                    $t_email = '<a href="mailto:'.$t_note_info['email'].'">'.$t_note_info['email'].'</a>';
                }
                else
                {
                    $t_email = str_replace('@',"-at-",substr($t_note_info['email'],0,15)) . '...';
                }
            }
            else
            {
                $t_email = '';
            }

            if ( isset( $t_note_info['date'] ) )
            {
                $t_date = date('Y-m-d G:i', $t_note_info['date']);
            }
            else
            {
                $t_date = '';
            }

            if ( isset( $t_note_info['note'] ) )
            {
                $t_note = nl2br($t_note_info['note']);
            }
            else
            {
                $t_note = '&nbsp;';
            }

            echo <<<HTML
    <div class="webnotes-entry">
        <p>
        <span class="webnotes-id">${t_id_bookmark}</span>
        <span class="webnotes-email">$t_email</span> on <span class="webnotes-date">$t_date</span>
        $t_id_view
        </p>
        <blockquote class="webnotes-text">${t_note}</blockquote>
    </div>
HTML;
        }
    }

    #
    # FOOTER
    #

    if ( empty( $p_page_data['prev_page'] ) )
    {
        $t_prev_text = '';
    }
    else
    {
        $t_prev_text = "<img src=\"$prev_picture\" width=\"11\" height=\"7\" alt=\"" . $p_page_data['prev_page'] . "\" />" .
        link_create( $p_page_data['prev_url'], $p_page_data['prev_page'], true, '', '' );
    }

    if ( empty( $p_page_data['next_page' ] ) )
    {
        $t_next_text = '';
    }
    else
    {
        $t_next_text = link_create( $p_page_data['next_url'], $p_page_data['next_page'], true, '', '' ) .
        "<img src=\"$next_picture\" width=\"11\" height=\"7\" alt=\"" . $p_page_data['next_page'] . "\" />";
    }

    if ( empty( $t_prev_text ) && empty( $t_next_text ) )
    {
        $t_navigation_row = '';
    }
    else
    {
        $t_navigation_row = "<p>$t_prev_text &mdash; $t_next_text</p>";
    }

    if ( false === $p_page_data['preview'] )
    {
        $t_link_start = "<a href=\"$g_note_add_page?f_page_id=$t_page_id\">";
        $t_link_end = '</a>';
    }
    else
    {
        $t_link_start = $t_link_end = '';
    }

    if ( 0 !== count( $t_notes ) )
    {
        echo <<<HTML
    <p class="webnotes-page">$t_page
    <span class="webnotes-dash">&mdash;</span> <span class="webnotes-add">${t_link_start}Add${t_link_end}</span>
    </p>
HTML;
    }

    if ( false === $p_page_data['preview'] )
    {
        $t_last_updated = date('Y-m-d G:i:s', $p_page_data['last_updated']);
        echo <<<HTML
    $t_navigation_row
    <p class="webnotes-updated">Last updated: $t_last_updated</p>
HTML;
    }

    echo <<<HTML
</div>
HTML;

    if ( ( false === $p_page_data['preview'] ) && ( access_is_logged_in() ) )
    {
        echo '<div class="webnotes-admin">';
        print_admin_menu();
        echo '</div>';
    }
}
?>
