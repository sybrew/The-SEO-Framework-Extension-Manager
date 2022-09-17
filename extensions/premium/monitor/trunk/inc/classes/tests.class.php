<?php
/**
 * @package TSF_Extension_Manager\Extension\Monitor\Tests
 */

namespace TSF_Extension_Manager\Extension\Monitor;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

$tsfem = \tsfem();

if ( $tsfem->_has_died() or false === ( $tsfem->_verify_instance( $_instance, $bits[1] ) or $tsfem->_maybe_die() ) )
	return;

/**
 * Monitor extension for The SEO Framework
 * Copyright (C) 2016-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Monitor\Tests
 *
 * Tests Monitor Data input. With an overuse of goto statements.
 *
 * @since 1.0.0
 * @access private
 * @uses TSF_Extension_Manager\Traits
 */
final class Tests {
	use \TSF_Extension_Manager\Construct_Core_Static_Final_Instance;

	/**
	 * @since 1.0.0
	 * @var \The_SEO_Framework\Load TSF's class instance.
	 */
	private static $tsf;

	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 * @override See Trait \TSF_Extension_Manager\Construct_Core_Static_Final_Instance.
	 */
	private function __construct() {
		static::$tsf = \tsf();
	}

	/**
	 * Handles unapproachable invoked methods.
	 * Silently ignores errors on this call.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return string Empty.
	 */
	public function __call( $name, $arguments ) { // phpcs:ignore, VariableAnalysis.CodeAnalysis.VariableAnalysis
		return '';
	}

