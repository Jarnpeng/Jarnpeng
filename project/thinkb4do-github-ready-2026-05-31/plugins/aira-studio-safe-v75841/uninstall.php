<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

$options = array(
    'aira_studio_safe_settings',
    'aira_studio_safe_api_secrets',
    'aira_studio_safe_docs',
    'aira_studio_safe_memory',
    'aira_studio_safe_update_notifications',
    'aira_studio_safe_interest_stats',
    'aira_studio_authorized_identity_index',
    'aira_studio_abs_power_capsule',
    'aira_studio_abs_power_log',
);

foreach ( $options as $option_name ) {
    delete_option( $option_name );
}

$transients = array(
    'aira_studio_system_check_cache',
);

foreach ( $transients as $transient_name ) {
    delete_transient( $transient_name );
}

wp_clear_scheduled_hook( 'aira_studio_sync_event' );
