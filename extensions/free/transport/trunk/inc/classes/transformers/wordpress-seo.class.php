<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * copyright (C) 2022 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Transformer for Yoast SEO.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Core_Static_Final_Instance.
 */
class WordPress_SEO extends Core {
	use \TSF_Extension_Manager\Construct_Core_Static_Unique_Instance_Master;

	/**
	 * Resets replacement values, if needed.
	 *
	 * NOTE: When overriding, you're likely also overriding self::$properties:
	 * register those self::$properties statically to the child class to exploit
	 * late-static binding for properties.
	 *
	 * @since 1.0.0
	 */
	protected static function reset_replacements() {
		parent::reset_replacements();

		static::$replacements = [
			'archive_title'        => [ static::class, 'get_term_title' ], // Note: CPTA aren't transported--this replacement doesn't consider.
			'author_first_name'    => [ static::class, 'get_post_author_first_name' ], // Doesn't (shouldn't) work on Terms.
			'author_last_name'     => [ static::class, 'get_post_author_last_name' ],  // Doesn't (shouldn't) work on Terms.
			'caption'              => [ static::class, 'get_post_excerpt' ],
			'category'             => [ static::class, 'get_post_all_term_names' ],
			'category_description' => [ static::class, 'get_term_description' ],
			'category_title'       => [ static::class, 'get_term_title' ],
			'currentdate'          => [ static::class, 'get_current_date' ],  // should we?
			'currentday'           => [ static::class, 'get_current_day' ],   // should we?
			'currentmonth'         => [ static::class, 'get_current_month' ], // should we?
			'currentyear'          => [ static::class, 'get_current_year' ],  // should we?
			'date'                 => [ static::class, 'get_post_date' ],     // Doesn't (shouldn't) work on Terms.
			'excerpt'              => [ static::class, 'get_post_excerpt_trimmed' ],
			'excerpt_only'         => [ static::class, 'get_post_excerpt' ],
			'id'                   => [ static::class, 'get_id' ],
			'modified'             => [ static::class, 'get_post_modified_date' ], // date_i18n get_option( 'date_format' )
			'name'                 => [ static::class, 'get_post_author_display_name' ],
			'parent_title'         => [ static::class, 'get_post_parent_post_title' ], // Is this the only parent one?
			'post_content'         => [ static::class, 'get_post_content' ],
			'post_year'            => [ static::class, 'get_post_year' ],
			'post_month'           => [ static::class, 'get_post_month' ],
			'post_day'             => [ static::class, 'get_post_day' ],
			'pt_plural'            => [ static::class, 'get_post_post_type_plural_name' ],
			'pt_single'            => [ static::class, 'get_post_post_type_singular_name' ],
			'sep'                  => [ static::class, 'get_separator' ],
			'sitedesc'             => [ static::class, 'get_blog_description' ],
			'sitename'             => [ static::class, 'get_blog_name' ],
			'tag'                  => [ static::class, 'get_post_all_tag_names' ],
			'tag_description'      => [ static::class, 'get_term_description' ],
			'term_description'     => [ static::class, 'get_term_description' ],
			'term_title'           => [ static::class, 'get_term_title' ],
			'title'                => [ static::class, 'get_post_title' ],
			'user_description'     => [ static::class, 'get_post_author_description' ],
			'userid'               => [ static::class, 'get_post_author_id' ],
		];

		// We preserve these, some harmful, to allow warning the user they have not been transformed in TSF.
		static::$preserve = [
			// Too complex. Maybe later. Implied via prefix_preserve
			// 'ct_desc',
			// 'ct_product_cat',
			// 'ct_product_tag',

			// (Should) never (be) used in object context. Trim without warning.
			// 'searchphrase',
			// 'term404',

			// Fancy and fun in some situation, sure, but bad for SEO. Warn user.
			'currenttime',

			// We could extract these... but we can't tell whether they were migrated already or not.
			'focuskw',
			'primary_category',

			// "pagenumber of pagetotal" will cause issues. Warn user.
			'page',
			'pagenumber',
			'pagetotal',

			// Outputs URLs. This should not be used for titles and descriptions.
			'permalink',

			// WooCommerce: Maybe later. Warn user.
			'wc_brand',
			'wc_price',
			'wc_shortdesc',
			'wc_sku',
		];
		// This is also where /(%%single)?/ regex comes in.
		static::$prefix_preserve = [
			// Custom Taxonomy Product Attribute, implied via ct_*:
			// 'ct_pa_',
			'ct_',      // Custom Taxonomy field name., this can be %%ct_something%%single%%, which we do not test.
			'cf_',      // Custom field name.
		];

		static::$prefix_preserve_preg_quoted = implode( '|', array_map( 'preg_quote', static::$prefix_preserve ) );
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
		return static::_transform_syntax( $value, $object_id, $object_type );
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
		return static::_transform_syntax( $value, $object_id, $object_type );
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

		// TODO Consider using `/%%([^%]+(%%single)?)%%/`, for we might land stray `single%%`
		// There is zero documentation on the use of %%single, though. It's probably a bug or
		// leftover code in/following Yoast's regex. Here, enjoy 30 more bugs they refuse to fix:
		// <https://twitter.com/SybreWaaijer/status/1545621157998649346>
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
				   ! \in_array( $type, static::$preserve, true )
				&& ! preg_match(
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
		}

		return $value;
	}

	/**
	 * Converts Yoast SEO term robots-settings to TSF's qubit.
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
