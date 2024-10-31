<?php
/**
 * Payment Gateways Caller for WP e-Commerce
 *
 * Allows to include a merchant file through a GET request without specifying
 * its whole URL. Just call `http://yoursite.com/?load_merchant=filename`,
 * without the `.php` extension, and you're done. Please note that these
 * requests cannot be used to display pages.
 *
 * @package   WPSC_Gateways_Caller
 * @author    Andrés Villarreal <andrezrv@gmail.com>
 * @license   GPL2
 * @link      http://github.com/andrezrv/wp-e-commerce-merchants-caller
 * @copyright 2013 Andrés Villarreal
 *
 * @wordpress-plugin
 * Plugin name: Payment Gateways Caller for WP e-Commerce
 * Plugin URI: http://github.com/andrezrv/wp-e-commerce-merchants-caller
 * Description: Allows to include a merchant file through a GET request without specifying its whole URL. Just call <code>http://yoursite.com/?load_merchant=filename</code>, without the <code>.php</code> extension, and you're done. Please note that these requests cannot be used to display pages.
 * Author: Andrés Villarreal
 * Author URI: http://about.me/andrezrv
 * Version: 1.0.1
 * License: GPL2
 */
class WPSC_Gateways_Caller {

	var $file_path;
	var $filename;
	var $merchants_path;

	/**
	 * Add action hook to load merchant on "plugins_loaded" execution.
	 */
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Load merchant file in case the required file exists.
	 */
	function init() {
		$this->merchants_path = $this->get_merchants_path();
		$this->filename = $this->get_filename();
			do_action( 'wpscmc_before_gateway_load' );
			do_action( 'wpscmc_after_gateway_loade' );
		if ( $this->file_path = $this->get_merchant_file() ) {
			$this->load_merchant();
		}
	}

	/**
	 * Load merchant gith given $_GET['load_merchant']
	 */
	function load_merchant() {
		require( $this->file_path );
		die(); // This is required in order to avoid theme loading.
	}

	/**
	 * Prevent LFI vulnerability.
	 */
	function get_filename( $path = '' ) {
		if ( !empty( $_GET['load_merchant'] ) ) {
			$path = str_replace( '..', '', $_GET['load_merchant'] );
			$path = str_replace( '/', '', $path );
		}
		return $path;
	}

	/**
	 * Get absolute path for merchant file.
	 */
	function get_merchant_file() {
		if (   !is_admin() 
			&& is_file( $file = $this->merchants_path . '/'. $this->filename . '.php' )
		) {
			return $file;
		}
		return '';
	}

	/**
	 * Obtain path to "wpsc-merchants" folder by looking for WP_eCommerce class
	 * location via ReflectionClass.
	 */
	function get_merchants_path( $path = '' ) {
		/* ReflectionClass throws an exception if it doesn't find what is 
		 * looking for, so we need a try/catch structure. */ 
		try {
			$reflector = new ReflectionClass( 'WP_eCommerce' );
			$filename = $reflector->getFileName();
			$path = dirname( $filename ) . '/wpsc-merchants';
		} catch( Exception $e ) {
			// Do nothing.
		}
		return $path;
	}

}

new WPSC_Gateways_Caller;
