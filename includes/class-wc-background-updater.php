<?php
/**
 * background updater.
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @author   WooThemes
 * @category Admin
 * @package  WooCommerce/Classes
 * @version  2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( 'libraries/wp-async-request.php' );
include_once( 'libraries/wp-background-process.php' );

/**
 * WC_Background_Updater Class.
 */
class WC_Background_Updater extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wc_updater';

	/**
	 * Dispatch
	 */
	public function dispatch() {
		WC_Admin_Notices::add_notice( 'updating' );
		parent::dispatch();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'WC_UPDATING' ) ) {
			define( 'WC_UPDATING', true );
		}

		$logger = new WC_Logger();

		include_once( 'wc-update-functions.php' );

		if ( is_callable( $callback ) ) {
			$logger->add( 'wc_db_updates', sprintf( 'Running %s callback', $callback ) );
			call_user_func( $callback );
			$logger->add( 'wc_db_updates', sprintf( 'Finished %s callback', $callback ) );
		} else {
			$logger->add( 'wc_db_updates', sprintf( 'Could not find %s callback', $callback ) );
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$logger = new WC_Logger();

		$logger->add( 'wc_db_updates', 'Data update complete' );
		WC_Install::update_db_version();
		parent::complete();
	}
}
