<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin
 */

namespace TSF_Extension_Manager\Extension\Cord;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

if ( \tsfem()->_blocked_extension_file( $_instance, $bits[1] ) ) return;

/**
 * Cord extension for The SEO Framework
 * Copyright (C) 2019-2023 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Cord\Admin
 *
 * Holds extension admin page methods.
 *
 * @since 1.0.0
 * @access private
 * @errorval 109xxxx
 * @uses TSF_Extension_Manager\Traits
 * @final
 */
final class Admin extends Core {
	use \TSF_Extension_Manager\Construct_Master_Once_Interface;

	/**
	 * Constructor.
	 */
	private function construct() {
		$this->prepare_settings();
	}

	/**
	 * Prepares settings GUI.
	 *
	 * @since 1.0.0
	 */
	private function prepare_settings() {

		\TSF_Extension_Manager\ExtensionSettings::prepare();

		\add_action( 'tsfem_register_settings_fields', [ $this, '_register_settings' ] );
		\add_action( 'tsfem_register_settings_sanitization', [ $this, '_register_sanitization' ] );
	}

	/**
	 * Registers settings for Articles.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $settings \TSF_Extension_Manager\ExtensionSettings
	 */
	public function _register_settings( $settings ) {

		$_settings = [
			'analytics' => [
				'_default' => null,
				'_edit'    => true,
				'_ret'     => '',
				'_req'     => false,
				'_type'    => 'plain_dropdown',
				'_desc'    => [
					'',
					\__( 'Connect your site with various third-party analytical platforms. Cord takes care of the rest.', 'the-seo-framework-extension-manager' ),
				],
				'_md'      => true,
				'_fields'  => [
					'google_analytics' => [
						'_default' => null,
						'_edit'    => true,
						'_ret'     => '',
						'_req'     => false,
						'_type'    => 'plain_multi',
						'_desc'    => [
							\__( 'Google Analytics', 'the-seo-framework-extension-manager' ),
							sprintf(
								/* translators: %s = Measurement ID documentation link. Markdown. */
								\__( 'Start tracking with [Google Analytics](%s) by filling in a Measurement ID.', 'the-seo-framework-extension-manager' ),
								'https://analytics.google.com/analytics/web/'
							),
						],
						'_md'      => true,
						'_fields'  => [
							'tracking_id'               => [
								'_default' => null,
								'_edit'    => true,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'text',
								'_pattern' => '(\bUA-\d{4,10}-\d{1,4}\b)|(\bG-[A-Z0-9]{4,15})', // Google doesn't specify length, assume 4~15 (norm is 10)
								'_desc'    => [
									\__( 'Measurement ID', 'the-seo-framework-extension-manager' ),
									sprintf(
										/* translators: %s = Measurement/Pixel ID documentation link. Markdown. */
										\__( 'Get your [Measurement ID](%s).', 'the-seo-framework-extension-manager' ),
										'https://support.google.com/analytics/answer/12270356'
									),
								],
								'_ph'      => 'G-ABCDE1FGH23',
								'_md'      => true,
							],
							'enhanced_link_attribution' => [
								'_default' => null,
								'_edit'    => false,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'checkbox',
							],
							'ip_anonymization'          => [
								'_default' => null,
								'_edit'    => false,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'checkbox',
							],
						],
					],
					'facebook_pixel'   => [
						'_default' => null,
						'_edit'    => true,
						'_ret'     => '',
						'_req'     => false,
						'_type'    => 'plain_multi',
						'_desc'    => [
							\__( 'Meta Pixel', 'the-seo-framework-extension-manager' ),
							sprintf(
								/* translators: %s = Measurement/Pixel ID documentation link. Markdown. */
								\__( 'Start tracking with [Meta pixel](%s) by filling in a Pixel ID.', 'the-seo-framework-extension-manager' ),
								'https://www.facebook.com/business/help/952192354843755'
							),
						],
						'_md'      => true,
						'_fields'  => [
							'pixel_id' => [
								'_default' => null,
								'_edit'    => true,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'text',
								'_pattern' => '[0-9]+',
								'_desc'    => [
									\__( 'Pixel ID', 'the-seo-framework-extension-manager' ),
									sprintf(
										/* translators: %s = Measurement/Pixel ID documentation link. Markdown. */
										\__( 'Get your [Pixel ID](%s).', 'the-seo-framework-extension-manager' ),
										'https://www.facebook.com/ads/manager/pixel/facebook_pixel'
									),
								],
								'_ph'      => '1234567890123456',
								'_md'      => true,
							],
						],
					],
				],
			],
		];

		$settings::register_settings(
			$this->o_index,
			[
				'title'    => 'Cord',
				'logo'     => TSFEM_E_CORD_DIR_URL . 'lib/images/icon.svg',
				'before'   => '',
				'after'    => '',
				'pane'     => [],
				'settings' => $_settings,
				// When we add more panes, we can order them by adding up to 9.9999 to this value.
				'priority' => \tsfem()->get_extension_order()['cord'],
			]
		);

		$settings::register_defaults( $this->o_index, $this->o_defaults );
	}

	/**
	 * Registers sanitization callbacks for Articles.
	 *
	 * @since 1.0.0
	 *
	 * @param string $settings \TSF_Extension_Manager\ExtensionSettings
	 */
	public function _register_sanitization( $settings ) {
		$settings::register_sanitization(
			$this->o_index,
			[
				'analytics' => [ static::class, '_sanitize_options_analytics' ],
			]
		);
	}

	/**
	 * Sanitizes the analytics settings index.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value The analytics index values.
	 * @return array The sanitized analytics index values.
	 */
	public static function _sanitize_options_analytics( $value ) {

		if ( ! \is_array( $value ) )
			$value = [];

		// TODO do we want to strip unknown entries from payload?
		// Yes, this is needlessly complex. It's built for future expansion...
		$valid_indexes = [
			'google_analytics',
			'facebook_pixel',
		];
		foreach ( $valid_indexes as $index ) :
			switch ( $index ) :
				case 'google_analytics':
					$key = 'tracking_id';

					$value[ $index ][ $key ] = trim( $value[ $index ][ $key ] ?? '' );

					// Google doesn't specify length, assume 4~15 (norm is 10)
					if ( ! preg_match( '/^(\bUA-\d{4,10}-\d{1,4}\b)|(\bG-[A-Z0-9]{4,15})$/', $value[ $index ][ $key ] ) )
						$value[ $index ][ $key ] = '';

					break;

				case 'facebook_pixel':
					$key = 'pixel_id';

					$value[ $index ][ $key ] = trim( $value[ $index ][ $key ] ?? '' );

					if ( ! preg_match( '/^[0-9]+$/', $value[ $index ][ $key ] ) )
						$value[ $index ][ $key ] = '';

					break;

				default:
					break;
			endswitch;
		endforeach;

		return $value;
	}
}
