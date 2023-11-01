<?php
/**
 * @package TSF_Extension_Manager\Extension\Honeypot
 */

namespace TSF_Extension_Manager\Extension\Honeypot;

/**
 * Extension Name: Honeypot
 * Extension URI: https://theseoframework.com/extensions/honeypot/
 * Extension Description: The Honeypot extension catches comment spammers with a 99.99% catch-rate using five lightweight yet powerful methods that won't leak data from your site.
 * Extension Version: 2.0.1
 * Extension Author: Sybre Waaijer
 * Extension Author URI: https://cyberwire.nl/
 * Extension License: GPLv3
 */

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Honeypot extension for The SEO Framework
 * Copyright (C) 2017-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 *
 * @since 1.0.0
 */
\define( 'TSFEM_E_HONEYPOT_VERSION', '2.0.1' );

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\honeypot_init', 11 );
/**
 * Initializes the plugin.
 *
 * @since 1.0.0
 *
 * @return bool True if class is loaded.
 */
function honeypot_init() {

	static $loaded;

	// Don't init the class twice.
	if ( isset( $loaded ) )
		return $loaded;

	// Don't run on the admin side. This extension is front-end only. For now.
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
	use \TSF_Extension_Manager\Construct_Master_Once_Final_Interface;

	/**
	 * @since 1.0.0
	 * @var bool Whether the class has been constructed.
	 */
	private $setup = false;

	/**
	 * @since 1.0.0
	 * @var bool Whether the spam validation is extremely vibrant and dynamic.
	 */
	private $hardcore = false;

	/**
	 * @since 1.0.0
	 * @var array Array of properties, like fields.
	 */
	private $hp_properties = [];

	/**
	 * @since 2.0.0
	 * @var int Expected nonce length.
	 */
	private $nonce_length = 20;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function construct() {

		$this->setup = true;

		// Adds honeypot to comment fields.
		// We could filter 'comment_form_fields' and randomly shuffle our fields into it.
		// However, that would be somewhat unreliable.
		\add_action(
			mt_rand( 0, 1 ) ? 'comment_form_after_fields' : 'comment_form_before_fields',
			[ $this, '_add_honeypot' ]
		);
		// Do we want to filter 'comment_form_field_comment', change the name of it, and adjust the comment-form catcher?
		// That'd be amazing, but also break themes that use name-queries.

		// Checks honeypot existence before setting approval of a comment.
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

		$shuffle = range( 0, 4 );
		shuffle( $shuffle );

		// TODO log number of times a comment gets caught by X type? (and 'since' record?)
		// That'd be 'fun' and 'interesting' for the user. That's it, though.
		// TODO Allow user to (auto/optionally) send data to us, for us to showcase how many comments are blocked?
		// Attach unique ID to each user sending it? Must be unique from TSFEM activation ID, though.
		foreach ( $shuffle as $honeypot ) {
			switch ( $honeypot ) {
				case 0:
					$this->output_css_honeypot();
					break;
				case 1:
					$this->output_css_rotation_honeypot();
					break;
				case 2:
					$this->output_js_honeypot();
					break;
				case 3:
					$this->output_nonce_honeypot();
					break;
				case 4:
					$this->output_timer_honeypot();
					break;
				default:
					break 2;
			}
		}
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
		if ( \in_array( $approved, [ 'spam', 'thrash' ], true ) )
			return $approved;

		// These checks only work if user is not logged in.
		if ( \is_user_logged_in() )
			return $approved;

		$this->set_hardcore();
		$this->set_id( $commentdata );

		$this->setup_post_check_properties();

		$i = 0;
		do {
			switch ( ++$i ) {
				case 1:
					$this->check_css_field( $approved );
					break;

				case 2:
					$this->check_css_rotation_fields( $approved );
					break;

				case 3:
					$this->check_js_field( $approved );
					break;

				case 4:
					$this->check_nonce_rotation_field( $approved );
					break;

				case 5:
					$this->check_timer_field( $approved );
					break;

				default:
					break 2;
			}
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
			'<p style="display:none;"><input type="text" name="%1$s" value=""></p>', // Keep XHTML valid!
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
	 * @since 2.0.0 Added a fake label: Website.
	 * @since 2.0.1 Removed scoped tag for style, which became deprecated. Doesn't affect.
	 * @todo Set CSS external rather than inline when http/2 using HTML5 spec?
	 */
	private function output_css_rotation_honeypot() {
		printf(
			// Keep XHTML valid!
			'<p id="%1$s"><label for="%1$s">Website</label><input type="text" name="%1$s" value=""><style>#%1$s{display:none}</style></p>',
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

		$items = [
			'wrapper_id' => \sanitize_key( $this->hp_properties['js_rotate_wrapper_id'] ),
			'input_name' => \sanitize_key( $this->hp_properties['js_input_name'] ),
			'input_id'   => \sanitize_key( $this->hp_properties['js_rotate_input_id'] ),
			'label_i18n' => \esc_html( $this->hp_properties['js_input_label_i18n'] ),
			'ph_i18n'    => \esc_attr( $this->hp_properties['js_input_placeholder_i18n'] ),
			'value_i18n' => \esc_textarea( $this->hp_properties['js_input_value_i18n'] ),
		];

		// All values have been sanitized!
		$php_values = json_encode(
			[
				'w' => $items['wrapper_id'],
				'i' => $items['input_id'],
			],
			JSON_FORCE_OBJECT
		);

		$script = <<<JS
(a=>{let b=document.getElementById(a.i),c=document.getElementById(a.w);b&&c&&(b.value="",c.style.display="none")})($php_values);
JS;

		// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already taken care of.
		vprintf(
			'<p id="%1$s">
				<label for="%4$s">%3$s</label>
				<textarea name="%2$s" id="%4$s" placeholder="%5$s">%6$s</textarea>
				<script>%7$s</script>
			</p>',
			[
				$items['wrapper_id'],
				$items['input_name'],
				$items['label_i18n'],
				$items['input_id'],
				$items['ph_i18n'],
				$items['value_i18n'],
				$script,
			]
		);
		// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
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
			'<input type="hidden" name="%1$s" value="%2$s">', // Keep XHTML valid!
			[
				\sanitize_key( $this->hp_properties['nonce_input_name'] ),
				\esc_attr( $this->hp_properties['nonce_rotated_input_value'] ),
			]
		);
	}

	/**
	 * Outputs timer honeypot.
	 *
	 * This input field forwards random, mangled floating point numbers.
	 *
	 * @since 2.0.0
	 */
	private function output_timer_honeypot() {

		/**
		 * @since 2.0.0
		 * @todo make option.
		 * @param float $time
		 */
		$time = (float) \apply_filters( 'the_seo_framework_honeypot_countdown_time', 5.33 );

		// Random 16 bit timer scale. This converts to string, but that's what we need for reliable JS output.
		// 0x00FF is subtracted for we reserve 8 bits as unknown zero-offset in JS.
		$random_scale = number_format( mt_rand( 1, (int) ( 0xFF00 / $time ) ), 2, '.', '' );

		// This converts to string, but that's what we need anyway for reliable JS output.
		$random_time = number_format( $time * $random_scale, 2, '.', '' );

		$input_name = \sanitize_key( $this->hp_properties['timer_input_name'] );

		// All values have been sanitized!
		$php_values = json_encode(
			[
				'n' => $input_name,
				's' => $random_scale,
				't' => $random_time,
			],
			JSON_FORCE_OBJECT
		);

		// Can we make this even smaller without losing functionality? Unpacked source: /timer.js
		$script = <<<JS
(a=>{let b,c,d=document.getElementsByName(a.n)[0],e=255*(1-Math.random()),f=f=>{void 0===b&&(b=f),c=f-b,c<1e3*(+a.t/+a.s)?(d.value=+a.t+e-c/1e3,g()):d.value=""},g=()=>setTimeout(()=>requestAnimationFrame(f),100+200*Math.random());d&&(d.value=+a.t+e,g())})($php_values);
JS;

		// phpcs:disable, WordPress.Security.EscapeOutput.OutputNotEscaped -- Already taken care of.
		vprintf(
			'<input type="hidden" name="%s" value=""><script>%s</script>', // Keep XHTML valid!
			[
				$input_name,
				$script,
			]
		);
		// phpcs:enable, WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Checks the static CSS input field that ought to be empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_css_field( &$approved ) {

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- No data is processed.

		// Perform same sanitization during display.
		$_field = \esc_attr( $this->hp_properties['css_input_name'] );

		// Check if input is set.
		if ( ! empty( $_POST[ $_field ] ) ) {
			// Empty check failed.
			$approved = 'spam';
		}

		// phpcs:enable, WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Checks the CSS input fields that ought to be empty, based on rotation.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_css_rotation_fields( &$approved ) {

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- No data is processed.

		// Perform same sanitization during display.
		$fields = \map_deep(
			[
				$this->hp_properties['css_rotate_input_name'],
				$this->hp_properties['css_rotate_input_name_previous'],
			],
			'\\esc_attr'
		);
		foreach ( $fields as $input ) {
			if ( ! empty( $_POST[ $input ] ) ) {
				// Empty check failed.
				$approved = 'spam';
				break;
			}
		}

		// phpcs:enable, WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Checks the static JS input field that ought to be empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_js_field( &$approved ) {

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- No data is processed.

		// Perform same sanitization as displayed.
		$_field = \sanitize_key( $this->hp_properties['js_input_name'] );

		// Check if input is set.
		if ( ! empty( $_POST[ $_field ] ) ) {
			// Empty check failed.
			$approved = 'spam';
		}

		// phpcs:enable, WordPress.Security.NonceVerification.Missing
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

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- No data is processed.

		// Perform same sanitization as displayed.
		$_field = \sanitize_key( $this->hp_properties['nonce_input_name'] );

		if ( empty( $_POST[ $_field ] ) ) {
			$approved = 'spam';
			return;
		}

		$_nonces = [
			$this->hp_properties['nonce_rotated_input_value'],
			$this->hp_properties['nonce_rotated_input_value_previous'],
		];

		foreach ( $_nonces as $i => $_nonce ) {
			// Perform same sanitization as displayed and trim to the usable length.
			$_nonces[ $i ] = substr( \esc_attr( $_nonce ), 0, $this->nonce_length );
		}

		$_tick  = 0;
		$_input = substr( $_POST[ $_field ], 0, $this->nonce_length );

		if ( hash_equals( $_nonces[0], $_input ) ) :
			$_tick = 1;
		elseif ( hash_equals( $_nonces[1], $_input ) ) :
			$_tick = 2;
		endif;

		if ( $_tick < 1 )
			$approved = 'spam';

		// phpcs:enable, WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Checks the input fields that ought to be empty.
	 *
	 * @since 2.0.0
	 *
	 * @param string|int $approved The current approval state. Passed by reference.
	 */
	private function check_timer_field( &$approved ) {

		// phpcs:disable, WordPress.Security.NonceVerification.Missing -- No data is processed.

		// Perform same sanitization as displayed.
		$_field = \sanitize_key( $this->hp_properties['timer_input_name'] );

		if ( ! empty( $_POST[ $_field ] ) )
			$approved = 'spam';

		// phpcs:enable, WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Sets the current ID.
	 *
	 * @since 1.0.0
	 *
	 * @param array $commentdata Required. The commentdata on POST.
	 */
	private function set_id( $commentdata ) {
		$this->get_id( $commentdata );
	}

	/**
	 * Returns the current ID.
	 *
	 * If on POST, use $this->set_id() beforehand.
	 *
	 * @since 1.0.0
	 *
	 * @param array $commentdata Optional. The commentdata on POST.
	 * @return int The post ID.
	 */
	private function get_id( $commentdata = [] ) {
		static $id;
		return $id ?? (
			$id = (int) ( $commentdata['comment_post_ID'] ?? \get_the_ID() )
		);
	}

	/**
	 * Enables or disables hardcore mode based on caching and filters.
	 *
	 * @since 1.0.0
	 */
	private function set_hardcore() {
		/**
		 * Determines whether the hashing is randomized, or otherwise static.
		 * Set this to true if you don't use caching and still get spam through.
		 *
		 * @since 1.0.0
		 * @uses const WP_CACHE If WP_CACHE is true, hardcore is false.
		 * @todo make option.
		 * @param bool $hardcore
		 */
		$this->hardcore = (bool) \apply_filters( 'the_seo_framework_honeypot_hardcore', ! \WP_CACHE );
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
			// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned -- This is readable.
			$this->hp_properties += [
				/**
				 * Preventing CSS-disabled bots.
				 * Value must be empty.
				 */
				'css_input_name'                 =>
					$this->get_static_hashed_field_name( 11 ) . 'C' . $this->get_static_hashed_field_name( 12 ),
				'css_rotate_input_name'          =>
					$this->get_rotated_hashed_field_name( 24, false ),
				'css_rotate_input_name_previous' =>
					$this->get_rotated_hashed_field_name( 24, false, true ),

				/**
				 * Preventing JS-disabled bots and weak GET injection.
				 * Value must be empty.
				 */
				'js_input_name' =>
					$this->get_static_hashed_field_name( 11 ) . 'J' . $this->get_static_hashed_field_name( 12, true ),

				/**
				 * Preventing /wp-comments-post.php and other POST injection.
				 * Value must be filled in.
				 */
				'nonce_input_name'                   =>
					$this->get_static_hashed_field_name( 11, true ) . 'N' . $this->get_static_hashed_field_name( 12 ),
				'nonce_rotated_input_value'          =>
					$this->get_rotated_hashed_nonce_value( mt_rand( $this->nonce_length, 49 ), false ),
				'nonce_rotated_input_value_previous' =>
					$this->get_rotated_hashed_nonce_value( $this->nonce_length, false ),

				/**
				 * Preventing real browsers commenting instantly.
				 * Value must be empty.
				 */
				'timer_input_name' =>
					$this->get_static_hashed_field_name( 11, true ) . 'T' . $this->get_static_hashed_field_name( 12, true ),
			];
			// phpcs:enable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
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
				'js_rotate_wrapper_id'      =>
					$this->get_rotated_hashed_field_name( mt_rand( 13, 23 ), (bool) mt_rand( 0, 1 ) ),
				'js_input_label_i18n'       =>
					$this->get_text( 'js_label' ),
				'js_rotate_input_id'        =>
					$this->get_rotated_hashed_field_name( mt_rand( 13, 23 ), (bool) mt_rand( 0, 1 ) ),
				'js_input_placeholder_i18n' =>
					$this->get_text( 'js_placeholder' ),
				'js_input_value_i18n'       =>
					$this->get_text( 'js_input' ),
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

		switch ( $what ) {
			case 'js_placeholder':
				/**
				 * @since 1.0.0
				 * @param string $text The placeholder text shown to non-JS users.
				 */
				$text = (string) \apply_filters(
					'the_seo_framework_honeypot_placeholder',
					\__( 'You are human!', 'the-seo-framework-extension-manager' )
				);
				break;

			case 'js_input':
				/**
				 * @since 1.0.0
				 * @param string $text The input field text that needs to be removed shown to non-JS users.
				 */
				$text = (string) \apply_filters(
					'the_seo_framework_honeypot_input',
					\__( "Please empty this comment field to prove you're human.", 'the-seo-framework-extension-manager' )
				);
				break;

			case 'js_label':
				/**
				 * @since 1.0.0
				 * @param string $text The input label title shown to non-JS users.
				 */
				$text = (string) \apply_filters(
					'the_seo_framework_honeypot_label',
					\__( 'Comment for robots', 'the-seo-framework-extension-manager' )
				);
				break;
		}

		return $text ?? '';
	}

	/**
	 * Generates a hashed field name so bots can't automatically exclude this field.
	 *
	 * If hardcore, each key is valid for 60 minutes per post ID. Totalling to 120 minutes.
	 * Otherwise, each key is unique per post ID.
	 *
	 * @since 1.0.0
	 * @since 1.0.2 Values always start with an alphabetic character
	 *
	 * @param int  $length   The length of the hash to get.
	 * @param bool $flip     Whether to flip the hash key prior to returning it.
	 * @param bool $previous Whether to get the previous hash.
	 * @return string The $_POST form field hash.
	 */
	private function get_rotated_hashed_field_name( $length = 24, $flip = false, $previous = false ) {

		static $_hashes;

		if ( ! isset( $_hashes ) ) {

			$uid   = $this->get_id() . '+' . __METHOD__ . '+' . $GLOBALS['blog_id'];
			$tsfem = \tsfem();

			if ( $this->hardcore ) {
				/**
				 * Set this lower if you are a prominent spam target.
				 * Lower than 300 seconds (total 600 i.e. 10 minutes) is not recommended,
				 * as some bots purposely wait.
				 * If you're using page caching whilst in hardcore mode, set this higher.
				 *
				 * @since 1.0.0
				 * @param int $scale The time in seconds on how fast the check works.
				 *            Note that this value is doubled for the fallback check.
				 */
				$scale = (int) \apply_filters( 'the_seo_framework_honeypot_field_scale', 60 * MINUTE_IN_SECONDS );

				$_hashes = [
					'current'  => $tsfem->_get_timed_hash( $uid, $scale ),
					'previous' => $tsfem->_get_timed_hash( $uid, $scale, time() - $scale ),
				];
			} else {
				$_hash   = $tsfem->_get_uid_hash( $uid );
				$_hashes = [
					'current'  => $_hash,
					'previous' => $_hash,
				];
			}
		}

		$hash = $previous ? $_hashes['previous'] : $_hashes['current'];

		return $this->alpha_first(
			(string) substr(
				$flip ? strrev( $hash ) : $hash,
				0,
				$length
			)
		);
	}

	/**
	 * Generates a hashed field name so bots can't automatically exclude this field.
	 * Each key is different per Post ID.
	 *
	 * @since 1.0.0
	 * @since 1.0.2 Values always start with an alphabetic character
	 * @since 2.0.0 No longer returns solely hexadecimals, but performs Base62 conversion.
	 *
	 * @param int  $length   The length of the hash to get.
	 * @param bool $flip     Whether to flip the hash key prior to returning it.
	 * @return string The $_POST form field hash.
	 */
	private function get_static_hashed_field_name( $length = 24, $flip = false ) {

		static $hash = '';

		if ( ! \strlen( $hash ) )
			$hash = $this->hex_to_62_trim( \tsfem()->_get_uid_hash(
				$this->get_id() . '+' . __METHOD__ . '+' . $GLOBALS['blog_id']
			) );

		return $this->alpha_first(
			(string) substr(
				$flip ? strrev( $hash ) : $hash,
				0,
				$length
			)
		);
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
	 * @since 1.0.2 Values always start with an alphabetic character
	 * @since 2.0.0 No longer returns solely hexadecimals, but performs Base36 conversion.
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

			$time = $this->hardcore ? 12 * HOUR_IN_SECONDS : 5 * \DAY_IN_SECONDS;

			/**
			 * Set this lower if you are a prominent spam target.
			 * Lower than 3600 seconds (total 7200 i.e. 2 hours) is not recommended,
			 * as some users generously wait to comment (closing laptop and such).
			 * If you're using page caching, set this higher.
			 *
			 * @since 1.0.0
			 * @since 1.1.1 Now passes the $hardcore parameter.
			 * @see $this->set_hardcore()
			 *
			 * @param int  $scale    The time in seconds on how fast the check works.
			 *             Note that this value is doubled for the fallback check.
			 * @param bool $hardcore Whether hardcore mode is activated.
			 */
			$scale = (int) \apply_filters( 'the_seo_framework_honeypot_nonce_scale', $time, $this->hardcore );

			$tsfem = \tsfem();

			$_hashes = [
				'current'  => $this->hex_to_36_trim(
					$tsfem->_get_timed_hash( $uid, $scale )
				),
				'previous' => $this->hex_to_36_trim(
					$tsfem->_get_timed_hash( $uid, $scale, time() - $scale )
				),
			];
		}

		$hash = $previous ? $_hashes['previous'] : $_hashes['current'];

		return $this->alpha_first(
			(string) substr(
				$flip ? strrev( $hash ) : $hash,
				0,
				$length
			)
		);
	}

	/**
	 * Transforms well-known Base16 to Base36.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hex The Base16 value. If not Base16, the input value is returned.
	 * @return string The Base36 conversion without stray 0's.
	 *                If the input wasn't Base16, then it'll be returned without conversion.
	 */
	private function hex_to_36_trim( $hex ) {
		return ctype_xdigit( $hex ) ? rtrim( base_convert( $hex, 16, 36 ), '0' ) : $hex;
	}

	/**
	 * Transforms well-known Base16 to Base62... kinda... NON-reversible.
	 * Since PHP does not support Base62, we use Base64 conversion, and strip the unwanted extras.
	 *
	 * Pseudo-Base62.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hex The Base16 value.
	 * @return string The Base62 conversion without stray 0's.
	 *                If the input wasn't Base16, then it'll be returned without conversion.
	 */
	private function hex_to_62_trim( $hex ) {
		// phpcs:ignore, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- That's the idea.
		return str_replace( [ '=', '+', '/' ], '', base64_encode( $hex ) );
	}

	/**
	 * Transforms first character of hash to alphabetic if not.
	 *
	 * @since 1.0.2
	 * @since 1.1.1 Fixed OB1 error.
	 *
	 * @param string $hash A hash with a possible numeric first character
	 * @return string A hash with an alphabetical character first.
	 */
	private function alpha_first( $hash ) {

		$first_char = substr( $hash, 0, 1 );

		if ( ! is_numeric( $first_char ) )
			return $hash;

		$table = range( 'a', 'z' );
		// We can't divide by 0.
		$first_char = $first_char ?: 10;

		return $table[ round( \count( $table ) / $first_char ) - 1 ] . substr( $hash, 1 );
	}
}
