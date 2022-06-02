<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Transformers
 */

namespace TSF_Extension_Manager\Extension\Transport\Transformers\PostMeta;

\defined( 'TSFEM_E_TRANSPORT_VERSION' ) or die;

/**
 * Transformer base class.
 *
 * @since 1.0.0
 * @access private
 *
 * Inherits \TSF_Extension_Manager\Construct_Master_Once_Final_Interface. Requires construct().
 * Inherits abstract
 */
abstract class Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.0.0
	 * @var string The syntax key, e.g. '%%' for Yoost SEO ('%%date%%'), '#' for AIOSEO ('#post_date').
	 *             Leave empty to validate every string for syntaxes.
	 */
	protected $syntax_key;

	/**
	 * @since 1.0.0
	 * @var array[] The syntaxes to find and their transformer callback,
	 *              e.g. [ 'post' => [ 'title' => [ '%%date%%' => [ $this => 'post_date' ] ] ] ]
	 */
	protected $syntax_transformers;

	/**
	 * Tests if syntax key is found in string.
	 *
	 * @since 1.0.0
	 * @uses $this->syntax_key
	 *
	 * @param string $string The string to test.
	 * @return bool
	 */
	abstract protected function has_syntax_key( $string );

	/**
	 * Transforms string according to syntax.
	 *
	 * @since 1.0.0
	 * @uses $this->has_syntax_key()
	 * @uses $this->syntax_transformers
	 *
	 * @param string $string The string to transform.
	 * @return string The transformed string.
	 */
	protected function transform( $string, $type ) {

		// if ( $this->has_syntax_key( $string, $type ) ) {
			foreach ( $this->syntax_transformers[ $type ] as $syntax => $transformer ) {
				$string = \call_user_func( $transformer, $string, $syntax );
				if ( ! $this->has_syntax_key( $string ) ) break;
			}
		// }

		return $string;
	}
}
