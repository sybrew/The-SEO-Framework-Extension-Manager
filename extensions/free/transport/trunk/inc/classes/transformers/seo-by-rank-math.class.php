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
	 * @since 1.0.0
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
	 * See for reference (in Rank Math) RankMath\Admin\Importers\Yoast::convert_variables()
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Now no longer relies on Rank Math's erroneous ...Importers\Yoast::convert_variables(),
	 *              but we read the code, and implemented all instances of register_var_replacement() calls.
	 * @override
	 */
	protected static function reset_replacements() {
		parent::reset_replacements();

		static::$replacements = [
			'category'         => [ static::class, 'get_post_first_category_name' ],
			'categories'       => [ static::class, 'get_post_all_category_names' ],
			'currentdate'      => [ static::class, 'get_current_date' ],  // should we?
			'currentday'       => [ static::class, 'get_current_day' ],   // should we?
			'currentmonth'     => [ static::class, 'get_current_month' ], // should we?
			'currentyear'      => [ static::class, 'get_current_year' ],  // should we?
			'date'             => [ static::class, 'get_post_date' ],     // Doesn't (shouldn't) work on Terms.
			'excerpt'          => [ static::class, 'get_post_excerpt_trimmed' ],
			'excerpt_only'     => [ static::class, 'get_post_excerpt' ],
			'id'               => [ static::class, 'get_id' ],
			'modified'         => [ static::class, 'get_post_modified_date' ], // date_i18n get_option( 'date_format' )
			'name'             => [ static::class, 'get_post_author_display_name' ],
			'parent_title'     => [ static::class, 'get_post_parent_post_title' ], // Is this the only parent one?
			'post_author'      => [ static::class, 'get_post_author_display_name' ],
			'pt_plural'        => [ static::class, 'get_post_post_type_plural_name' ],
			'pt_single'        => [ static::class, 'get_post_post_type_singular_name' ],
			'seo_title'        => [ static::class, 'get_post_title' ],           // hidden, likely confusing
			'seo_description'  => [ static::class, 'get_post_excerpt_trimmed' ], // hidden, likely confusing
			'sep'              => [ static::class, 'get_separator' ],
			'sitedesc'         => [ static::class, 'get_blog_description' ],
			'sitename'         => [ static::class, 'get_blog_name' ],
			'tag'              => [ static::class, 'get_post_first_tag_name' ],
			'tags'             => [ static::class, 'get_post_all_tag_names' ],
			'term'             => [ static::class, 'get_term_title' ],
			'term_description' => [ static::class, 'get_term_description' ],
			'title'            => [ static::class, 'get_post_title' ],
			'user_description' => [ static::class, 'get_post_author_description' ],
			'userid'           => [ static::class, 'get_post_author_id' ],
		];
		static::$preserve     = [
			// Fancy and fun in some situation, sure, but bad for SEO. Warn user.
			'currenttime',

			// This one is actually neat, but who does SEO for attachments? Preserve.
			'filename',

			// We could extract this... but we can't tell whether they were migrated already or not.
			'focuskw',

			// BuddyPress: Maybe later. Warn user.
			'group_desc',
			'group_name',

			// We could extract this... but we can't tell whether they were migrated already or not.
			'keywords',

			// Too complex. Maybe later.
			'org_name',
			'org_logo',
			'org_url',

			// "pagenumber of pagetotal" will cause issues. Warn user.
			'page',
			'pagenumber',
			'pagetotal',

			// (Should) never (be) used in object context. Trim without warning.
			// 'search_query',

			// Outputs URLs. This should not be used for titles and descriptions.
			'post_thumbnail',

			// We could extract this... but we can't tell whether they were migrated already or not.
			'primary_category',
			'primary_taxonomy_terms',

			// Outputs URLs. This should not be used for titles and descriptions.
			'url',

			// WooCommerce: Maybe later. Warn user.
			'wc_brand',
			'wc_price',
			'wc_shortdesc',
			'wc_sku',
		];

		// Override.
		static::$prefix_preserve = [
			// Too complex. Maybe later.
			'categories', // Rank Math has two categories, this one is advanced.
			'count',
			'currenttime', // Rank Math has two currenttime, this one is advanced.
			'customfield',
			'customterm',
			'customterm_desc',
			'date', // Rank Math has two date, this one is advanced.
			'modified', // Rank Math has two modified, this one is advanced.
			'tags', // Rank Math has two tags, this one is advanced.
		];

		static::$prefix_preserve_preg_quoted = implode( '|', array_map( '\\preg_quote', static::$prefix_preserve ) );
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
		return static::_transform_syntax( $value, $object_id, $object_type );
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
		return static::_transform_syntax( $value, $object_id, $object_type );
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
