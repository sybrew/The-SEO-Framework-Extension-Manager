<?php
/**
 * @package TSF_Extension_Manager\Classes
 */
namespace TSF_Extension_Manager;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Panes
 *
 * Holds plugin activation functions.
 *
 * @since 1.0.0
 */
class Panes extends Core {

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	private function __wakeup() { }

	/**
	 * Constructor. Loads parent constructor and initializes actions.
	 */
	protected function __construct() {
		parent::__construct();

		//* Ajax listener for updating feed option.
		add_action( 'wp_ajax_tsfem_enable_feeds', array( $this, 'wp_ajax_enable_feeds' ) );
	}

	/**
	 * Parses Google Feed.
	 * This is a prototype. It's planned to fetch from premium.theseoframework.com
	 * With a filtered list that's parsed remotely, which has a history and is loaded from more or personalized sources.
	 *
	 * @since 1.0.0
	 *
	 * @return string The filtered Google Webmasters feed output.
	 */
	protected function prototype_get_google_webmasters_feed() {

		if ( ! function_exists( 'simplexml_load_string' ) )
			return -1;

		$transient_name = 'latest-seo-feed-transient';
		$output = get_transient( $transient_name );

		if ( false === $output ) {
			//* Google Webmasters official blog link.
			$feed_url = 'https://www.blogger.com/feeds/32069983/posts/default';

			$http_args = array(
				'timeout' => 7,
				'httpversion' => apply_filters( 'tsf_extension_manager_http_request_version', '1.1' ),
			);

			$request = wp_safe_remote_get( $feed_url, $http_args );

			if ( 200 !== (int) wp_remote_retrieve_response_code( $request ) )
				return '';

			$xml = wp_remote_retrieve_body( $request );
			$options = LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_NSCLEAN;
			$xml = simplexml_load_string( $xml, 'SimpleXMLElement', $options );

			if ( ! isset( $xml->entry ) || empty( $xml->entry ) ) {
				set_transient( $transient_name, '', DAY_IN_SECONDS );
				return '';
			}

			$entry = $xml->entry;
			unset( $xml );

			$output = '';

			$max = 20;
			$i = 0;
			foreach ( $entry as $object ) {

				//* For loop? @todo
				if ( $i >= $max )
					break;

				if ( ! isset( $object->category ) || ! is_object( $object->category ) )
					continue;

				$found = false;
				//* Filter terms.
				foreach ( $object->category as $category ) {
					$term = isset( $category->{0}['term'] ) ? $category->{0}['term']->__toString() : '';
					if ( ! in_array( $term, array( 'search results', 'crawling and indexing', 'general tips' ), true ) ) {
						$found = true;
						break;
					}
					continue;
				}
				unset( $category );
				if ( false === $found )
					continue;

				//* Fetch link.
				$link = '';
				foreach ( $object->link as $link_object ) {

					$type = isset( $link_object->{0}['type'] ) ? $link_object->{0}['type']->__toString() : '';
					if ( 'text/html' === $type ) {

						$rel = isset( $link_object->{0}['rel'] ) ? $link_object->{0}['rel']->__toString() : '';
						if ( 'replies' === $rel ) {

							$link = isset( $link_object->{0}['href'] ) ? $link_object->{0}['href']->__toString() : '';

							if ( $link )
								$link = strtok( $link, '#' );

							break;
						}
					}
				}
				unset( $link_object );

				// $object->updated also exists.
				$date = isset( $object->published ) ? $object->published->__toString() : '';
				$date = $date ? '<time>' . date_i18n( get_option( 'date_format' ), strtotime( $date ) ) . '</time>' : '';

				$title = isset( $object->title ) ? $object->title->__toString() : '';
				$title = $title ? the_seo_framework()->escape_title( $title ) : '';

				$content = isset( $object->content ) ? $object->content->__toString() : '';
				$content = $content ? wp_strip_all_tags( $content ) : '';
				unset( $object );

				//* Do not care for the current length. Always trim.
				$content = the_seo_framework()->trim_excerpt( $content, 251, 250 );
				$content = the_seo_framework()->escape_description( $content );

				if ( $link ) {
					//* No need for translations, it's English only.
					$title = sprintf( '<h4><a href="%s" target="_blank" rel="external nofollow" title="Read more...">%s</a></h4>', esc_url( $link ), $title );
				}

				$output .= sprintf( '<div class="tsfem-feed-entry"><div class="tsfem-feed-top">%s%s</div><div class="tsfem-feed-content">%s</div></div>', $title, $date, $content );
				$i++;
			}

			set_transient( $transient_name, $output, DAY_IN_SECONDS );
		}

		return $output;
	}

