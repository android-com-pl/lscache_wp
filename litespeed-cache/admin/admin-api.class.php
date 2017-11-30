<?php
/**
 * Admin API
 *
 * @since      1.5
 * @package    LiteSpeed_Cache
 * @subpackage LiteSpeed_Cache/admin
 * @author     LiteSpeed Technologies <info@litespeedtech.com>
 */
class LiteSpeed_Cache_Admin_API
{
	private static $_instance ;

	private $_sapi_key ;

	const DB_API_KEY = 'litespeed_api_key' ;
	const DB_API_KEY_HASH = 'litespeed_api_key_hash' ;

	// For each request, send a callback to confirm
	const TYPE_REQUEST_CALLBACK = 'request_callback' ;
	const TYPE_NOTIFY_IMG = 'notify_img' ;
	const TYPE_CHECK_IMG = 'check_img' ;

	const IAPI_ACTION_REQUEST_KEY = 'request_key' ;
	const IAPI_ACTION_MEDIA_SYNC_DATA = 'media_sync_data' ;
	const IAPI_ACTION_REQUEST_OPTIMIZE = 'request_optimize' ;
	const IAPI_ACTION_PULL_IMG = 'client_pull' ;
	const IAPI_ACTION_PULL_IMG_FAILED = 'client_pull_failed' ;
	const IAPI_ACTION_ENV_REPORT = 'env_report' ;

	/**
	 * Init
	 *
	 * @since  1.5
	 * @access private
	 */
	private function __construct()
	{
		$this->_sapi_key = get_option( self::DB_API_KEY ) ?: '' ;
	}

	/**
	 * Handle aggressive callback requests from LiteSpeed server
	 *
	 * @since  1.6
	 * @access public
	 */
	public static function sapi_aggressive_callback()
	{
		$instance = self::get_instance() ;

		switch ( LiteSpeed_Cache_Router::verify_type() ) {
			case self::TYPE_NOTIFY_IMG :
				LiteSpeed_Cache_Media::get_instance()->notify_img() ;
				break ;

			case self::TYPE_CHECK_IMG :
				$instance->validate_lsserver() ;
				LiteSpeed_Cache_Media::get_instance()->check_img() ;
				break ;

			default:
				break ;
		}

		exit ;
	}

	/**
	 * Validate litespeed api server IP
	 *
	 * @since  1.6.5
	 * @access public
	 */
	public function validate_lsserver()
	{
		$ip = gethostbyname( 'wp.api.litespeedtech.com' ) ;
		if ( $ip != LiteSpeed_Cache_Router::get_ip() ) {
			exit( 'wrong ip' ) ;
		}
	}

	/**
	 * Handle passive callback requests from LiteSpeed server
	 *
	 * @since  1.6
	 * @access public
	 */
	public static function sapi_passive_callback()
	{
		$instance = self::get_instance() ;

		switch ( LiteSpeed_Cache_Router::verify_type() ) {
			case self::TYPE_REQUEST_CALLBACK :
				$instance->_request_callback() ;
				break ;

			default:
				break ;
		}

		exit ;
	}

	/**
	 * request key callback from LiteSpeed
	 *
	 * @since  1.5
	 * @access private
	 */
	private function _request_callback()
	{
		$key_hash = get_option( self::DB_API_KEY_HASH ) ;
		LiteSpeed_Cache_Log::debug( 'IAPI __callback request hash: ' . $key_hash ) ;
		exit( $key_hash ) ;
	}

	/**
	 * Check if is valid callback from litespeed passive request
	 *
	 * @since  1.5
	 * @access public
	 * @return bool True if correct
	 */
	public static function sapi_valiate_passive_callback()
	{
		if ( empty( $_REQUEST[ 'hash' ] ) ) {
			LiteSpeed_Cache_Log::debug( 'IAPI __callback bypassed passive check' ) ;
			return false ;
		}
		$instance = self::get_instance() ;

		// use tmp hash to check
		$key_hash = get_option( self::DB_API_KEY_HASH ) ;
		$hash_check = md5( $key_hash ) === $_REQUEST[ 'hash' ] ;

		LiteSpeed_Cache_Log::debug( 'IAPI __callback hash check ' . $key_hash . ': ' . ( $hash_check ? 'passed' : 'failed' ) ) ;

		return $hash_check ;
	}

