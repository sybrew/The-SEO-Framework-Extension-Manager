<?php
/**
 * @package TSF_Extension_Manager\Extension\Honeypot
 */
namespace TSF_Extension_Manager\Extension\Honeypot;

/**
 * Extension Name: Honeypot - *beta*
 * Extension URI: https://premium.theseoframework.com/extensions/honeypot/
 * Extension Description: The Honeypot extension catches comment spammers in four lightweight yet powerful ways. By adding hashed input fields that only real browsers can clear, it has a near 100% catch-rate.
 * Extension Version: 1.0.1-***β***
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
 * The extension version.
 * @since 1.0.0
 */
define( 'TSFEM_E_HONEYPOT_VERSION', '1.0.1' );

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

	//* Don't run on the admin side. This extension is front-end only. For now.
	if ( \is_admin() )
		return $loaded = false;

	new Core;

	return $loaded = true;
}

/**
 * Class TSF_Extension_Manager\Extension\Honeypot\Core
 *
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Core {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * Determines whether the class has been constructed.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $setup
	 */
	private $setup = false;

	/**
	 * Determines whether the spam validation is extremely vibrant and dynamic.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $hardcore
	 */
	private $hardcore = false;

	/**
	 * Maintains array of properties, like fields.
	 *
	 * @since 1.0.0
	 *
	 * @var array $hp_properties
	 */
	private $hp_properties = [];

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->setup = true;

		//* Adds honeypot to comment fields.
		\add_action( 'comment_form_top', [ $this, '_add_honeypot' ] );

		//* Checks honeypot existence before setting approval of a comment.
		\add_filter( 'pre_comment_approved', [ $this, '_check_honeypot' ], 0, 2 );
	}

	/**
	 * Generates and outputs honeypot comment field within the comment forms.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void Early if not setup (i.e. statically called) or if user is logged in.
	 */
	public function _add_honeypot() {

		if ( \is_user_logged_in() )
			return;

		$this->set_hardcore();
		$setup = $this->setup_display_properties() && $this->setup_post_check_properties();

		if ( ! $setup )
			return;

		$this->output_css_honeypot();
		$this->output_css_rotation_honeypot();
		$this->output_js_honeypot();
		$this->output_nonce_honeypot();
	}

	/**
	 * Checks generated honeypot fields, if any.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string|int $approved    The current approval state.
	 * @param array      $commentdata Comment data.
	 * @return string|int The new approval state.
	 */
	public function _check_honeypot( $approved = '', $commentdata = [] ) {

		if ( ! $this->setup )
			return $approved;

		// No need to check further.
		if ( 'spam' === $approved || 'trash' === $approved )
			return $approved;

		//* These checks only work if user is not logged in.
		if ( \is_user_logged_in() )
			return $approved;

		$this->set_hardcore();
		$this->set_id( $commentdata );

		$this->setup_post_check_properties();

		$i = 0;
		do {
			switch ( $i ) :
				case 0 :
					$this->check_css_field( $approved );
					break;

				case 1 :
					$this->check_css_rotation_fields( $approved );
					break;

				case 2 :
					$this->check_js_field( $approved );
					break;

				case 3 :
					$this->check_nonce_rotation_field( $approved );
					break;

				default :
					break 2;
			endswitch;
			$i++;
		} while ( 'spam' !== $approved );

		return $approved;
	}

	/**
	 * Outputs CSS honeypot.
	 *
	 * This input field is shown and can be filled in when CSS is disabled.
	 *
	 * @since 1.0.0
	 * @todo Set CSS external rather than inline when http/2 using HTML5 spec?
	 */
	private function output_css_honeypot() {
		printf(
			'<p style="display:none;"><input type="text" name="%1$s" value=""></p>',
			\esc_attr( $this->hp_properties['css_input_name'] )
		);
	}

	/**
	 * Outputs CSS Rotation honeypot.
	 *
	 * This input field is shown and can be filled in when CSS is disabled.
	 * This input field uses a 2 hour valid name, rotated by half of that time.
	 *
	 * If filled in but when name is expired, it can't be checked against.
	 *
	 * When not hardcore, the name is valid indefinitely, differentiating per post.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Moved display annotation into a scoped style node.
	 * @todo Set CSS external rather than inline when http/2 using HTML5 spec?
	 */
	private function output_css_rotation_honeypot() {
		printf(
			'<p id="%1$s"><input type="text" name="%1$s" value=""><style scoped>#%1$s{display:none}</style></p>',
			\esc_attr( $this->hp_properties['css_rotate_input_name'] )
		);
	}

	/**
	 * Outputs JS honeypot.
	 *
	 * This textarea is shown and must be manually emptied when JS is disabled.
	 * Because real users without JS can see this, i18n friendly text is displayed.
	 *
	 * The IDs are rotated, they're only effective on the browser.
	 * The field name is static, and is unique per post.
	 *
	 * @since 1.0.0
	 */
	private function output_js_honeypot() {
		vprintf(
			'<p id="%1$s">
				<label for="%2$s">%3$s</label>
				<textarea type="text" name="%2$s" id="%4$s" placeholder="%5$s">%6$s</textarea>
			</p>
			<script type="text/javascript">document.getElementById("%4$s").value="";document.getElementById("%1$s").style.display="none";</script>',
			[
				\sanitize_key( $this->hp_properties['js_rotate_wrapper_id'] ),
				\sanitize_key( $this->hp_properties['js_input_name'] ),
				\esc_html( $this->hp_properties['js_input_label_i18n'] ),
				\sanitize_key( $this->hp_properties['js_rotate_input_id'] ),
				\esc_attr( $this->hp_properties['js_input_placeholder_i18n'] ),
				\esc_textarea( $this->hp_properties['js_input_value_i18n'] ),
			]
		);
	}

	/**
	 * Outputs nonce honeypot.
	 *
	 * This input field outputs a 24 hour valid nonce, rotated by half of that time.
	 * When not hardcore, the nonce is valid for 10 days, rotated by half of that time.
	 *
	 * @since 1.0.0
	 */
	private function output_nonce_honeypot() {
		vprintf(
			'<input type="hidden" name="%1$s" value="%2$s">',
			[
				\sanitize_key( $this->hp_properties['nonce_input_name'] ),
				\esc_attr( $this->hp_properties['nonce_rotated_input_value'] ),
			]
		);
	}

	/**
	 * Checks the static CSS input field that ought to be empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_css_field( &$approved ) {

		//* Perform same sanitation as displayed.
		$_field = \esc_attr( $this->hp_properties['css_input_name'] );

		//* Check if input is set.
		$set = ! empty( $_POST[ $_field ] ) ?: false;

		if ( $set ) {
			// Empty check failed.
			$approved = 'spam';
			unset( $_POST[ $_field ] );
		}
	}

	/**
	 * Checks the CSS input fields that ought to be empty, based on rotation.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_css_rotation_fields( &$approved ) {

		//* Perform same sanitation as displayed.
		$_fields = \map_deep( [
			$this->hp_properties['css_rotate_input_name'],
			$this->hp_properties['css_rotate_input_name_previous'],
		], '\\esc_attr' );

		//* This is a low-level check... transform to higher level i.e. array_intersect()?
		$field = ( empty( $_POST[ $_fields[0] ] ) xor $k = 0 xor 1 ) ?: ( empty( $_POST[ $_fields[1] ] ) xor $k = 1 ) ?: $k = false;

		if ( $field ) {
			// Empty check failed.
			$approved = 'spam';
			unset( $_POST[ $_fields[ $k ] ] );
		}
	}

	/**
	 * Checks the static JS input field that ought to be empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_js_field( &$approved ) {

		//* Perform same sanitation as displayed.
		$_field = \esc_attr( $this->hp_properties['js_input_name'] );

		//* Check if input is set.
		$set = ! empty( $_POST[ $_field ] ) ?: false;

		if ( $set ) {
			// Empty check failed.
			$approved = 'spam';
			unset( $_POST[ $_field ] );
		}
	}

	/**
	 * Checks the input fields that ought to be set.
	 * This prevents POST hijack spam and is timing attack safe.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 * @return void Early if field not POSTed, therefore spam.
	 */
	private function check_nonce_rotation_field( &$approved ) {

		$_field = $this->hp_properties['nonce_input_name'];

		if ( empty( $_POST[ $_field ] ) ) {
			$approved = 'spam';
			return;
		}

		//* Perform same sanitation as displayed.
		$_nonces = \map_deep( [
			$this->hp_properties['nonce_rotated_input_value'],
			$this->hp_properties['nonce_rotated_input_value_previous'],
		], '\\esc_attr' );

		$_tick = 0;
		$_input = $_POST[ $_field ];

		if ( hash_equals( $_nonces[0], $_input ) ) :
			$_tick = 1;
		elseif ( hash_equals( $_nonces[1], $_input ) ) :
			$_tick = 2;
		endif;

		if ( $_tick < 1 ) {
			$approved = 'spam';
			unset( $_POST[ $_field ] );
		}
	}

	/**
	 * Sets the current ID.
	 *
	 * @since 1.0.0
	 *
	 * @param array $commentdata Required. The commentdata on POST.
	 */
	private function set_id( array $commentdata ) {
		$this->get_id( $commentdata );
	}

	/**
	 * Returns the current ID.
	 *
	 * If on POST, use $this->set_id() beforehand.
	 *
	 * @since 1.0.0
	 * @staticvar int $id
	 *
	 * @param array $commentdata Optional. The commentdata on POST.
	 * @return int The post ID.
	 */
	private function get_id( array $commentdata = [] ) {

		static $id = null;

		return $id ?: $id = (int) ( isset( $commentdata['comment_post_ID'] ) ? $commentdata['comment_post_ID'] : \get_the_ID() );
	}

	/**
	 * Enables or disables hardcore mode based on caching and filters.
	 *
	 * @since 1.0.0
	 */
	private function set_hardcore() {
		/**
		 * Applies filters 'the_seo_framework_honeypot_hardcore'
		 *
		 * Determines whether the hashing is randomized, or otherwise static.
		 * Set this to true if you don't use caching and still get spam through.
		 *
		 * @uses @const WP_CACHE If WP_CACHE is true, hardcore is false.
		 *
		 * @todo make option.
		 * @param bool $hardcore
		 */
		$this->hardcore = (bool) \apply_filters( 'the_seo_framework_honeypot_hardcore', ! WP_CACHE );
	}

	/**
	 * Sets class field properties.
	 *
	 * Front-& back-end.
	 *
	 * @since 1.0.0
	 * @see $this->hp_properties
	 *
	 * @return bool True on success, false when class isn't constructed.
	 */
	private function setup_post_check_properties() {

		if ( $this->setup ) {
			$this->hp_properties += [
				/**
				 * Preventing CSS-disabled bots.
				 * Value must be empty.
				 */
				'css_input_name'                 => $this->get_static_hashed_field_name( 24 ),
				'css_rotate_input_name'          => $this->get_rotated_hashed_field_name( 24, false ),
				'css_rotate_input_name_previous' => $this->get_rotated_hashed_field_name( 24, false, true ),

				/**
				 * Preventing JS-disabled bots and weak GET injection.
				 * Value must be empty.
				 */
				'js_input_name' => 'tsfem-e-hp-js',

				/**
				 * Preventing /wp-comments-post.php and other POST injection.
				 * Value must be filled in.
				 */
				'nonce_input_name'                   => 'tsfem-e-hp-nonce',
				'nonce_rotated_input_value'          => $this->get_rotated_hashed_nonce_value( 24, false ),
				'nonce_rotated_input_value_previous' => $this->get_rotated_hashed_nonce_value( 24, false, true ),
			];
			return true;
		}
		return false;
	}

	/**
	 * Sets class display properties.
	 *
	 * Front-end only.
	 *
	 * This method is not cache sensitive as it's for display only, so hashing
	 * can be used generously.
	 *
	 * @since 1.0.0
	 * @see $this->hp_properties
	 *
	 * @return bool True on success, false when class isn't constructed.
	 */
	private function setup_display_properties() {

		if ( $this->setup ) {
			$this->hp_properties += [
				'js_rotate_wrapper_id'      => 'comment-form-' . $this->get_rotated_hashed_field_name( mt_rand( 13, 23 ), (bool) mt_rand( 0, 1 ) ),
				'js_input_label_i18n'       => $this->get_text( 'js_label' ),
				'js_rotate_input_id'        => 'comment-form-' . $this->get_rotated_hashed_field_name( mt_rand( 13, 23 ), (bool) mt_rand( 0, 1 ) ),
				'js_input_placeholder_i18n' => $this->get_text( 'js_placeholder' ),
				'js_input_value_i18n'       => $this->get_text( 'js_input' ),
			];
			return true;
		}
		return false;
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
			case 'js_placeholder' :
				/**
				 * Applies filters 'the_seo_framework_honeypot_placeholder'
				 *
				 * @since 1.0.0
				 *
				 * @param string $text The placeholder text shown to non-JS users.
				 */
				$text = (string) \apply_filters( 'the_seo_framework_honeypot_placeholder', \__( 'You are human!', 'the-seo-framework-extension-manager' ) );
				break;

			case 'js_input' :
				/**
				 * Applies filters 'the_seo_framework_honeypot_input'
				 *
				 * @since 1.0.0
				 *
				 * @param string $text The input field text that needs to be removed shown to non-JS users.
				 */
				$text = (string) \apply_filters( 'the_seo_framework_honeypot_input', \__( "Please remove this comment to prove you're human.", 'the-seo-framework-extension-manager' ) );
				break;

			case 'js_label' :
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
	 * Generates a hashed field name so bots can't automatically exclude this field.
	 *
	 * If hardcore, each key is valid for 60 minutes per post ID. Totalling to 120 minutes.
	 * Otherwise, each key is unique per post ID.
	 *
	 * @since 1.0.0
	 * @staticvar array $_hashes
	 *
	 * @param int  $length   The length of the hash to get.
	 * @param bool $flip     Whether to flip the hash key prior to returning it.
	 * @param bool $previous Whether to get the previous hash.
	 * @return string The $_POST form field hash.
	 */
	private function get_rotated_hashed_field_name( $length = 24, $flip = false, $previous = false ) {

		static $_hashes = [];

		if ( empty( $_hashes ) ) {

			$uid = $this->get_id() . '+' . __METHOD__ . '+' . $GLOBALS['blog_id'];

			if ( $this->hardcore ) {
				/**
				 * Applies filters 'the_seo_framework_honeypot_field_scale'
				 *
				 * Set this lower if you are a prominent spam target.
				 * Lower than 300 seconds (total 600 i.e. 10 minutes) is not recommended,
				 * as some bots purposely wait.
				 * If you're using page caching whilst in hardcore mode, set this higher.
				 *
				 * @since 1.0.0
				 *
				 * @param int $scale The time in seconds on how fast the check works.
				 *            Note that this value is doubled for the fallback check.
				 */
				$scale = (int) \apply_filters( 'the_seo_framework_honeypot_field_scale', 60 * MINUTE_IN_SECONDS );

				$_hashes = [
					'current'  => \tsf_extension_manager()->_get_timed_hash( $uid, $scale ),
					'previous' => \tsf_extension_manager()->_get_timed_hash( $uid, $scale, time() - $scale ),
				];
			} else {
				$_hash = \tsf_extension_manager()->_get_uid_hash( $uid );
				$_hashes = [
					'current'  => $_hash,
					'previous' => $_hash,
				];
			}
		}

		$hash = $previous ? $_hashes['previous'] : $_hashes['current'];
		$hash = $flip ? strrev( $hash ) : $hash;
		return (string) substr( $hash, 0, $length );
	}

	/**
	 * Generates a hashed field name so bots can't automatically exclude this field.
	 * Each key is different per Post ID minutes.
	 *
	 * @since 1.0.0
	 * @staticvar string $_hash
	 *
	 * @param int  $length   The length of the hash to get.
	 * @param bool $flip     Whether to flip the hash key prior to returning it.
	 * @return string The $_POST form field hash.
	 */
	private function get_static_hashed_field_name( $length = 24, $flip = false ) {

		static $_hash = [];

		if ( empty( $_hash ) ) {
			$uid = $this->get_id() . '+' . __METHOD__ . '+' . $GLOBALS['blog_id'];
			$_hash = \tsf_extension_manager()->_get_uid_hash( $uid );
		}

		$hash = $flip ? strrev( $_hash ) : $_hash;
		return (string) substr( $hash, 0, $length );
	}

	/**
	 * Generates a hashed nonce so bots can't use PHP files to spam comments.
	 *
	 * If hardcore, each key is valid for 12 hours. For a total of 24 hours comment time.
	 * Otherwise, each key is valid for 5 days. For a total of 10 days comment time.
	 *
	 * This will affect users who stay on a comment section for longer,
	 * the hash will then fail the spam check. 24 hours is very generous, however.
	 *
	 * @since 1.0.0
	 * @staticvar array $_hashes
	 *
	 * @param int  $length   The length of the hash to get.
	 * @param bool $flip     Whether to flip the hash key prior to returning it.
	 * @param bool $previous Whether to get the previous hash.
	 * @return string The $_POST form nonce value hash.
	 */
	private function get_rotated_hashed_nonce_value( $length = 24, $flip = false, $previous = false ) {

		static $_hashes = [];

		if ( empty( $_hashes ) ) {

			$uid = $this->get_id() . '+' . __METHOD__ . '+' . $GLOBALS['blog_id'];

			$time = $this->hardcore ? 12 * HOUR_IN_SECONDS : 5 * DAY_IN_SECONDS;

			/**
			 * Applies filters 'the_seo_framework_honeypot_nonce_scale'
			 *
			 * Set this lower if you are a prominent spam target.
			 * Lower than 3600 seconds (total 7200 i.e. 2 hours) is not recommended,
			 * as some users generously wait to comment (closing laptop and such).
			 * If you're using page caching, set this higher.
			 *
			 * @since 1.0.0
			 *
			 * @param int $scale The time in seconds on how fast the check works.
			 *            Note that this value is doubled for the fallback check.
			 */
			$scale = (int) \apply_filters( 'the_seo_framework_honeypot_nonce_scale', $time );

			$_hashes = [
				'current'  => \tsf_extension_manager()->_get_timed_hash( $uid, $scale ),
				'previous' => \tsf_extension_manager()->_get_timed_hash( $uid, $scale, time() - $scale ),
			];
		}

		$hash = $previous ? $_hashes['previous'] : $_hashes['current'];
		$hash = $flip ? strrev( $hash ) : $hash;
		return (string) substr( $hash, 0, $length );
	}
}
