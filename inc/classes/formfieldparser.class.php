<?php
/**
 * @package TSF_Extension_Manager\Classes
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2019-2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\FormFieldParser.
 *
 * Contains HTML to PHP form structs.
 * Importing this class for clean code is recommended.
 *
 * @see <http://php.net/manual/en/language.namespaces.importing.php>
 *
 * @since 2.2.0
 * @access public
 * @final
 */
final class FormFieldParser {
	use Construct_Core_Static_Final;

	/**
	 * Loops through multidimensional keys and values to find the corresponding one.
	 *
	 * Expected not to go beyond 10 key depth.
	 * CAUTION: 2nd parameter is passed by reference and it will be annihilated.
	 *
	 * @since 2.2.0
	 *
	 * @param array|string $keys  The keys that collapse with $value. For performance
	 *                            benefits, the last value should be a string.
	 * @param array|string $value The values that might contain $keys' value.
	 *                            Passed by reference for huge performance improvement.
	 * @return mixed|null Null if not found. Value otherwise.
	 */
	public static function get_mda_value( $keys, &$value ) {

		//= Because it's cast to array, the return will always be inside this loop.
		foreach ( (array) $keys as $k => $v ) {
			if ( \is_array( $v ) ) {
				return isset( $value[ $k ] ) ? static::get_mda_value( $v, $value[ $k ] ) : null;
			} else {
				if ( $k ) {
					return isset( $value[ $k ][ $v ] ) ? $value[ $k ][ $v ] : null;
				}

				return isset( $value[ $v ] ) ? $value[ $v ] : null;
			}
		}
	}

	/**
	 * Converts single dimensional strings from matosa to a multidimensional array.
	 *
	 * Great for parsing form array keys.
	 * umatosa: "Undo Multidimensional Array TO Single Array"
	 *
	 * Direct matosa to umatosa:
	 * Example: '1[2][3]=value';
	 * Becomes: [ 1 => [ 2 => [ 3 => [ 'value' ] ] ] ];
	 *
	 * From form key:
	 * Example: '1[2][3][value]';
	 * Becomes: [ 1 => [ 2 => [ 3 => [ 'value' ] ] ] ];
	 *
	 * @since 2.2.0
	 * @see parse_str() You might wish to use that instead.
	 *
	 * @param string|array $value The array or string to loop. First call must be array.
	 * @return array The iterated string to array.
	 */
	public static function umatosa( $value ) {

		$items = [];
		if ( ']' === substr( $value, -1 ) ) {
			$items = preg_split( '/[\[\]]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
			return static::satoma( $items );
		}

		parse_str( $value, $items );

		return $items;
	}

	/**
	 * Returns last value of an array.
	 *
	 * I should get a nobel prize for this.
	 *
	 * @since 2.2.0
	 * @see $this->umatosa() Which created a need for this.
	 *
	 * @param array $a The array to get the last value from.
	 * @return string The last array value.
	 */
	public static function get_last_value( array $a ) {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- Don't fault my amazingness.
		while ( \is_array( $a = end( $a ) ) );

		return $a;
	}

	/**
	 * Converts a single or sequential|associative array into a multidimensional array.
	 *
	 * SAtoMA: "Single Array to Multidimensional Array"
	 *
	 * Example: '[ 0 => a, 1 => b, 3 => c ]';
	 * Becomes: [ a => [ b => [ c ] ];
	 *
	 * @NOTE Do not pass multidimensional arrays, as they will cause PHP errors.
	 *       Their values will be used as keys. Arrays can't be keys.
	 *
	 * @since 2.2.0
	 *
	 * @param array $a The single dimensional array.
	 * @return array Multidimensional array, where the values are the dimensional keys.
	 */
	public static function satoma( array $a ) {

		$r = [];

		if ( $a ) {
			$last = array_shift( $a );

			if ( $a ) {
				$r[ $last ] = static::satoma( $a );
			} else {
				$r = $last;
			}
		}

		return $r;
	}
}
