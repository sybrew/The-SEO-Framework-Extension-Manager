<?php
/**
 * @package TSF_Extension_Manager\Extension\Cord\Admin
 */

namespace TSF_Extension_Manager\Extension\Cord;

defined( 'ABSPATH' ) or die;

if ( \tsf_extension_manager()->_has_died() or false === ( \tsf_extension_manager()->_verify_instance( $_instance, $bits[1] ) or \tsf_extension_manager()->_maybe_die() ) )
	return;

/**
 * Cord extension for The SEO Framework
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
	use \TSF_Extension_Manager\Enclose_Stray_Private,
		\TSF_Extension_Manager\Construct_Master_Once_Interface;

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
				'_default'                => null,
				'_edit'                   => true,
				'_ret'                    => '',
				'_req'                    => false,
				'_type'                   => 'plain_dropdown',
				'_desc'                   => [
					'',
					\__( 'Connect your site with various third-party analytical platforms. Cord takes care of the rest.', 'the-seo-framework-extension-manager' ),
				],
				'_md'                     => true,
				'_dropdown_title_dynamic' => '',
				'_dropdown_title_checked' => '',
				'_fields'                 => [
					'google_analytics' => [
						'_default' => null,
						'_edit'    => true,
						'_ret'     => '',
						'_req'     => false,
						'_type'    => 'plain_multi',
						'_desc'    => [
							\__( 'Google Analytics', 'the-seo-framework-extension-manager' ),
							sprintf(
								/* translators: %s = Tracking ID documentation link. Markdown. */
								\__( 'Start tracking with [Google Analytics](%s) by filling in a Tracking ID.', 'the-seo-framework-extension-manager' ),
								'https://analytics.google.com/analytics/web/'
							),
						],
						'_md'      => true,
						'_fields'  => [
							'tracking_id' => [
								'_default' => null,
								'_edit'    => true,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'text',
								'_pattern' => '\bUA-\d{4,10}-\d{1,4}\b',
								'_desc'    => [
									\__( 'Tracking ID', 'the-seo-framework-extension-manager' ),
									sprintf(
										/* translators: %s = Tracking ID documentation link. Markdown. */
										\__( 'Get your [Tracking ID](%s).', 'the-seo-framework-extension-manager' ),
										'https://support.google.com/analytics/answer/1008080#GAID'
									),
								],
								'_ph'      => 'UA-12345678-9',
								'_md'      => true,
							],
							'enhanced_link_attribution' => [
								'_default' => null,
								'_edit'    => true,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'checkbox',
								'_desc'    => [
									\__( 'Enhanced Link Attribution', 'the-seo-framework-extension-manager' ),
									sprintf(
										/* translators: %s = Tracking ID documentation link. Markdown. */
										\__( 'You can improve the accuracy of links clicked with [Enhanced Link Attribution](%s).', 'the-seo-framework-extension-manager' ),
										'https://support.google.com/analytics/answer/7377126'
									),
									\__( 'Use the "Page Analytics (by Google)" browser extension for Google Chrome to analyze the information collected. Custom theme development may be required to take full advantage of this feature.', 'the-seo-framework-extension-manager' ),
								],
								'_check'   => [
									\__( 'Enable Enhanced Link Attribution?', 'the-seo-framework-extension-manager' ),
								],
								'_md'      => true,
							],
							'ip_anonymization' => [
								'_default' => null,
								'_edit'    => true,
								'_ret'     => 's',
								'_req'     => false,
								'_type'    => 'checkbox',
								'_desc'    => [
									\__( 'IP Anonymization', 'the-seo-framework-extension-manager' ),
									sprintf(
										/* translators: %s = Tracking ID documentation link. Markdown. */
										\__( 'You can protect the identity of your visitors through [IP anonymization](%s).', 'the-seo-framework-extension-manager' ),
										'https://support.google.com/analytics/answer/2763052'
									),
									\__( 'This feature is at the cost of a slight possibility of inconsistent data.', 'the-seo-framework-extension-manager' ),
								],
								'_check'   => [
									\__( 'Enable IP anonymization?', 'the-seo-framework-extension-manager' ),
								],
								'_md'      => true,
							],
						],
					],
					'facebook_pixel' => [
						'_default' => null,
						'_edit'    => true,
						'_ret'     => '',
						'_req'     => false,
						'_type'    => 'plain_multi',
						'_desc'    => [
							\__( 'Facebook Pixel', 'the-seo-framework-extension-manager' ),
							sprintf(
								/* translators: %s = Tracking ID documentation link. Markdown. */
								\__( 'Start tracking with [Facebook Pixel](%s) by filling in a Pixel ID.', 'the-seo-framework-extension-manager' ),
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
									\__( 'Tracking ID', 'the-seo-framework-extension-manager' ),
									sprintf(
										/* translators: %s = Tracking ID documentation link. Markdown. */
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
				'logo'     => [
					'svg' => TSFEM_E_CORD_DIR_URL . 'lib/images/icon.svg',
					'2x'  => TSFEM_E_CORD_DIR_URL . 'lib/images/icon-58x58.png',
					'1x'  => TSFEM_E_CORD_DIR_URL . 'lib/images/icon-29x29px.png',
				],
				'before'   => '',
				'after'    => '',
				'pane'     => [],
				'settings' => $_settings,
				// When we add more panes, we can order them by adding up to 9.9999 to this value.
				'priority' => \tsf_extension_manager()->get_extension_order()['cord'],
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

		if ( ! is_array( $value ) )
			$value = [];

		// Yes, this is needlessly complex. It's built for future expansion...
		$indexes = [ 'google_analytics' ];
		foreach ( $indexes as $index ) :
			switch ( $index ) :
				case 'google_analytics':
					$keys = [
						'tracking_id',
						'enhanced_link_attribution',
						'ip_anonymization',
					];
					foreach ( $keys as $key ) {
						switch ( $key ) {
							case 'tracking_id':
								$value[ $index ][ $key ] = trim( \tsf_extension_manager()->coalesce_var( $value[ $index ][ $key ], '' ) );
								if ( ! preg_match( '/^\bUA-\d{4,10}-\d{1,4}\b$/', $value[ $index ][ $key ] ) ) {
									$value[ $index ][ $key ] = '';
								}
								break;

							case 'enhanced_link_attribution':
							case 'ip_anonymization':
								$value[ $index ][ $key ] = \the_seo_framework()->s_one_zero(
									\tsf_extension_manager()->coalesce_var( $value[ $index ][ $key ], 0 )
								);
								break;

							default:
								break;
						}
					}
					break;

				case 'facebook_pixel':
					$key = 'pixel_id';
					$value[ $index ][ $key ] = trim( \tsf_extension_manager()->coalesce_var( $value[ $index ][ $key ], '' ) );
					if ( ! preg_match( '/^[0-9]+$/', $value[ $index ][ $key ] ) ) {
						$value[ $index ][ $key ] = '';
					}
					break;

				default:
					break;
			endswitch;
		endforeach;

		return $value;
	}
}
