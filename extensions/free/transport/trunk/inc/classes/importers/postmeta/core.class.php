<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Core importer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract
 */
abstract class Core extends \TSF_Extension_Manager\Extension\Transport\Importers\Base {
	use \TSF_Extension_Manager\Construct_Sub_Once_Interface;

	/**
	 * Sets up class, mainly required variables.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {
		$this->type                   = 'post';
		$this->id_key                 = 'post_id';
		$this->globals_table_fallback = $GLOBALS['wpdb']->postmeta;
		$this->cache_clear_cb         = 'clean_post_cache';
	}

	/**
	 * Returns a list of taxonomies that could have primary term support.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] List of taxonomy names.
	 */
	final protected function get_taxonomy_list_with_pt_support() {

		$taxonomies = array_filter(
			\get_taxonomies( [], 'objects' ),
			static function( $t ) {
				return ! empty( $t->hierarchical );
			}
		);

		return array_keys( $taxonomies );
	}
}
