<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transport extension for The SEO Framework
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Transformer for SEOPress.
 *
 * @since 1.1.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Core_Static_Final_Instance.
 */
class WP_SEOPress extends Core {
	use \TSF_Extension_Manager\Construct_Core_Static_Unique_Instance_Master;

	/**
	 * @since 1.1.0
	 * @override Prevent writing to self.
	 * @see parent::reset_replacements()
	 * @var array[string:callable] The replacement types by name.
	 */
	protected static $replacements = [];

	/**
	 * @since 1.1.0
	 * @override Prevent writing to self.
	 * @see parent::reset_replacements()
	 * @var string[] The non-replacement types' prefixes.
	 */
	protected static $prefix_preserve = [];

	/**
	 * @since 1.1.0
	 * @override Prevent writing to self.
	 * @see parent::reset_replacements()
	 * @var string The non-replacement types' prefixes, quoted for regex.
	 */
	protected static $prefix_preserve_preg_quoted = '';

	/**
	 * Resets replacement values, if needed.
	 *
	 * NOTE: When overriding, you're likely also overriding self::properties:
	 * register those self::properties statically to the child class to exploit
	 * late-static binding for properties.
	 *
	 * See for reference (in SEOPress) wp-seopress\inc\functions\variables\dynamic-variables.php
	 * Or in SEOPress function `seopress_get_dyn_variables()`.
	 * Or in SEOPress any other 69 places they put things without proper documentation.
	 *
	 * @since 1.1.0
	 * @override
	 */
	protected static function reset_replacements() {
		parent::reset_replacements();

		static::$replacements = [
			'_category_description' => [ static::class, 'get_term_description' ],
			'_category_title'       => [ static::class, 'get_term_title' ],
			'archive_title'         => [ static::class, 'get_term_title' ], // I don't think Benjamin knows how WP works.
			'author_bio'            => [ static::class, 'get_post_author_description' ],
			'author_first_name'     => [ static::class, 'get_post_author_first_name' ],
			'author_last_name'      => [ static::class, 'get_post_author_last_name' ],
			'author_nickname'       => [ static::class, 'get_post_author_display_name' ],
			'currentday'            => [ static::class, 'get_current_day' ],   // should we?
			'currentmonth'          => [ static::class, 'get_current_month' ], // should we?
			'currentmonth_num'      => [ static::class, 'get_current_month_number' ],
			'currentmonth_short'    => [ static::class, 'get_current_month_short' ], // should we?
			'currentyear'           => [ static::class, 'get_current_year' ],  // should we?
			'date'                  => [ static::class, 'get_post_date' ],
			'excerpt'               => [ static::class, 'get_post_excerpt_trimmed' ],
			'post_author'           => [ static::class, 'get_post_author_display_name' ],
			'post_category'         => [ static::class, 'get_post_first_category_name' ],
			'post_content'          => [ static::class, 'get_post_content' ],
			'post_date'             => [ static::class, 'get_post_date' ],
			'post_excerpt'          => [ static::class, 'get_post_excerpt_trimmed' ],
			'post_modified_date'    => [ static::class, 'get_post_modified_date' ],
			'post_tag'              => [ static::class, 'get_post_first_tag_name' ],
			'post_title'            => [ static::class, 'get_post_title' ],
			'sep'                   => [ static::class, 'get_separator' ],
			'sitedesc'              => [ static::class, 'get_blog_description' ],
			'sitename'              => [ static::class, 'get_blog_name' ],
			'sitetitle'             => [ static::class, 'get_blog_name' ],
			'tag_description'       => [ static::class, 'get_term_description' ], // Dumb implementation by SEOPress
			'tag_title'             => [ static::class, 'get_term_title' ], // Dumb implementation by SEOPress
			'tagline'               => [ static::class, 'get_blog_description' ],
			'term_description'      => [ static::class, 'get_term_description' ],
			'term_title'            => [ static::class, 'get_term_title' ],
			'title'                 => [ static::class, 'get_post_title' ],
			'wc_single_cat'         => [ static::class, 'get_post_all_product_cat_names' ],
			'wc_single_short_desc'  => [ static::class, 'get_post_excerpt_trimmed' ],
			'wc_single_tag'         => [ static::class, 'get_post_all_product_tag_names' ],
		];

		static::$preserve = [
			// Outputs URLs. This should not be used for titles and descriptions.
			'post_thumbnail_url',

			// Outputs URLs. This should not be used for titles and descriptions.
			'post_url',

			// (Should) never (be) used in object context. Trim without warning.
			// 'search_keywords',

			// "pagenumber of pagetotal" will cause issues. Warn user.
			'current_pagination',
			'page',

			// (Should) never (be) used in object context. Trim without warning.
			// 'cpt_plural',
			// 'archive_date',
			// 'archive_date_day',
			// 'archive_date_month',
			// 'archive_date_month_name',
			// 'archive_date_year',

			// WooCommerce: Maybe later. Warn user.
			'wc_single_price',
			'wc_single_price_exc_tax',
			'wc_sku',

			// Fancy and fun in some situation, sure, but bad for SEO. Warn user.
			'currenttime',

			// Outputs URLs. This should not be used for titles and descriptions.
			'author_website',

			// We could extract this... but we can't tell whether they were migrated already or not.
			'target_keyword',
		];

		// Override.
		static::$prefix_preserve = [
			// Too complex. Undocumented; doubt anyone uses it. Maybe later, probably never.
			'_cf_',
			'_ct_', // These don't even transform in SEOPress. What a joke.
			'_ucf_',
		];

		// Override.
		static::$prefix_preserve_preg_quoted = implode( '|', array_map( 'preg_quote', static::$prefix_preserve ) );
	}

	/**
	 * Converts SEOPress title syntax to human readable text.
	 *
	 * @since 1.1.0
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
	 * Converts SEOPress description syntax to human readable text.
	 *
	 * @since 1.1.0
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
	 * Converts SEOPress title/description syntax to human readable text.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed  $text        The old description value possibly unsafe for TSF.
	 * @param int    $object_id   The post, user, or term ID to transform.
	 * @param string $object_type The current object type.
	 * @return string The transformed title or description.
	 */
	private static function _transform_syntax( $text, $object_id, $object_type ) {

		// %id% is the shortest valid tag... ish. Let's stop at 6.
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
				   ! \in_array( $type, static::$preserve, true )
				&& ! preg_match(
					\sprintf( '/^(%s)/', static::$prefix_preserve_preg_quoted ),
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
	 * Converts SEOPress robots-settings to TSF's qubit.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value The old robots value possibly unsafe for TSF.
	 * @return int|null The sanitized qubit.
	 */
	public static function _robots_qubit( $value ) {

		if ( 'yes' === $value ) { // Aptly named... not.
			$value = 1; // Force no_robots
		} else {
			$value = null; // Default/unassigned
		}

		return $value;
	}
}
