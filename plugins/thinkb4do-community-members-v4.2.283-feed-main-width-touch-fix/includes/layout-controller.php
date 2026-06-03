<?php
/**
 * Lightweight layout controller for Thinkb4do Community modules.
 *
 * v4.2.231: Main Community now renders center feed only. Left / Right sidebars are separated into their own desktop-only plugins.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;

/**
 * Return the absolute path of a community module template.
 *
 * @param string $module Module key: left-sidebar, center-feed, right-sidebar.
 * @return string
 */
function tb4cm_get_module_file( $module ) {
    $module = sanitize_key( (string) $module );
    $allowed = [
        'left-sidebar'  => 'left-sidebar.php',
        'center-feed'   => 'center-feed.php',
        'right-sidebar' => 'right-sidebar.php',
    ];

    $filename = isset( $allowed[ $module ] ) ? $allowed[ $module ] : '';
    $path     = $filename ? TB4CM_DIR . 'includes/modules/' . $filename : '';

    return (string) apply_filters( 'tb4c_community_module_file', $path, $module );
}

/**
 * Layout manifest for Settings / future Elementor / future Gutenberg control.
 *
 * @return array<string,array<string,string>>
 */
function tb4cm_get_community_layout_manifest() {
    $manifest = [
        'center-feed' => [
            'label'       => 'Center Feed Module',
            'role'        => 'Stream หลัก / Composer / Reels / Posts',
            'width'       => '700px desktop readable stream',
            'file'        => 'includes/modules/center-feed.php',
            'css'         => 'assets/community-clean-ui-reset.css',
        ],
    ];

    return (array) apply_filters( 'tb4c_community_layout_manifest', $manifest );
}

/**
 * Tiny critical guard. Main styling is in the module stylesheet; this keeps the
 * page readable if cache/CDN delays the final CSS for a moment.
 *
 * @return string
 */
function tb4cm_module_layout_inline_style_42164() {
    return '<style id="tb4c-module-layout-critical-42188">#tb4c-community-app.tb4c-v42188-complete-decoration{box-sizing:border-box}#tb4c-community-app.tb4c-v42188-complete-decoration *{box-sizing:border-box}#tb4c-community-app.tb4c-v42188-complete-decoration .tb4c-module-grid{display:grid;grid-template-columns:minmax(0,720px);justify-content:center;gap:0;align-items:start}#tb4c-community-app.tb4c-v42188-complete-decoration .tb4c-social-feed{min-width:0}#tb4c-community-app.tb4c-v42188-complete-decoration .tb4c-social-left,#tb4c-community-app.tb4c-v42188-complete-decoration .tb4c-social-right{display:none!important}@media(max-width:860px){#tb4c-community-app.tb4c-v42188-complete-decoration .tb4c-module-grid{display:block}}</style>';
}
