<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Importer for Yoost SEO.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits abstract setup_vars.
 */
final class WordPress_SEO extends Core {

	/**
	 * Sets up variables.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	protected function setup_vars() {
		global $wpdb;

		$this->syntax_key = '%%';

		// 'supports'  => [
		// 	'title',
		// 	'description',
		// 	'canonical_url',
		// 	'noindex',
		// 	'nofollow',
		// 	'noarchive',
		// 	'og_title',
		// 	'og_description',
		// 	'twitter_title',
		// 	'twitter_description',
		// 	'og_image',
		// 	'article_type',
		// ],

		// 'transform' => [ /* "Transformed fields cannot be recovered without a backup" */
		// 	'title',
		// 	'description',
		// 	'noindex',
		// 	'nofollow',
		// 	'noarchive',
		// 	'og_title',
		// 	'og_description',
		// 	'twitter_title',
		// 	'twitter_description',
		// ],

		/**
		 * $from_index,
		 * $to_index,
		 * $transformer,
		 * $from_database,
		 * $to_database,
		 */
		$this->conversion_sets = [
			[
				'_yoast_wpseo_title',
				'_genesis_title',
				null,
				// $wpdb->postmeta,
				// $wpdb->postmeta,
			],
			[
				'_yoast_wpseo_metadesc',
				'_genesis_description',
				null,
				// $wpdb->postmeta,
				// $wpdb->postmeta,
			],
			[
				'_yoast_wpseo_meta-robots-noindex',
				'_genesis_noindex',
				null,
				// $wpdb->postmeta,
				// $wpdb->postmeta,
			],
			[
				'_yoast_wpseo_meta-robots-nofollow',
				'_genesis_nofollow',
				null,
				// $wpdb->postmeta,
				// $wpdb->postmeta,
			],
			// [ // Doesn't exist?
				// '_yoast_wpseo_meta-robots-noarchive',
				// '_genesis_noarchive',
				// null,
				// // $wpdb->postmeta,
				// // $wpdb->postmeta,
			// ],
			[
				'_yoast_wpseo_canonical',
				'_genesis_canonical_uri',
				null,
				// $wpdb->postmeta,
				// $wpdb->postmeta,
			],
			[
				'_yoast_wpseo_redirect',
				'redirect',
				null,
				// $wpdb->postmeta,
				// $wpdb->postmeta,
			],
		];
	}
}
