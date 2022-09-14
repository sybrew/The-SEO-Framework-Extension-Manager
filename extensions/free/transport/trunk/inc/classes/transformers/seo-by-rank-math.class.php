<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * Copyright (C) 2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Transformer for Rank Math.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Core_Static_Final_Instance.
 */
class SEO_By_Rank_Math extends Core {
	use \TSF_Extension_Manager\Construct_Core_Static_Unique_Instance_Master;

	/**
	 * @since 1.0.0
	 * @override Prevent writing to self.
	 * @see parent::reset_replacements()
	 * @var array[string:callable] The replacement types by name.
	 */
	protected static $replacements = [];

	/**
	 * @since 1.0.0
	 * @override Prevent writing to self.
	 * @see parent::reset_replacements()
	 * @var string[] The non-replacement types' prefixes.
	 */
	protected static $prefix_preserve = [];

	/**
	 * Resets replacement values, if needed.
	 *
	 * NOTE: When overriding, you're likely also overriding self::properties:
	 * register those self::properties statically to the child class to exploit
	 * late-static binding for properties.
	 *
	 * See for reference RankMath\Admin\Importers\Yoast::convert_variables()
	 *
	 * @since 1.0.0
	 * @override
	 */
	protected static function reset_replacements() {
		parent::reset_replacements();

		static::$replacements['term'] = static::$replacements['term_title'];
		unset(
			static::$replacements['term_title'],
		);

		static::$prefix_preserve = [
			'customfield',
			'customterm',
		];
	}

	/**
	 * Converts Rank Math title syntax to human readable text.
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
	 * Converts Rank Math description syntax to human readable text.
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
	 * Converts Rank Math title/description syntax to human readable text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $text        The old description value possibly unsafe for TSF.
	 * @param int    $object_id   The post, user, or term ID to transform.
	 * @param string $object_type The current object type.
	 * @return string The transformed title or description.
	 */
	private static function _transform_syntax( $text, $object_id, $object_type ) {

		// %id% is the shortest valid tag... ish. Let's stop at 4.
		if ( \strlen( $text ) < 4 || false === strpos( $text, '%' ) )
			return $text;

		// Rank Math supports %var(something)% syntax, whence they extract something.
		// We do not care for (something), for now at least. Ignore the basic syntax:
		if ( ! preg_match_all( '/%([^%]+)%/', $text, $matches ) )
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

		return self::_trim_separators(
			self::_remove_duplicated_separators(
				strtr( $text, $_replacements )
			)
		);
	}

	/**
	 * Converts Rank Math robots-text to TSF's qubit.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The old robots value possibly unsafe for TSF.
	 * @return int|null The sanitized qubit.
	 */
	public static function _robots_text_to_qubit( $value ) {

		// Future-proofed. TSF "only" supports 'noindex', 'nofollow', and 'noarchive'.
		if ( \in_array( $value, [ 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ], true ) ) {
			$value = 1; // Force no_robots
		} else {
			$value = null; // Default/unassigned
		}

		return $value;
	}
}
