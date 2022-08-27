<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transformer for TSF.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Stray_Private. Requires construct().
 */
class Temp_TSF_Transformer {
	use \TSF_Extension_Manager\Construct_Stray_Private;

	/**
	 * Converts TSF qubit robots-settings to Yoast SEO.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The old robots value possibly unsafe for Yoast SEO.
	 * @return int|null The sanitized qubit.
	 */
	public static function _robots_unqubit( $value ) {

		switch ( (int) $value ) {
			case -1:
				$value = 2; // Force index
				break;
			case 1:
				$value = 1; // Force noindex
				break;
			default:
			case 0:
				$value = null; // Default/unassigned
				break;
		}

		return $value;
	}

	/**
	 * Converts Yoast SEO advanced robots-settings to TSF's qubit.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The old robots value possibly unsafe for TSF.
	 * @return int|null The sanitized qubit.
	 */
	public static function _robots_advanced( $value ) {

		switch ( $value ) {
			case -1:
				$value = null; // unset
			case 1:
				$value = 'noarchive';
				break;
			default:
			case 0:
				$value = null; // Default/unassigned
				break;
		}

		return $value;
	}
}
