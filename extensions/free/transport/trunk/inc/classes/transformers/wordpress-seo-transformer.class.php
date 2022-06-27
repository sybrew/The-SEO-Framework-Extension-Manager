<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transformer for Yoost SEO.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Stray_Private. Requires construct().
 */
class WordPress_SEO_Transformer {
	use \TSF_Extension_Manager\Construct_Stray_Private;

	/**
	 * Converts Yoast SEO title syntax to human readable text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The old title value possibly unsafe for TSF.
	 * @return int|null The sanitized qubit.
	 */
	public static function _title_syntax( $value ) {
		// var_dump() TODO
		return $value;
	}

	/**
	 * Converts Yoast SEO description syntax to human readable text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The old description value possibly unsafe for TSF.
	 * @return int|null The sanitized qubit.
	 */
	public static function _description_syntax( $value ) {
		// var_dump() TODO
		return $value;
	}

	/**
	 * Converts Yoast SEO robots-settings to TSF's qubit.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The old robots value possibly unsafe for TSF.
	 * @return int|null The sanitized qubit.
	 */
	public static function _robots_qubit( $value ) {

		switch ( (int) $value ) {
			case 2:
				$value = -1; // Force allow_robots
				break;
			case 1:
				$value = 1; // Force no_robots
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

		if ( \in_array( $value, [ 'noarchive', 'noimageindex', 'nosnippet' ], true ) ) {
			$value = 1; // Force no_robots
		} else {
			$value = null; // Default/unassigned
		}

		return $value;
	}
}
