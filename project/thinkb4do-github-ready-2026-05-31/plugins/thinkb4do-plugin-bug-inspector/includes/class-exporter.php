<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TBPBI_Exporter {
    public static function send_txt_download( string $filename, string $content ): void {
        if ( headers_sent() ) {
            wp_die( esc_html__( 'Cannot export because headers were already sent.', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $filename = sanitize_file_name( $filename );
        if ( ! $filename ) {
            $filename = 'thinkb4do-plugin-debug-report.txt';
        }

        nocache_headers();
        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $content ) );
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text download.
        exit;
    }
}
