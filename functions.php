<?php

/**
 * Determines a writable directory for temporary files.
 * Function's preference is to WP_CONTENT_DIR followed by the return value of <code>sys_get_temp_dir()</code>, before finally defaulting to /tmp/
 *
 * In the event that this function does not find a writable location, It may be overridden by the <code>WP_TEMP_DIR</code> constant in your <code>wp-config.php</code> file.
 *
 * @since 2.5.0
 *
 * @return string Writable temporary directory
 */
function get_temp_dir() {
	static $temp;
	if ( defined('WP_TEMP_DIR') )
		return trailingslashit(WP_TEMP_DIR);

	if ( $temp )
		return trailingslashit($temp);

	$temp = WP_CONTENT_DIR . '/';
	if ( is_dir($temp) && @is_writable($temp) )
		return $temp;

	if  ( function_exists('sys_get_temp_dir') ) {
		$temp = sys_get_temp_dir();
		if ( @is_writable($temp) )
			return trailingslashit($temp);
	}

	$temp = ini_get('upload_tmp_dir');
	if ( is_dir($temp) && @is_writable($temp) )
		return trailingslashit($temp);

	$temp = '/tmp/';
	return $temp;
}