	/**
	 * Determines if the Favicon is output.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function issue_favicon( $data ) {

		$content = '';
		$state   = 'unknown';

		if ( ! isset( $data['meta'], $data['static'] ) ) {
			$state   = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$state = 'good';

		if ( empty( $data['meta'] ) ) {
			$content .= $this->wrap_info( \esc_html__( 'You should add a site icon through the customizer to add extra support for mobile devices.', 'the-seo-framework-extension-manager' ) );
			$state    = 'warning';

			if ( empty( $data['static'] ) ) {
				$content .= $this->wrap_info( static::$tsf->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'No `favicon.ico` file was found in the root directory of your website. Web browsers automatically try to call this file; so, you should add one to prevent 404 hits and improve website performance.', 'the-seo-framework-extension-manager' ),
					[ 'code' ]
				) );
			} else {
				$content .= $this->wrap_info( static::$tsf->convert_markdown(
					\esc_html__( 'A `favicon.ico` file was found in the root directory of your website. This is good, because it prevents 404 hits to your website.', 'the-seo-framework-extension-manager' ),
					[ 'code' ]
				) );
			}
		} else {
			$content .= $this->wrap_info( \esc_html__( 'A dynamic favicon has been found, this increases support for mobile devices.', 'the-seo-framework-extension-manager' ) );
		}

		end :;
		return compact( 'content', 'state' );
	}

	/**
	 * Determines if the Title is correctly output.
	 *
	 * @since 1.1.0
	 * @since 1.2.0 Added TSF v3.1 compat.
	 * @access private
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function issue_title( $data ) {

		$state   = 'unknown';
		$content = '';

		if ( ! isset( $data['located'] ) ) {
			$state   = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$state   = 'good';
		$content = '';

		$consult_theme_author = false;

		if ( ! $data['located'] ) {
			$content .= $this->wrap_info( \esc_html__( 'No title tag was found.', 'the-seo-framework-extension-manager' ) );
			$state    = 'error';

			$consult_theme_author = true;
		} elseif ( isset( $data['value'] ) && $data['value'] ) {
			preg_match( '/(?:<title.*?>)(.*)?(?:<\/title>)/is', $data['value'], $matches );
			$first_found_title = isset( $matches[1] ) ? trim( $matches[1] ) : '';

			if ( ! $first_found_title ) {
				$content .= $this->wrap_info( \esc_html__( 'The homepage title tag is empty.', 'the-seo-framework-extension-manager' ) );
				$state    = 'error';

				$consult_theme_author = true;
			} else {

				$_expected_title = static::$tsf->get_title( [ 'id' => static::$tsf->get_the_front_page_ID() ] );

				if ( $_expected_title !== $first_found_title ) {
					$content  = $this->wrap_info( \esc_html__( 'The homepage title is not as expected. You should activate the Title Fix extension.', 'the-seo-framework-extension-manager' ) );
					$content .= $this->wrap_info(
						sprintf(
							static::$tsf->convert_markdown(
								/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
								\esc_html__( 'Found: `%s`', 'the-seo-framework-extension-manager' ),
								[ 'code' ]
							),
							\esc_html( $first_found_title )
						)
					);
					$content .= $this->wrap_info(
						sprintf(
							static::$tsf->convert_markdown(
								/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
								\esc_html__( 'Expected: `%s`', 'the-seo-framework-extension-manager' ),
								[ 'code' ]
							),
							\esc_html( $_expected_title )
						)
					);
					$state = 'bad';
				} else {
					$content = $this->wrap_info( \esc_html__( 'The homepage title is as expected.', 'the-seo-framework-extension-manager' ) );
				}
			}
		}

		if ( isset( $data['count'] ) && $data['count'] > 1 ) {
			$state    = 'bad';
			$content .= $this->wrap_info( sprintf(
				/* translators: %d = the number "2" or greater */
				\esc_html__( '%d title tags are found on the homepage.', 'the-seo-framework-extension-manager' ),
				$data['count']
			) );

			$consult_theme_author = true;
		}

		if ( $consult_theme_author ) {
			$_theme         = \wp_get_theme();
			$_theme_contact = $_theme->get( 'ThemeURI' ) ?: $_theme->get( 'AuthorURI' ) ?: '';
			if ( $_theme_contact ) {
				$_dev = \tsfem()->get_link( [
					'url'     => $_theme_contact,
					'content' => \__( 'theme developer', 'the-seo-framework-extension-manager' ),
					'target'  => '_blank',
				] );
			} else {
				$_dev = \esc_html__( 'theme developer', 'the-seo-framework-extension-manager' );
			}

			$content .= $this->wrap_info( sprintf(
				/* translators: %s = theme developer */
				\esc_html__( 'Please consult with your %s to fix the title.', 'the-seo-framework-extension-manager' ),
				$_dev
			) );
		}

		end :;
		return [
			'content' => $content,
			'state'   => $state,
		];
	}

	/**
	 * Determines if the description is correctly output.
	 *
	 * @since 1.2.1
	 * @access private
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function issue_description( $data ) {

		$content = '';
		$state   = 'unknown';

		if ( ! isset( $data['located'] ) ) {
			$state   = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$state   = 'good';
		$content = '';

		$consult_theme_author_on_duplicate = false;

		if ( ! $data['located'] ) {
			$state   = 'warning';
			$content = $this->wrap_info( \esc_html__( 'No description meta tags are found on the homepage.', 'the-seo-framework-extension-manager' ) );
		} else {
			$content = $this->wrap_info( \esc_html__( 'A description meta tag is found on the homepage.', 'the-seo-framework-extension-manager' ) );
		}

		if ( isset( $data['count'] ) && $data['count'] > 1 ) {
			$state    = 'bad';
			$content .= $this->wrap_info( sprintf(
				/* translators: %d = Always the number "2" or greater */
				\esc_html__( '%d description meta tags are found on the homepage.', 'the-seo-framework-extension-manager' ),
				$data['count']
			) );

			$consult_theme_author_on_duplicate = true;
		}

		if ( $consult_theme_author_on_duplicate ) {
			$_theme         = \wp_get_theme();
			$_theme_contact = $_theme->get( 'ThemeURI' ) ?: $_theme->get( 'AuthorURI' ) ?: '';
			if ( $_theme_contact ) {
				$_dev = \tsfem()->get_link( [
					'url'     => $_theme_contact,
					'content' => \__( 'theme developer', 'the-seo-framework-extension-manager' ),
					'target'  => '_blank',
				] );
			} else {
				$_dev = \esc_html__( 'theme developer', 'the-seo-framework-extension-manager' );
			}

			$content .= $this->wrap_info( sprintf(
				/* translators: %s = theme developer */
				\esc_html__( 'Please consult with your %s to fix the duplicated description meta tags.', 'the-seo-framework-extension-manager' ),
				$_dev
			) );
		}

		end :;
		return [
			'content' => $content,
			'state'   => $state,
		];
	}

	/**
	 * Determines if there are PHP errors detected.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data.
	 */
	public function issue_php( $data ) {

		$content = '';
		$state   = 'unknown';

		if ( ! \is_array( $data ) ) {
			$state   = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		$links = [];

		foreach ( $data as $value ) :
			if ( isset( $value['value'] ) && false === $value['value'] ) :
				$id = isset( $value['post_id'] ) ? (int) $value['post_id'] : false;

				if ( false !== $id ) {
					if ( ! $id ) {
						$id = static::$tsf->get_the_front_page_ID();
					}

					$title = static::$tsf->get_title( [ 'id' => $id ] );
					$url   = static::$tsf->create_canonical_url( [ 'id' => $id ] );

					$links[] = sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', $url, $title );
				}
			endif;
		endforeach;

		// Links are filled in with erroneous pages.
		if ( empty( $links ) ) {
			$state   = 'good';
			$content = $this->wrap_info( $this->no_issue_found() );
		} else {
			$state    = 'bad';
			$content  = $this->wrap_info( \esc_html__( 'Something is causing a PHP error on your website. This prevents correctly closing of HTML tags.', 'the-seo-framework-extension-manager' ) );
			$content .= sprintf( '<h4>%s</h4>', \esc_html( \_n( 'Affected page:', 'Affected pages:', \count( $links ), 'the-seo-framework-extension-manager' ) ) );

			$content .= '<ul class="tsfem-ul-disc">';
			foreach ( $links as $link ) {
				$content .= sprintf( '<li>%s</li>', $link );
			}
			$content .= '</ul>';
		}

		$content .= $this->wrap_info( $this->small_sample_disclaimer() );

		end :;
		return [
			'content' => $content,
			'state'   => $state,
		];
	}

	/**
	 * Determines if the robots.txt file is correctly output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data in HTML.
	 */
	public function issue_robots( $data ) {

		$state   = 'unknown';
		$content = '';

		if ( ! isset( $data['located'] ) )
			goto end;

		if ( ! $data['located'] ) {
			$state   = 'error';
			$content = $this->wrap_info(
				static::$tsf->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'No `robots.txt` file has been found. Please check your server configuration.', 'the-seo-framework-extension-manager' ),
					[ 'code' ]
				)
			);
			goto end;
		}

		if ( ! \get_option( 'blog_public' ) ) {
			$state   = 'bad';
			$content = $this->wrap_info(
				\esc_html__( 'This site is discouraging Search Engines from visiting. This means popular Search Engines are not crawling and indexing your website.', 'the-seo-framework-extension-manager' )
			);
			goto end;
		}

		if ( ! isset( $data['value'] ) ) {
			$state   = 'unknown';
			$content = $this->no_data_found();
			goto end;
		}

		// Cache safe.
		$sample_tsf = static::$tsf->robots_txt();

		// TSF 4.0.5 compat, remove robots.txt warning. This warning cannot be translated, so this is fine... for now.
		// TODO see note at robots_txt() method in The SEO Framework, and adjust this for that.
		$sample_tsf = preg_replace( '/^\#.*?[\r\n]+\#.*?robots\.txt[\r\n]+/', '', $sample_tsf );

		// Normalize.
		$sample_tsf    = \esc_html( str_replace( [ "\r\n", "\r", "\n" ], '', trim( $sample_tsf ) ) );
		$data['value'] = \esc_html( str_replace( [ "\r\n", "\r", "\n" ], '', trim( $data['value'] ) ) );

		if ( $sample_tsf === $data['value'] ) {
			$state   = 'good';
			$content = $this->wrap_info(
				static::$tsf->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'The `robots.txt` file is handled correctly by The SEO Framework.', 'the-seo-framework-extension-manager' ),
					[ 'code' ]
				)
			);
			goto end;
		}

		// phpcs:ignore, TSF.Performance.Functions.PHP -- This is an asserter for issues so there's no other way.
		if ( ! file_exists( \get_home_path() . 'robots.txt' ) ) {
			$state   = 'unknown';
			$content = $this->wrap_info(
				static::$tsf->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'The `robots.txt` file is not handled by The SEO Framework.', 'the-seo-framework-extension-manager' ),
					[ 'code' ]
				)
			);
			goto end;
		}

		static_file : {
			$state   = 'okay';
			$content = $this->wrap_info(
				static::$tsf->convert_markdown(
					/* translators: Backticks are markdown for <code>Text</code>. Keep the backticks. */
					\esc_html__( 'The `robots.txt` file is static or overwritten in another way. Consider deleting the `robots.txt` file from your home directory folder because The SEO Framework handles this appropriately.', 'the-seo-framework-extension-manager' ),
					[ 'code' ]
				)
			);
			// goto end; // Not needed.
		}

		end:;

		return [
			'content' => $content,
			'state'   => $state,
		];
	}

	/**
	 * Determines if the sitemap.xml file is correctly output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data in HTML.
	 */
	public function issue_sitemap( $data ) {

		$state   = 'unknown';
		$content = '';

		if ( ! isset( $data['located'] ) )
			goto end;

		if ( ! $data['located'] ) {
			$state   = 'error';
			$content = $this->wrap_info( \esc_html__( 'No sitemap file could be found. Enable the sitemap or check your server configuration.', 'the-seo-framework-extension-manager' ) );
			goto end;
		}

		$state = 'good';

		// 10 MB, not 10 MiB. Although the real limit is 52428800 (50MiB), large files add weight to the server, and slows down cralwers.
		if ( isset( $data['size'] ) && $data['size'] > 10000000 ) {
			$state    = 'bad';
			$content .= $this->wrap_info( \esc_html__( 'The sitemap file is bigger than 10MB, you should make it smaller.', 'the-seo-framework-extension-manager' ) );
		}

		if ( isset( $data['valid'] ) && ! $data['valid'] ) {
			$state    = 'bad';
			$content .= $this->wrap_info( \esc_html__( 'The sitemap file is found to be invalid. Please request Premium Support if you do not know how to resolve this.', 'the-seo-framework-extension-manager' ) );
		} else {
			$content .= $this->wrap_info( \esc_html__( 'The sitemap file is found and valid.', 'the-seo-framework-extension-manager' ) );

		}

		if ( isset( $data['index'] ) && $data['index'] ) {
			$content .= $this->wrap_info( $this->small_sample_disclaimer() );
		}

		if ( empty( $content ) ) {
			$content = $this->wrap_info( $this->no_issue_found() );
		}

		end :;
		return [
			'content' => $content,
			'state'   => $state,
		];
	}

	/**
	 * Determines if the site is accessible on HTTPS and if the canonical URLs are set.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The input data.
	 * @return string The evaluated data in HTML.
	 */
	public function issue_https( $data ) {

		$state   = 'unknown';
		$content = '';

		$_test_alt = false;

		if ( ! isset( $data['https_type'] ) || $data['https_type'] < 0 || $data['https_type'] > 3 ) {
			$content = $this->no_data_found();
			goto end;
		}

		switch ( $data['https_type'] ) :
			case 1:
				// Forced HTTPS
				$content         .= $this->wrap_info(
					\esc_html__( 'Your website forces HTTPS through the server configuration. That is great!', 'the-seo-framework-extension-manager' )
				);
				$state            = 'good';
				$_expected_scheme = 'https';
				break;

			case 2:
				// Could do HTTPS
				$content         .= $this->wrap_info(
					\esc_html__( 'Your website is accessible on both HTTPS and HTTP.', 'the-seo-framework-extension-manager' )
				);
				$state            = 'good';
				$_test_alt        = true;
				$_expected_scheme = 'https';
				break;

			default:
			case 3:
			case 0:
				// Forced HTTP or error on HTTPS.
				$content         .= $this->wrap_info(
					\esc_html__( 'Your website is only accessible on HTTP.', 'the-seo-framework-extension-manager' )
				);
				$state            = 'warning';
				$_expected_scheme = 'http';
				break;
		endswitch;

		if ( empty( $data['canonical_url'] ) ) :
			$state    = 'warning';
			$content .= $this->wrap_info(
				\esc_html__( 'No canonical URL is found.', 'the-seo-framework-extension-manager' )
			);
		elseif ( ! empty( $data['canonical_url_scheme'] ) ) :
			if ( $_test_alt ) :
				if ( ! empty( $data['canonical_url_scheme_alt'] ) ) :
					if ( $data['canonical_url_scheme'] === $data['canonical_url_scheme_alt'] ) :
						if ( $_expected_scheme === $data['canonical_url_scheme'] ) {
							$state    = 'good';
							$content .= $this->wrap_info(
								\esc_html__( 'Both versions of your site point to the secure version. Great!', 'the-seo-framework-extension-manager' )
							);
						} else {
							$state    = 'warning';
							$content .= $this->wrap_info(
								\esc_html__( 'Both versions of your site point to the insecure version. Is this intended?', 'the-seo-framework-extension-manager' )
							);
						}
					else :
						$state = 'bad';
						// Cache safe.
						\wp_doing_ajax() and static::$tsf->add_menu_link();
						$content .= $this->wrap_info(
							static::$tsf->convert_markdown(
								sprintf(
									/* translators: URLs are in markdown. %s = SEO Settings page admin URL. */
									\esc_html__( 'The canonical URL scheme is automatically determined. Set the preferred scheme to either HTTP or HTTPS in the [General SEO settings](%s).', 'the-seo-framework-extension-manager' ),
									\esc_url( static::$tsf->get_admin_page_url( static::$tsf->seo_settings_page_slug ), [ 'https', 'http' ] )
								),
								[ 'a' ]
							)
						);
					endif;
				else :
					$state    = 'bad';
					$content .= $this->wrap_info(
						\esc_html__( 'No canonical URL is found on the HTTPS version of your site.', 'the-seo-framework-extension-manager' )
					);
				endif;
			else :
				if ( $_expected_scheme === $data['canonical_url_scheme'] ) {
					//= Don't change state.
					$content .= $this->wrap_info(
						\esc_html__( 'The canonical URL scheme matches the expected scheme.', 'the-seo-framework-extension-manager' )
					);
				} else {
					$state = 'bad';
					// Cache safe.
					\wp_doing_ajax() and static::$tsf->add_menu_link();
					$content .= $this->wrap_info(
						static::$tsf->convert_markdown(
							sprintf(
								/* translators: URLs are in markdown. %s = SEO Settings page admin URL. */
								\esc_html__( 'The canonical URL scheme is set incorrectly. Set the preferred scheme to be detected automatically in the [General SEO settings](%s).', 'the-seo-framework-extension-manager' ),
								\esc_url( \tsfem()->get_admin_page_url( static::$tsf->seo_settings_page_slug ), [ 'https', 'http' ] )
							),
							[ 'a' ]
						)
					);
				}
			endif;
		else :
			$state    = 'bad';
			$content .= $this->wrap_info(
				\esc_html__( 'The canonical URL does not seem to have a set scheme; so, the URL is invalid. The active theme, another plugin or an external service might be interfering.', 'the-seo-framework-extension-manager' )
			);
		endif;

		if ( empty( $content ) )
			$content = $this->wrap_info( $this->no_issue_found() );

		end :;
		return [
			'content' => $content,
			'state'   => $state,
		];
	}

	/**
	 * Returns more coming soon information with unknown state.
	 *
	 * @since 1.0.0
	 *
	 * @return string The information string in HTML.
	 */
	public function issue_moresoon() {
		return [
			'content' => $this->wrap_info( \esc_html__( 'More issue tests are coming soon!', 'the-seo-framework-extension-manager' ) ),
			'state'   => 'unknown',
		];
	}

	/**
	 * Wraps text into an HTML info wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Escaped input text.
	 * @return string The HTML wrapped information text.
	 */
	protected function wrap_info( $text ) {
		return sprintf( '<p class="tsfem-e-monitor-info">%s</p>', $text );
	}

	/**
	 * Returns translatable string wrapped in HTML for when no issues are found.
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML wrapped no issues found.
	 */
	protected function no_issue_found() {
		static $cache;
		return $cache ?: $cache = sprintf(
			'<span class="tsfem-description">%s</span>',
			\esc_html__( 'No issues have been found.', 'the-seo-framework-extension-manager' )
		);
	}

	/**
	 * Returns translatable string wrapped in HTML for when no data is found.
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML wrapped no data found.
	 */
	protected function no_data_found() {
		static $cache;
		return $cache ?: $cache = sprintf(
			'<span class="tsfem-description">%s</span>',
			\esc_html__( 'No data has been found on this issue.', 'the-seo-framework-extension-manager' )
		);
	}

	/**
	 * Returns translatable string wrapped in HTML for when a small sample size has been used.
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML wrapped small sample size used.
	 */
	protected function small_sample_disclaimer() {
		static $cache;
		return $cache ?: $cache = sprintf(
			'<span class="tsfem-description">%s</span>',
			\esc_html__( 'This has been evaluated with a small sample size.', 'the-seo-framework-extension-manager' )
		);
	}
}
