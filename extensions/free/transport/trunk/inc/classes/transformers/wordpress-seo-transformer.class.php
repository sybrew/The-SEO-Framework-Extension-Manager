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
class WordPress_SEO_Transformer extends Core {

	/**
	 * Constructor, sets up vars.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {
		parent::construct();
		// static::$separators = [];
	}

	/**
	 * Converts Yoast SEO title syntax to human readable text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value       The old title value possibly unsafe for TSF.
	 * @param int    $object_id   The post, user, or term ID to transform.
	 * @param string $object_type The current object type.
	 * @return string The transformed title.
	 */
	public static function _title_syntax( $value, $object_id, $object_type ) {
		return self::$tsf->s_title_raw(
			static::_transform_syntax( $value, $object_id, $object_type )
		);
	}

	/**
	 * Converts Yoast SEO description syntax to human readable text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value       The old description value possibly unsafe for TSF.
	 * @param int    $object_id   The post, user, or term ID to transform.
	 * @param string $object_type The current object type.
	 * @return string The transformed description.
	 */
	public static function _description_syntax( $value, $object_id, $object_type ) {
		return self::$tsf->s_description_raw(
			static::_transform_syntax( $value, $object_id, $object_type )
		);
	}

	/**
	 * Converts Yoast SEO title/description syntax to human readable text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $text        The old description value possibly unsafe for TSF.
	 * @param int    $object_id   The post, user, or term ID to transform.
	 * @param string $object_type The current object type.
	 * @return string The transformed title or description.
	 */
	private static function _transform_syntax( $text, $object_id, $object_type ) {

		// %%id%% is the shortest valid tag... ish. Let's stop at 6.
		if ( \strlen( $text ) < 6 || false === strpos( $text, '%%' ) )
			return $text;

		if ( ! preg_match_all( '/%%([^%]+)%%/', $text, $matches ) )
			return $text;

		static::set_main_object_type( $object_type );
		static::{"set_{$object_type}"}( $object_id );

		$_replacements = [];

		$matches[0] = array_unique( $matches[0] );
		$matches[1] = array_intersect_key( $matches[1], $matches[0] );

		foreach ( $matches[1] as $i => $type ) {
			if ( isset( static::$replacements[ $type ] ) ) {
				$_replacements[ $matches[0][ $i ] ] = \call_user_func_array(
					static::$replacements[ $type ],
					[
						$text,
						$type,
						$matches[0][ $i ],
					]
				);
			} elseif (
				! \in_array( $type, static::$preserve, true ) &&
				! preg_match(
					sprintf( '/^(%s)/', static::$prefix_preserve_preg_quoted ),
					$type
				)
			) {
				$_replacements[ $matches[0][ $i ] ] = '';
			}
		}

		return static::_remove_duplicated_separators(
			strtr( $text, $_replacements )
		);
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
