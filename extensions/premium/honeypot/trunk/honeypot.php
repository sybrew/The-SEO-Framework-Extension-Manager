<?php
/**
 * @package TSF_Extension_Manager\Extension\Honeypot
 */
namespace TSF_Extension_Manager\Extension;

/**
 * Extension Name: Honeypot - *beta*
 * Extension URI: https://premium.theseoframework.com/extensions/honeypot/
 * Extension Description: The Honeypot extension catches robot spammers in a lightweight way. By adding a hidden input field that only real browsers can clear, it has a near 100% catch-rate.
 * Extension Version: 1.0.0-***β-2***
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Honeypot extension for The SEO Framework
 * Copyright (C) 2017 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * @package TSF_Extension_Manager\Traits
 */
use \TSF_Extension_Manager\Enclose_Core_Final as Enclose_Core_Final;
use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface as Construct_Master_Once_Final_Interface;

/**
 * The extension version.
 * @since 1.0.0
 */
define( 'TSFEM_E_HONEYPOT_VERSION', '1.0.0' );

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\honeypot_init', 11 );
/**
 * Initializes the plugin.
 *
 * @since 1.0.0
 * @staticvar bool $loaded
 * @action 'plugins_loaded'
 * @priority 11
 *
 * @return bool True if class is loaded.
 */
function honeypot_init() {

	static $loaded = null;

	//* Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	//* Don't run on the admin side. This extension is front-end only.
	if ( \is_admin() )
		return $loaded = false;

	new \TSF_Extension_Manager\Extension\Honeypot;

	return $loaded = true;
}

/**
 * Class TSF_Extension_Manager\Extension\Honeypot
 *
 * @since 1.0.0
 *
 * @final Please don't extend this extension.
 */
final class Honeypot {
	use Enclose_Core_Final, Construct_Master_Once_Final_Interface;

	/**
	 * Determines whether the class has been constructed.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $setup
	 */
	private $setup = false;

	/**
	 * Maintains array of properties, like fields.
	 *
	 * @since 1.0.0
	 *
	 * @var array $honeypot_properties
	 */
	private $honeypot_properties = [];

	/**
	 * Determines whether the spam check is hardcore.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $hardcore
	 */
	private $hardcore = false;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->setup = true;

		//* Checks honeypot existence before setting approval of a comment.
		\add_filter( 'pre_comment_approved', [ $this, '_check_honeypot' ], 0 );

