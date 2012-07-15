<?php 
/**
 * Adds Proxy support to the WordPress HTTP API.
 *
 * There are caveats to proxy support. It requires that defines be made in the wp-config.php file to
 * enable proxy support. There are also a few filters that plugins can hook into for some of the
 * constants.
 *
 * Please note that only BASIC authentication is supported by most transports.
 * cURL MAY support more methods (such as NTLM authentication) depending on your environment.
 *
 * The constants are as follows:
 * <ol>
 * <li>WP_PROXY_HOST - Enable proxy support and host for connecting.</li>
 * <li>WP_PROXY_PORT - Proxy port for connection. No default, must be defined.</li>
 * <li>WP_PROXY_USERNAME - Proxy username, if it requires authentication.</li>
 * <li>WP_PROXY_PASSWORD - Proxy password, if it requires authentication.</li>
 * <li>WP_PROXY_BYPASS_HOSTS - Will prevent the hosts in this list from going through the proxy.
 * You do not need to have localhost and the blog host in this list, because they will not be passed
 * through the proxy. The list should be presented in a comma separated list, wildcards using * are supported, eg. *.wordpress.org</li>
 * </ol>
 *
 * An example can be as seen below.
 * <code>
 * define('WP_PROXY_HOST', '192.168.84.101');
 * define('WP_PROXY_PORT', '8080');
 * define('WP_PROXY_BYPASS_HOSTS', 'localhost, www.example.com, *.wordpress.org');
 * </code>
 *
 * @link http://core.trac.wordpress.org/ticket/4011 Proxy support ticket in WordPress.
 * @link http://core.trac.wordpress.org/ticket/14636 Allow wildcard domains in WP_PROXY_BYPASS_HOSTS
 * @since 2.8
 */
class EasyHttp_Proxy {

	static $host;
	static $port;
	static $username;
	static $password;
	static $bypassHosts;
	
	/**
	 * Whether proxy connection should be used.
	 *
	 * @since 2.8
	 * @use WP_PROXY_HOST
	 * @use WP_PROXY_PORT
	 *
	 * @return bool
	 */
	function is_enabled() {
		return isset(self::$host) && isset(self::$port);
	}

	/**
	 * Whether authentication should be used.
	 *
	 * @since 2.8
	 * @use WP_PROXY_USERNAME
	 * @use WP_PROXY_PASSWORD
	 *
	 * @return bool
	 */
	function use_authentication() {
		return isset(self::$username) && isset(self::$password);
	}

	/**
	 * Retrieve the host for the proxy server.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function host() {
		return self::$host;
	}

	/**
	 * Retrieve the port for the proxy server.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function port() {
		return self::$port;
	}

	/**
	 * Retrieve the username for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function username() {
		return self::$username;
	}

	/**
	 * Retrieve the password for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function password() {
		return self::$password;
	}

	/**
	 * Retrieve authentication string for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function authentication() {
		return $this->username() . ':' . $this->password();
	}

	/**
	 * Retrieve header string for proxy authentication.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	function authentication_header() {
		return 'Proxy-Authorization: Basic ' . base64_encode( $this->authentication() );
	}

	/**
	 * Whether URL should be sent through the proxy server.
	 *
	 * We want to keep localhost and the blog URL from being sent through the proxy server, because
	 * some proxies can not handle this. We also have the constant available for defining other
	 * hosts that won't be sent through the proxy.
	 *
	 * @uses WP_PROXY_BYPASS_HOSTS
	 * @since 2.8.0
	 *
	 * @param string $uri URI to check.
	 * @return bool True, to send through the proxy and false if, the proxy should not be used.
	 */
	function send_through_proxy( $uri ) {
		// parse_url() only handles http, https type URLs, and will emit E_WARNING on failure.
		// This will be displayed on blogs, which is not reasonable.
		$check = @parse_url($uri);

		// Malformed URL, can not process, but this could mean ssl, so let through anyway.
		if ( $check === false )
			return true;

		if ( $check['host'] == 'localhost' || isset($_SERVER['HTTP_HOST']) && $check['host'] == $_SERVER['HTTP_HOST'] )
			return false;

		if ( !isset(self::$bypassHosts) )
			return true;

		static $bypass_hosts;
		static $wildcard_regex = false;
		if ( null == $bypass_hosts ) {
			$bypass_hosts = preg_split('|,\s*|', self::$bypassHosts);

			if ( false !== strpos(self::$bypassHosts, '*') ) {
				$wildcard_regex = array();
				foreach ( $bypass_hosts as $host )
					$wildcard_regex[] = str_replace('\*', '[\w.]+?', preg_quote($host, '/'));
				$wildcard_regex = '/^(' . implode('|', $wildcard_regex) . ')$/i';
			}
		}

		if ( !empty($wildcard_regex) )
			return !preg_match($wildcard_regex, $check['host']);
		else
			return !in_array( $check['host'], $bypass_hosts );
	}
}