	/**
	 * Check if is valid callback from litespeed aggressive request
	 *
	 * @since  1.6
	 * @access public
	 * @return bool True if correct
	 */
	public static function sapi_validate_aggressive_callback()
	{
		$instance = self::get_instance() ;

		// don't have auth_key yet
		if ( ! $instance->_sapi_key ) {
			LiteSpeed_Cache_Log::debug( 'IAPI __callback aggressive check failed: No init key' ) ;
			return false ;
		}

		// Once client has auth_key, each time when callback to check, need to carry on this key
		if ( empty( $_REQUEST[ 'auth_key' ] ) ) {
			LiteSpeed_Cache_Log::debug( 'IAPI __callback aggressive check failed: lack of auth_key' ) ;
			return false ;
		}

		$res = md5( $instance->_sapi_key ) === $_REQUEST[ 'auth_key' ] ;
		LiteSpeed_Cache_Log::debug( 'IAPI __callback aggressive auth_key check: ' . ( $res ? 'passed' : 'failed' ) ) ;
		return $res ;
	}

	/**
	 * Post data to LiteSpeed server
	 *
	 * @since  1.6
	 * @access public
	 * @param  array $data
	 */
	public static function post( $action, $data = false, $server = false )
	{
		$instance = self::get_instance() ;

		/**
		 * All requests must have api_key first
		 * @since  1.6.5
		 */
		if ( ! $instance->_sapi_key ) {
			$instance->_request_key() ;
		}

		return $instance->_post( $action, $data, $server ) ;
	}

	/**
	 * request key from LiteSpeed
	 *
	 * @since  1.5
	 * @access private
	 */
	private function _request_key()
	{
		// Send request to LiteSpeed
		$json = $this->_post( self::IAPI_ACTION_REQUEST_KEY, home_url() ) ;

		// Check if get key&server correctly
		if ( empty( $json[ 'auth_key' ] ) ) {
			LiteSpeed_Cache_Log::debug( 'IAPI request key failed: ', $json ) ;
			$msg = sprintf( __( 'IAPI Error %s', 'litespeed-cache' ), $json ) ;
			LiteSpeed_Cache_Admin_Display::error( $msg ) ;
			return ;
		}

		// store data into option locally
		update_option( self::DB_API_KEY, $json[ 'auth_key' ] ) ;
		LiteSpeed_Cache_Log::debug( 'IAPI applied auth_key' ) ;

		$this->_sapi_key = $json[ 'auth_key' ] ;

	}

	/**
	 * Post data to LiteSpeed server
	 *
	 * @since  1.6
	 * @access private
	 * @param  array $data
	 */
	private function _post( $action, $data = false, $server = false )
	{
		$hash = Litespeed_String::rrand( 16 ) ;
		// store hash
		update_option( self::DB_API_KEY_HASH, $hash ) ;

		if ( $server == false ) {
			$server = 'https://wp.api.litespeedtech.com' ;
		}

		$url = $server . '/' . $action ;

		LiteSpeed_Cache_Log::debug( 'IAPI posting to : ' . $url ) ;

		$param = array(
			'auth_key'	=> $this->_sapi_key,
			'v'	=> LiteSpeed_Cache::PLUGIN_VERSION,
			'hash'	=> $hash,
			'data' => $data,
		) ;
		/**
		 * Extended timeout to avoid cUrl 28 timeout issue as we need callback validation
		 * @since 1.6.4
		 */
		$response = wp_remote_post( $url, array( 'body' => $param, 'timeout' => 15 ) ) ;

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message() ;
			LiteSpeed_Cache_Log::debug( 'IAPI failed to post: ' . $error_message ) ;
			return $error_message ;
		}

		// parse data from server
		$json = json_decode( $response[ 'body' ], true ) ;

		if ( ! is_array( $json ) ) {
			LiteSpeed_Cache_Log::debug( 'IAPI failed to decode post json: ' . $response[ 'body' ] ) ;
			return $response[ 'body' ] ;
		}

		if ( ! empty( $json[ 'err' ] ) ) {
			LiteSpeed_Cache_Log::debug( 'IAPI err: ' . $json[ 'err' ] ) ;
			$msg = __( 'Failed to communicate with LiteSpeed server', 'litespeed-cache' ) . ': ' . $json[ 'err' ] ;
			if ( ! empty( $json[ 'links' ] ) ) {
				foreach ( $json[ 'links' ] as $v ) {
					$msg .= ' ' . sprintf( '<a href="%s" class="%s" target="_blank">%s</a>', $v[ 'link' ], ! empty( $v[ 'cls' ] ) ? $v[ 'cls' ] : '', $v[ 'title' ] ) ;
				}
			}
			LiteSpeed_Cache_Admin_Display::error( $msg ) ;
			return null ;
		}

		return $json ;
	}

	/**
	 * Get the current instance object.
	 *
	 * @since 1.5
	 * @access public
	 * @return Current class instance.
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self() ;
		}

		return self::$_instance ;
	}
}