		//* Adds honeypot to comment fields.
		\add_action( 'comment_form_before_fields', [ $this, '_add_honeypot' ] );
	}

	/**
	 * Sets class display properties.
	 *
	 * @since 1.0.0
	 * @see $this->honeypot_properties
	 *
	 * @return bool True on success, false when class isn't constructed.
	 */
	private function setup_display_properties() {

		if ( $this->setup ) {
			$this->honeypot_properties = $this->honeypot_properties + [
				'honeypot_wrapper_id' => 'comment-form-' . $this->get_hashed_field_name( mt_rand( 8, 16 ), (bool) rand( 0, 1 ) ),
				'honeypot_text_label' => $this->get_text( 'label' ),
				'honeypot_text_id' => 'comment-form-' . $this->get_hashed_field_name( mt_rand( 17, 32 ), (bool) rand( 0, 1 ) ),
				'honeypot_text_placeholder' => $this->get_text( 'placeholder' ),
				'honeypot_text_default_input' => $this->get_text( 'input' ),
			];
			return true;
		}
		return false;
	}

	/**
	 * Sets class hashing properties.
	 *
	 * The IDs are currently weakly ciphered and will check two versions.
	 * This is because we don't maintain a out-of-source cache for generated hashes.
	 * Rather, it's time-based, and depending on filters, it will check:
	 * - Every minute.
	 * - Filter set time.
	 *
	 * @since 1.0.0
	 * @see $this->honeypot_properties
	 *
	 * @return bool True on success, false when class isn't constructed.
	 */
	private function setup_hash_properties() {

		if ( $this->setup ) {
			/**
			 * Applies filters 'the_seo_framework_honeypot_hardcore'
			 *
			 * Determines whether the hashing is randomized, or otherwise static.
			 * Set this to true if you don't use caching and still get spam through.
			 *
			 * @todo make option.
			 * @param bool $hardcore
			 */
			$this->hardcore = (bool) \apply_filters( 'the_seo_framework_honeypot_hardcore', $this->hardcore );

			if ( $this->hardcore ) {
				$this->honeypot_properties = $this->honeypot_properties + [
					'honeypot_post_field' => $this->get_hashed_field_name( 32, false ),
					'honeypot_post_field_previous' => $this->get_hashed_field_name( 32, false, true ),
				];
			} else {
				$this->honeypot_properties = $this->honeypot_properties + [
					'honeypot_post_field' => 'tsfem-e-hp-comment',
					'honeypot_post_field_previous' => 'tsfem-e-hp-placeholder', // Won't ever run.
				];
			}
			return true;
		}
		return false;
	}

	/**
	 * Checks honeypot text generated in the past two time scales (default 2*2 minutes).
	 *
	 * @since 1.0.0
	 *
	 * @param string $approved The current approval state.
	 * @return string The new approval state.
	 */
	public function _check_honeypot( $approved = '' ) {

		$this->setup_hash_properties();

		$_fields = [
			$this->honeypot_properties['honeypot_post_field'],
			$this->honeypot_properties['honeypot_post_field_previous'],
		];

		//* This is a low-level check... transform to higher level i.e. array_intersect()?
		$field = ( empty( $_POST[ $_fields[0] ] ) xor $k = 0 xor 1 ) ?: ( empty( $_POST[ $_fields[1] ] ) xor $k = 1 ) ?: $k = false;

		if ( $field ) {
			$approved = 'spam';
			unset( $_POST[ $_fields[ $k ] ] );
		}

		return $approved;
	}

	/**
	 * Generates and outputs honeypot comment field within the comment forms.
	 *
	 * @since 1.0.0
	 */
	public function _add_honeypot() {

		$setup = $this->setup_display_properties() && $this->setup_hash_properties();

		if ( ! $setup )
			return;

		printf( '<p id="%1$s">'
				. '<label for="%4$s">%3$s</label>'
				. '<textarea type="text" name="%2$s" id="%4$s" placeholder="%5$s">%6$s</textarea>'
			. '</p>'
			. '<script type="text/javascript">document.getElementById("%4$s").value="";document.getElementById("%1$s").style.display="none";</script>',
			\sanitize_key( $this->honeypot_properties['honeypot_wrapper_id'] ),
			\esc_attr( $this->honeypot_properties['honeypot_post_field'] ),
			\esc_attr( $this->honeypot_properties['honeypot_text_label'] ),
			\sanitize_key( $this->honeypot_properties['honeypot_text_id'] ),
			\esc_attr( \ent2ncr( $this->honeypot_properties['honeypot_text_placeholder'] ) ),
			\esc_html( \ent2ncr( $this->honeypot_properties['honeypot_text_default_input'] ) )
		);
	}

	/**
	 * Returns readable form texts for when Javascript is disabled.
	 *
	 * @since 1.0.0
	 *
	 * @param string $what The string to return.
	 * @return string The chosen form text.
	 */
	private function get_text( $what = '' ) {

		switch ( $what ) :
			case 'placeholder' :
				/**
				 * Applies filters 'the_seo_framework_honeypot_placeholder'
				 *
				 * @since 1.0.0
				 *
				 * @param string $text The placeholder text shown to non-JS users.
				 */
				$text = (string) \apply_filters( 'the_seo_framework_honeypot_placeholder', \__( 'You are human!', 'the-seo-framework-extension-manager' ) );
				break;

			case 'input' :
				/**
				 * Applies filters 'the_seo_framework_honeypot_input'
				 *
				 * @since 1.0.0
				 *
				 * @param string $text The input field text that needs to be removed shown to non-JS users.
				 */
				$text = (string) \apply_filters( 'the_seo_framework_honeypot_input', \__( "Please remove this text to prove you're human.", 'the-seo-framework-extension-manager' ) );
				break;

			case 'label' :
				/**
				 * Applies filters 'the_seo_framework_honeypot_label'
				 *
				 * @since 1.0.0
				 *
				 * @param string $text The input label title shown to non-JS users.
				 */
				$text = (string) \apply_filters( 'the_seo_framework_honeypot_label', \__( 'Comments for robots', 'the-seo-framework-extension-manager' ) );
				break;

			default :
				$text = '';
				break;
		endswitch;

		return $text;
	}

	/**
	 * Generates an ID hash so bots can't automatically exclude this field.
	 * Each key is valid for 1 hour.
	 *
	 * This shouldn't affect users who stay on a comment section for longer,
	 * the hash just never passes through the spam check. Which is fine.
	 *
	 * @since 1.0.0
	 * @staticvar array $_hashes
	 *
	 * @param int  $length   The length of the hash to get.
	 * @param bool $flip     Whether to flip the hash key prior to returning it.
	 * @param bool $previous Whether to get the previous hash.
	 * @return string The $_POST form field hash.
	 */
	private function get_hashed_field_name( $length = 32, $flip = false, $previous = false ) {

		static $_hashes = [];

		if ( empty( $_hashes ) ) {
			/**
			 * Applies filters 'the_seo_framework_honeypot_scale'
			 *
			 * Set this lower if you are a prominent spam target.
			 * Lower than 150 seconds (total 300 i.e. 5 minutes) is not recommended,
			 * as some bots purposely wait.
			 * If you're using page caching, set this higher.
			 *
			 * @since 1.0.0
			 *
			 * @param int $scale The time in seconds on how fast the check works.
			 *            Note that this value is doubled for the fallback check.
			 */
			$scale = (int) \apply_filters( 'the_seo_framework_honeypot_scale', 5 * MINUTE_IN_SECONDS );

			$_hashes = [
				'current'  => \tsf_extension_manager()->get_timed_hash( __METHOD__, $scale ),
				'previous' => \tsf_extension_manager()->get_timed_hash( __METHOD__, $scale, time() - $scale ),
			];
		}

		if ( $previous ) {
			$hash = (string) substr( $_hashes['previous'], 0, $length );
			return $flip ? strrev( $hash ) : $hash;
		} else {
			$hash = (string) substr( $_hashes['current'], 0, $length );
			return $flip ? strrev( $hash ) : $hash;
		}
	}
}
