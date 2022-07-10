<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Core transformer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract
 */
abstract class Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.0.0
	 * @var \The_SEO_Framework\Load TSF class.
	 * @final
	 */
	protected static $tsf;

	/**
	 * @since 1.0.0
	 * @var array[string:callable] The replacement types by name.
	 */
	protected static $replacements = [];

	/**
	 * @since 1.0.0
	 * @var string[] The non-replacement types.
	 */
	protected static $preserve = [];

	/**
	 * @since 1.0.0
	 * @var string The non-replacement types, quoted for regex.
	 */
	protected static $preserve_preg_quoted = '';

	/**
	 * @since 1.0.0
	 * @var string[] The non-replacement types' prefixes.
	 */
	protected static $prefix_preserve = [];

	/**
	 * @since 1.0.0
	 * @var string The non-replacement types' prefixes, quoted for regex.
	 */
	protected static $prefix_preserve_preg_quoted = '';

	/**
	 * @since 1.0.0
	 * @var \WP_Post The current post object to extract values from.
	 * @final
	 */
	protected static $post;

	/**
	 * @since 1.0.0
	 * @var \WP_User The current user object to extract values from.
	 * @final
	 */
	protected static $user;

	/**
	 * @since 1.0.0
	 * @var \WP_Term The current term object to extract values from.
	 * @final
	 */
	protected static $term;

	/**
	 * @since 1.0.0
	 * @var string The current object type. Accepts 'term', 'post', 'user'.
	 * @final
	 */
	protected static $main_object_type;

	/**
	 * @since 1.0.0
	 * @var array[string:mixed] The cache that will reset when new post is registered.
	 * @final
	 */
	protected static $post_cache = [];

	/**
	 * @since 1.0.0
	 * @var array[string:mixed] The cache that will reset when new user is registered.
	 * @final
	 */
	protected static $user_cache = [];

	/**
	 * @since 1.0.0
	 * @var array[string:mixed] The cache that will reset when new term is registered.
	 * @final
	 */
	protected static $term_cache = [];

	/**
	 * @since 1.0.0
	 * @var array[string:mixed] The cache that will never expire.
	 */
	protected static $persistent_cache = [];

	/**
	 * Constructor, sets up vars.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {
		self::$tsf = \tsf();
		static::reset_replacements();

		self::$persistent_cache['separator']   = self::$tsf->get_separator();
		self::$persistent_cache['q_separator'] = preg_quote( self::$persistent_cache['separator'], '/' );

		self::$persistent_cache['date_format'] = \get_option( 'date_format' );
	}

	/**
	 * Sets main object type.
	 *
	 * This helps discern whether the set post/taxonomy is for the current
	 * object transformation, or for a different one.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Must be either 'term', 'post', or 'user'.
	 *                     'user' will probably never be implemented, but this whole class supports it.
	 */
	final public static function set_main_object_type( $type ) {
		static::$main_object_type = $type;
	}

	/**
	 * Sets the active post object.
	 *
	 * @since 1.0.0
	 * @final
	 *
	 * @param int|\WP_Post $post The post ID or object.
	 *                           If `\WP_Post`, it's expected to be filtered via `get_post()` prior.
	 */
	final public static function set_post( $post ) {

		if ( ! $post instanceof \WP_Post )
			$post = \get_post( $post ) ?: null;

		// Terms may have shared a post, let's not reset cache.
		if ( $post !== self::$post ) {
			self::$post and \clean_post_cache( self::$post );

			self::$post       = $post;
			self::$post_cache = [];
		}
	}

	/**
	 * Sets user from current active post.
	 *
	 * @since 1.0.0
	 * @final
	 */
	final public static function set_user_from_current_post() {
		isset( self::$post->post_author ) and self::set_user( self::$post->post_author );
	}

	/**
	 * Sets the active post object.
	 *
	 * @since 1.0.0
	 * @final
	 *
	 * @param int|\WP_User $user The user ID or object.
	 *                           If `\WP_User`, it's expected to be filtered via `get_post()` prior.
	 */
	final public static function set_user( $user ) {

		if ( ! $user instanceof \WP_User )
			$user = \get_user_by( 'id', $user ) ?: null; // Discrepancy. Y no exist get_user()?

		// Posts may have shared a user, let's not reset cache.
		if ( $user !== self::$user ) {
			self::$user and \clean_user_cache( self::$user );

			// Posts may have a user deleted, unset if that's the case.
			// self::$user       = $user?->exists() ? $user : null; // PHP 8.0+
			self::$user       = $user && $user->exists() ? $user : null;
			self::$user_cache = [];
		}
	}

	/**
	 * Sets term from current active post.
	 *
	 * @since 1.0.0
	 * @final
	 */
	final public static function set_term_from_current_post() {

		if ( empty( self::$post->post_type ) ) return;

		$term = \get_the_terms(
			self::$post->ID,
			current( \get_object_taxonomies( self::$post->post_type, 'objects' ) )
		)[0] ?? null;

		$term instanceof \WP_Term and self::set_term( $term );
	}

	/**
	 * Sets the active term object.
	 *
	 * @since 1.0.0
	 * @final
	 *
	 * @param int|\WP_Term $term The term ID or object.
	 *                           If `\WP_Term`, it's expected to be filtered via `get_term()` prior.
	 */
	final public static function set_term( $term ) {

		if ( ! $term instanceof \WP_Term )
			$term = \get_term( $term ) ?: null;

		// Posts may have shared a term, let's not reset the cache.
		if ( $term !== self::$term ) {
			self::$term and \clean_term_cache(
				self::$term->ID,        // Discrepancy, term object not accepted.
				self::$term->taxonomy,  // Provide otherwise a new query takes place.
				false                   // Keep taxonomy cache.
			);

			self::$term       = $term;
			self::$term_cache = [];
		}
	}

	/**
	 * Resets replacement values, if needed.
	 *
	 * @since 1.0.0
	 */
	protected static function reset_replacements() {

		// searchphrase and term404 are trimmed.

		// Late static binding: The methods may be overwritten in a child class without affecting this array.
		// Names are derived from Yoast SEO's transformations -- but all other SEO plugins use these as a baseline.
		// Add to these replacements as you see fit, this array shouldn't be looped over, indexes should be fetched directly.
		self::$replacements = [
			'archive_title'        => [ static::class, 'get_term_title' ], // CPTA aren't transported.
			'author_first_name'    => [ static::class, 'get_post_author_first_name' ],
			'author_last_name'     => [ static::class, 'get_post_author_last_name' ],
			'caption'              => [ static::class, 'get_post_excerpt' ],
			'category'             => [ static::class, 'get_post_all_term_names' ],
			'category_description' => [ static::class, 'get_term_description' ],
			'category_title'       => [ static::class, 'get_term_title' ],
			'currentdate'          => [ static::class, 'get_current_date' ],
			'currentday'           => [ static::class, 'get_current_day' ],
			'currentmonth'         => [ static::class, 'get_current_month' ],
			'currentyear'          => [ static::class, 'get_current_year' ],
			'date'                 => [ static::class, 'get_post_date' ],
			'excerpt'              => [ static::class, 'get_post_excerpt_trimmed' ],
			'excerpt_only'         => [ static::class, 'get_post_excerpt' ],
			'id'                   => [ static::class, 'get_id' ],
			'modified'             => [ static::class, 'get_post_modified_date' ], // date_i18n get_option( 'date_format' )
			'name'                 => [ static::class, 'get_post_author_display_name' ],
			'parent_title'         => [ static::class, 'get_post_parent_post_title' ], // Is this the only parent one?
			'permalink'            => [ static::class, 'get_post_permalink' ],
			'post_content'         => [ static::class, 'get_post_content' ],
			'post_year'            => [ static::class, 'get_post_year' ],
			'post_month'           => [ static::class, 'get_post_month' ],
			'post_day'             => [ static::class, 'get_post_day' ],
			'pt_plural'            => [ static::class, 'get_post_post_type_plural_name' ],
			'pt_single'            => [ static::class, 'get_post_post_type_singular_name' ],
			'sep'                  => [ static::class, 'get_separator' ],
			'sitedesc'             => [ static::class, 'get_blog_description' ],
			'sitename'             => [ static::class, 'get_blog_name' ],
			'tag'                  => [ static::class, 'get_post_all_term_names' ],
			'tag_description'      => [ static::class, 'get_term_description' ],
			'term_description'     => [ static::class, 'get_term_description' ],
			'term_title'           => [ static::class, 'get_term_title' ],
			'title'                => [ static::class, 'get_post_title' ],
			'user_description'     => [ static::class, 'get_post_author_description' ],
			'userid'               => [ static::class, 'get_post_author_id' ],
		];

		// We preserve these, some harmful, to allow warning the user they have not been transformed in TSF.
		self::$preserve = [
			// Too complex. Maybe later. Implied via prefix_preserve
			// 'ct_desc',
			// 'ct_product_cat',
			// 'ct_product_tag',

			// (Should) never (be) used in object context. Trim without warning.
			// 'searchphrase',
			// 'term404',

			// Fancy and fun in some situation, sure, but bad for SEO. Warn user.
			'currenttime',

			// We could extract these... but we can't tell whether this was migrated already or not.
			'focuskw',
			'primary_category',

			// "pagenumber of pagetotal" will cause issues. Warn user.
			'page',
			'pagenumber',
			'pagetotal',

			// Maybe later. Warn user.
			'wc_brand',
			'wc_price',
			'wc_shortdesc',
			'wc_sku',
		];
		// This is also where /(%%single)?/ regex comes in.
		self::$prefix_preserve = [
			// 'ct_pa_',   // Custom Taxonomy Product Attribute
			'ct_',      // Custom Taxonomy field name., this can be %%ct_something%%single%%, which we do not test.
			'cf_',      // Custom field name.
		];

		self::$preserve_preg_quoted        = implode( '|', array_map( '\\preg_quote', self::$preserve ) );
		self::$prefix_preserve_preg_quoted = implode( '|', array_map( '\\preg_quote', self::$prefix_preserve ) );
	}

	/**
	 * Returns blog description.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_blog_description() {
		return self::$persistent_cache['blog_description']
			?? self::$persistent_cache['blog_description'] = trim( \get_bloginfo( 'description', 'display' ) );
	}

	/**
	 * Returns blog name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_blog_name() {
		return self::$persistent_cache['blog_name']
			?? self::$persistent_cache['blog_name'] = trim( \get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Returns current date based on date format and locale.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_current_date() {
		return self::$persistent_cache['current_date']
			?? self::$persistent_cache['current_date'] = \date_i18n( self::$persistent_cache['date_format'] );
	}

	/**
	 * Returns current day, translated.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_current_day() {
		return self::$persistent_cache['current_day']
			?? self::$persistent_cache['current_day'] = \date_i18n( 'j' );
	}

	/**
	 * Returns current month, translated.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_current_month() {
		return self::$persistent_cache['current_month']
			?? self::$persistent_cache['current_month'] = \date_i18n( 'F' );
	}

	/**
	 * Returns current year, translated.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_current_year() {
		return self::$persistent_cache['current_year']
			?? self::$persistent_cache['current_year'] = \date_i18n( 'Y' );
	}

	/**
	 * Returns the current object ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_id() {

		// Why does this discrepancy exist?
		if ( 'term' === self::$main_object_type )
			return (string) ( self::$term->term_id ?? '' );

		return (string) ( self::${self::$main_object_type}->ID ?? '' );
	}

	/**
	 * Returns current post's category list, comma separated.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The complete text with matched syntax. (Unused).
	 * @param string $type The registered type of the transformation.
	 * @return string
	 */
	protected static function get_post_all_term_names( $text, $type ) {

		if ( 'term' === self::$main_object_type ) {
			return self::$term->name ?? '';
		} else {
			return self::$post_cache['all_categories_names'][ $type ]
				?? self::$post_cache['all_categories_names'][ $type ] = self::_get_term_list(
					self::$post->ID,
					'tag' === $type ? 'post_tag' : $type
				);
		}
	}

	/**
	 * Returns current (post) author's description.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_author_description() {

		if ( 'term' === self::$main_object_type ) return '';

		// Even though we could do without the user object, WordPress will fetch it anyway.
		// So, let's prepare it so we can bust WordPress's cache when we fetch another user.
		// Do this unconditionally, let the cacher figure this out. This is an acceptable performance hit.
		self::set_user_from_current_post();

		if ( ! isset( self::$user_cache['description'] ) )
			self::$user_cache['description'] = self::$user ? \get_the_author_meta( 'description', self::$user->ID ) : '';

		return self::$user_cache['description'];
	}

	/**
	 * Returns current current (post's) author display name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_author_display_name() {

		if ( 'term' === self::$main_object_type ) return '';

		// Even though we could do without the user object, WordPress will fetch it anyway.
		// So, let's prepare it so we can bust WordPress's cache when we fetch another user.
		// Do this unconditionally, let the cacher figure this out. This is an acceptable performance hit.
		self::set_user_from_current_post();

		if ( ! isset( self::$user_cache['display_name'] ) )
			self::$user_cache['display_name'] = self::$user ? \get_the_author_meta( 'display_name', self::$user->ID ) : '';

		return self::$user_cache['display_name'];
	}

	/**
	 * Returns current (post) author's first name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_author_first_name() {

		if ( 'term' === self::$main_object_type ) return '';

		// Even though we could do without the user object, WordPress will fetch it anyway.
		// So, let's prepare it so we can bust WordPress's cache when we fetch another user.
		// Do this unconditionally, let the cacher figure this out. This is an acceptable performance hit.
		self::set_user_from_current_post();

		if ( ! isset( self::$user_cache['first_name'] ) )
			self::$user_cache['first_name'] = self::$user ? \get_the_author_meta( 'first_name', self::$user->ID ) : '';

		return self::$user_cache['first_name'];
	}

	/**
	 * Returns current (post) author's ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_author_id() {

		switch ( self::$main_object_type ) {
			case 'term':
				return '';
			case 'post':
				return (string) ( self::$post->post_author ?? '' );
			case 'user':
				return (string) ( self::$user->ID ?? '' );
		}
	}

	/**
	 * Returns current (post) author's last name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_author_last_name() {

		if ( 'term' === self::$main_object_type ) return '';

		// Even though we could do without the user object, WordPress will fetch it anyway.
		// So, let's prepare it so we can bust WordPress's cache when we fetch another user.
		// Do this unconditionally, let the cacher figure this out. This is an acceptable performance hit.
		self::set_user_from_current_post();

		if ( ! isset( self::$user_cache['last_name'] ) )
			self::$user_cache['last_name'] = self::$user ? \get_the_author_meta( 'last_name', self::$user->ID ) : '';

		return self::$user_cache['last_name'];
	}

	/**
	 * Returns current post content, because what could go wrong.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_content() {

		if ( 'post' !== self::$main_object_type ) return '';
		if ( ! empty( self::$post->post_password ) || '' === self::$post->post_content ) return '';

		return self::$post_cache['content']
			?? self::$post_cache['content'] = \wp_strip_all_tags( \strip_shortcodes( self::$post->post_content ) );
	}

	/**
	 * Returns current post's date, translated.
	 * If object type is not post, return original match to warn user of issue.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text  The complete text with matched syntax. (Unused).
	 * @param string $type  The registered type of the transformation.
	 * @param string $match The full syntax match that needs transforming. (Unused).
	 * @return string
	 */
	protected static function get_post_date( $text, $type, $match ) {

		if ( 'post' !== self::$main_object_type ) return $match;

		return self::$post_cache['date']
			?? self::$post_cache['date'] = (
				empty( self::$post->post_date )
					? ''
					: \date_i18n( self::$persistent_cache['date_format'], strtotime( self::$post->post_date ) )
			);
	}

	/**
	 * Returns current post modified day. Good for SEO and all that (not).
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_day() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['day']
			?? self::$post_cache['day'] = \get_the_date( 'd', self::$post->ID );
	}

	/**
	 * Returns current post's excerpt.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_excerpt() {

		if ( 'post' !== self::$main_object_type ) return '';
		if ( ! empty( self::$post->post_password ) ) return '';

		return self::$post_cache['excerpt']
			?? self::$post_cache['excerpt'] = self::$tsf->s_excerpt_raw(
				self::$tsf->fetch_excerpt( self::$post )
			);
	}

	/**
	 * Returns current post's excerpt, trimmed to "goodUpper" input guidelines of site locale.
	 *
	 * @since 1.0.0
	 * @uses static::get_post_excerpt(), overrideable.
	 *
	 * @return string
	 */
	protected static function get_post_excerpt_trimmed() {

		if ( 'post' !== self::$main_object_type ) return '';
		if ( ! empty( self::$post->post_password ) ) return '';

		return self::$post_cache['excerpt_short']
			?? self::$post_cache['excerpt_short'] = self::$tsf->trim_excerpt(
				static::get_post_excerpt(),
				self::$tsf->get_input_guidelines()['description']['search']['chars']['goodUpper']
			);
	}

	/**
	 * Returns current post's modified date, translated.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_modified_date() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['modified_date']
			?? self::$post_cache['modified_date'] = (
				empty( self::$post->post_modified )
					? ''
					: \date_i18n( self::$persistent_cache['date_format'], strtotime( self::$post->post_modified ) )
			);
	}

	/**
	 * Returns current post modified month. Good for SEO and all that (not).
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_month() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['month']
			?? self::$post_cache['month'] = \get_the_date( 'F', self::$post->ID );
	}

	/**
	 * Returns current post's parent title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_parent_post_title() {

		if ( 'post' !== self::$main_object_type ) return '';

		if ( isset( self::$post_cache['parent_post_title'] ) )
			return self::$post_cache['parent_post_title'];

		$parent_id = \wp_get_post_parent_id( self::$post->ID );

		if ( $parent_id ) {
			$title = \get_post( $parent_id )->post_title ?? '';
			\clean_post_cache( $parent_id );
		}

		return self::$post_cache['parent_post_title'] = $title ?? '';
	}

	/**
	 * Returns current post permalink.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_permalink() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['permalink']
			?? self::$post_cache['permalink'] = \get_permalink( 'post' );
	}

	/**
	 * Returns current post's post type plural name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_post_type_plural_name() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['post_type_plural_name']
			?? self::$post_cache['post_type_plural_name'] = self::$tsf->get_post_type_label(
				self::$post->post_type,
				false
			);
	}

	/**
	 * Returns current post's post type singular name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_post_type_singular_name() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['post_type_singular_name']
			?? self::$post_cache['post_type_singular_name'] = self::$tsf->get_post_type_label(
				self::$post->post_type,
				true
			);
	}

	/**
	 * Returns current post title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_title() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post->post_title ?? '';
	}

	/**
	 * Returns current post modified year. Good for SEO and all that (not).
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_post_year() {

		if ( 'post' !== self::$main_object_type ) return '';

		return self::$post_cache['year']
			?? self::$post_cache['year'] = \get_the_date( 'Y', self::$post->ID );
	}

	/**
	 * Returns separator.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_separator() {
		return self::$persistent_cache['separator'];
	}

	/**
	 * Returns current post's description.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_term_description() {

		if ( 'user' === self::$main_object_type ) return '';

		if ( 'post' === self::$main_object_type )
			self::set_term_from_current_post();

		// This might have HTML and shortcodes while it shouldn't.
		return self::$term_cache['description']
			?? self::$term_cache['description'] = (
				self::$term ? \wp_strip_all_tags( self::$term->description ?? '' ) : ''
			);
	}

	/**
	 * Returns current term title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function get_term_title() {

		if ( 'user' === self::$main_object_type ) return '';

		if ( 'post' === self::$main_object_type )
			self::set_term_from_current_post();

		return self::$term_cache['title']
			?? self::$term_cache['title'] = (
				self::$term ? self::$tsf->get_generated_single_term_title( self::$term ) : ''
			);
	}

	/**
	 * Returns a list of term values.
	 *
	 * @since 1.0.0
	 * Helper method.
	 *
	 * @param int    $post_id  The post ID to fetch terms for.
	 * @param string $taxonomy The taxonomy to fetch terms for.
	 * @param string $field    The field name to extract.
	 * @param string $sep      The term list separator.
	 * @return string
	 */
	protected static function _get_term_list( $post_id, $taxonomy, $field = 'name', $sep = ', ' ) {

		$terms = \get_the_terms( $post_id, 'category' );

		return \is_array( $terms ) ? implode(
			$sep,
			array_column(
				(array) \get_the_terms( self::$post->ID, 'category' ),
				$field
			)
		) : '';
	}

	/**
	 * Replaces repeated separators with a single separator.
	 *
	 * @since 1.0.0
	 * Helper method.
	 *
	 * @param string $text The value to remove duplicated separators.
	 * @return string
	 */
	protected static function _remove_duplicated_separators( $text ) {

		$q_sep = self::$persistent_cache['q_separator'];

		return preg_replace(
			"/{$q_sep}(?:\s*{$q_sep})+/u",
			self::$persistent_cache['separator'],
			$text
		);
	}
}
