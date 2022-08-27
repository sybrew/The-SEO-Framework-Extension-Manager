<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Importers
 */

namespace TSF_Extension_Manager\Extension\Transport\Importers\TermMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Core importer class.
 *
 * @since 1.0.0
 * @access private
 * @abstract
 */
class Core extends \TSF_Extension_Manager\Extension\Transport\Importers\Base {
	use \TSF_Extension_Manager\Construct_Sub_Once_Interface;

	/**
	 * @since 1.0.0
	 * @var string $current_taxonomy The current taxonomy, if any.
	 */
	protected $current_taxonomy = '';

	/**
	 * Sets up class, mainly required variables.
	 *
	 * @since 1.0.0
	 */
	protected function construct() {
		$this->type                   = 'term';
		$this->id_key                 = 'term_id';
		$this->globals_table_fallback = $GLOBALS['wpdb']->termmeta;
		$this->cache_clear_cb         = [ $this, 'clean_term_cache' ];
	}

	/**
	 * Cleans term cache.
	 *
	 * @since 1.0.0
	 *
	 * @param int $term_id The term ID to clear cache for.
	 * @return ?null Might one day return something.
	 */
	protected function clean_term_cache( $term_id ) {
		return \clean_term_cache( $term_id, $this->current_taxonomy, false );
	}

	/**
	 * Clears taxonomy cache.
	 *
	 * @since 1.0.0
	 * @return ?null Might one day return something.
	 */
	protected function clean_taxonomy_cache() {
		return $this->current_taxonomy ? \clean_taxonomy_cache( $this->current_taxonomy ) : null;
	}
}
