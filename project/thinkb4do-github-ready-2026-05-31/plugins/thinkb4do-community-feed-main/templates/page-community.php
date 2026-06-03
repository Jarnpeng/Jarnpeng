<?php
/**
 * Template Name: หน้าชุมชน Thinkb4do
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
get_header();
echo tb4cf_render_community(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