	/**
	 * Returns a button that implements an AJAX request for Feed enabling.
	 *
	 * @since 1.0.0
	 *
	 * @return string.
	 */
	protected function get_feed_enabler_button() {

		$enable = __( 'Enable feed?', 'the-seo-framework-extension-manager' );

		$key = sprintf( '<input type="hidden" name="%s" value="validate-key">', $this->get_field_name( 'action' ) );
		$nonce = wp_nonce_field( $this->nonce_action['enable-feed'], $this->nonce_name, true, false );
		$submit = sprintf( '<input type="submit" name="submit" id="submit" class="button-primary" value="%s">', esc_attr( $submit_class ), esc_attr( $enable ) );

		$form = $key . $nonce . $submit;
		$nojs = sprintf( '<form action="%s" method="post" id="tsfem-enable-feeds-form" class="hide-if-js">%s</form>', esc_url( $this->get_admin_page_url() ), $form );

		$js = '<a id="tsfem-enable-feeds" class="button-primary hide-if-no-js">' . esc_html( $enable ) . '</a>';

		return $js . $nojs;
	}

	/**
	 * Enables feed and sends back
	 *
	 * @since 1.0.0
	 * @access private
	 */
	public function wp_ajax_enable_feeds() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( $this->can_do_settings() ) {

				check_ajax_referer( 'tsfem-ajax-nonce', 'nonce' );

				if ( $this->get_option( '_enable_feed' ) )
					exit;

				$type = $this->update_option( '_enable_feed', true ) ? 'success' : 'error';

				if ( 'success' === $type ) {
					$results = array(
						'content' => $this->get_seo_trends_and_updates_overview( true ),
						'type' => $type,
					);
				} else {
					$results = array(
						'content' => '',
						'type' => $type,
					);
				}

				echo json_encode( $results );

				exit;
			}
		}
	}

	/**
	 * Returns the SEO trends and updates overview.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $ajax Whether the call is from an AJAX request.
	 * @return string The escaped SEO Trends and Updates overview.
	 */
	protected function get_seo_trends_and_updates_overview( $ajax = false ) {

		$output = '';

	//	$feed_enabled = $this->get_option( '_enable_feed', false );
		$this->update_option( '_enable_feed', false );
		$feed_enabled = false;

		if ( $feed_enabled || $ajax ) {
			$feed = $this->prototype_get_google_webmasters_feed();

			if ( -1 === $feed ) {
				$feed_error = esc_html__( "Unfortunately, your server can't process this request as of yet.", 'the-seo-framework-extension-manager' );
				$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
			} elseif ( empty( $feed ) ) {
				$feed_error = esc_html__( 'There are no trends and updates to report yet.', 'the-seo-framework-extension-manager' );
				$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $feed_error );
			} else {
				$output .= sprintf( '<div class="tsfem-feed-wrap">%s</div>', $feed );
			}
		} else {
			//* The feed is totally optional until it pulls from The SEO Framework premium. I.e. privacy.
			$info = esc_html__( 'The feed has been disabled to protect your privacy.', 'the-seo-framework-extension-manager' );
			$output .= sprintf( '<h4 class="tsfem-status-title">%s</h4>', $info );
			$output .= '<p>' . esc_html__( 'You may choose to enable the feed. Once enabled, it can not be disabled.', 'the-seo-framework-title-fix' ) . '</p>';
			$output .= $this->get_feed_enabler_button();
		}

		//* The AJAX output is already wrapped.
		if ( $ajax )
			return $output;

		return sprintf( '<div class="tsfem-trends-wrap">%s</div>', $output );
	}

	/**
	 * Returns TSF Extension Manager account actions overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The escaped account actions overview.
	 */
	protected function get_extensions_actions_overview() {

		$output = '';

		return sprintf( '<div class="tsfem-actions-wrap">%s</div>', $output );
	}

	/**
	 * Returns the extension overview.
	 *
	 * @since 1.0.0
	 *
	 * @return string The extensions overview.
	 */
	protected function get_extension_overview() {

		$header = __( 'Extensions', 'the-seo-framework-extension-manager' );
		$output = '';

		return $output;
	}
}
