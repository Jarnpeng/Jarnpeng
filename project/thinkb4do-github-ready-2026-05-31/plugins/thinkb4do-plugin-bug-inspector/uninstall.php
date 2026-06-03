<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Thinkb4do Plugin Bug Inspector stores only temporary scan transients and user meta.
// Transients expire automatically; no destructive cleanup is required by default.
