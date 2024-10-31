<?php
$allowed_roles = poc_plugin_roles();
unset( $allowed_roles['administrator'] );

return array(
    'label'     => __( 'General', 'poc-cd' ),
    'sections'  => array(
        'general'   => array(
            'title'         =>  __( 'General Settings', 'poc-cd' ),
            'description'   => '',
            'fields'        => array(
                'poc_cd_add_if_not_exists' => array(
                    'title'          => __( 'Force constants add', 'poc-cd' ),
                    'description'    => __( 'Add the missing constants to the wp-config.php.', 'poc-cd' ),
                    'type'           => 'checkbox',
                    'default'        => 'yes'
                ),
                'poc_cd_wp_debug' => array(
                    'title'          => __( 'WP_DEBUG', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/WP_DEBUG">http://codex.wordpress.org/WP_DEBUG</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_wp_debug_log' => array(
                    'title'          => __( 'WP_DEBUG_LOG', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/WP_DEBUG#WP_DEBUG_LOG">http://codex.wordpress.org/WP_DEBUG#WP_DEBUG_LOG</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_wp_debug_display' => array(
                    'title'          => __( 'WP_DEBUG_DISPLAY', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/WP_DEBUG#WP_DEBUG_DISPLAY">http://codex.wordpress.org/WP_DEBUG#WP_DEBUG_DISPLAY</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_script_debug' => array(
                    'title'          => __( 'SCRIPT_DEBUG', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Debugging_in_WordPress#SCRIPT_DEBUG">http://codex.wordpress.org/Debugging_in_WordPress#SCRIPT_DEBUG</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_savequeries' => array(
                    'title'          => __( 'SAVEQUERIES', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Debugging_in_WordPress#SAVEQUERIES">http://codex.wordpress.org/Debugging_in_WordPress#SAVEQUERIES</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_wp_post_revisions' => array(
                    'title'          => __( 'WP_POST_REVISIONS', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Editing_wp-config.php#Disable_Post_Revisions">http://codex.wordpress.org/Editing_wp-config.php#Disable_Post_Revisions</a>',
                    'type'           => 'checkbox',
                    'default'        => 'on'
                ),
                'poc_cd_concatenate_scripts' => array(
                    'title'          => __( 'CONCATENATE_SCRIPTS', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Editing_wp-config.php#Disable_Javascript_Concatenation">http://codex.wordpress.org/Editing_wp-config.php#Disable_Javascript_Concatenation</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_disallow_file_mods' => array(
                    'title'          => __( 'DISALLOW_FILE_MODS', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Editing_wp-config.php#Disable_Plugin_and_Theme_Update_and_Installation">http://codex.wordpress.org/Editing_wp-config.php#Disable_Plugin_and_Theme_Update_and_Installation</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_disallow_file_edit' => array(
                    'title'          => __( 'DISALLOW_FILE_EDIT', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Editing_wp-config.php#Disable_the_Plugin_and_Theme_Editor">http://codex.wordpress.org/Editing_wp-config.php#Disable_the_Plugin_and_Theme_Editor</a>',
                    'type'           => 'checkbox',
                    'default'        => 'off'
                ),
                'poc_cd_automatic_updater_disabled' => array(
                    'title'          => __( 'AUTOMATIC_UPDATER_DISABLED', 'poc-cd' ),
                    'description'    => '<a href="http://codex.wordpress.org/Editing_wp-config.php#Disable_WordPress_Auto_Updates">http://codex.wordpress.org/Editing_wp-config.php#Disable_WordPress_Auto_Updates</a>',
                    'type'           => 'checkbox',
                    'default'        => 'on'
                ),
            )
        ),
    )
);
