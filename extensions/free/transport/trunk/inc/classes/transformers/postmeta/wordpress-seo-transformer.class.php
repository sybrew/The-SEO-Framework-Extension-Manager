<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transformer for Yoost SEO.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Core_Static_Stray_Private_Instance. Requires construct().
 * Inherits abstract setup_vars(), et al.
 */
class WordPress_SEO_Transformer extends Core {
	use \TSF_Extension_Manager\Construct_Core_Static_Stray_Private_Instance;

	/**
	 * Sets up variables.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	protected function setup_vars() {
		$this->syntax_key = '%%';
	}

	/**
	 * Tests if syntax key is found in string.
	 *
	 * @since 1.0.0
	 * @uses $this->syntax_key
	 *
	 * @param string $string The string to test.
	 * @return bool
	 */
	protected function has_syntax_key( $string ) {
		return false !== strpos( $string, $this->syntax_key );
	}
}
