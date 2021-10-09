<?php
/**
 * @package TSF_Extension_Manager\Traits\Factory
 */

namespace TSF_Extension_Manager;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
 * Copyright (C) 2017-2021 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Holds timing methods.
 *
 * @since 1.5.0
 * @access private
 */
trait Time {

	/**
	 * Returns i18n time relative to now from since.
	 *
	 * @since 1.5.0
	 * @since 1.6.0 Now correctly denotes time.
	 *
	 * @param int $since The UNIX timestamp in the past.
	 * @return string The time ago.
	 */
	protected function get_time_ago_i18n( $since ) {

		$now = time();
		$ago = $now - $since;

		$ago_i18n = '';

		if ( $ago < 0 || $ago > $now ) {
			//= $since is in the future. Or, $since is before recorded time itself.
			$ago_i18n = \__( 'Invalid time. Is your server clock OK?', 'the-seo-framework-extension-manager' );
			goto ret;
		}

		$minute = 60;
		$hour   = $minute * 60;
		$day    = $hour * 24;
		$week   = $day * 7;

		if ( $ago < $minute ) {
			$ago_i18n = \__( 'Just now', 'the-seo-framework-extension-manager' );
		} elseif ( $ago < $hour ) {
			$x = round( $ago / $minute );
			/* translators: %d = minutes */
			$ago_i18n = sprintf( \_n( '%d minute ago', '%d minutes ago', $x, 'the-seo-framework-extension-manager' ), $x );
		} elseif ( $ago < $day ) {
			$x = round( $ago / $hour );
			/* translators: %d = hours */
			$ago_i18n = sprintf( \_n( '%d hour ago', '%d hours ago', $x, 'the-seo-framework-extension-manager' ), $x );
		} elseif ( $ago < $week ) {
			$x = round( $ago / $day );
			/* translators: %d = days */
			$ago_i18n = sprintf( \_n( '%d day ago', '%d days ago', $x, 'the-seo-framework-extension-manager' ), $x );
		}

		if ( $ago_i18n )
			goto ret;

		$month = $week * 4;
		$year  = $week * 52;

		//= A more accurate representation. It annotates the beginning of X.
		// e.g. last week can be up to 13 days ago; last month 60 days ago, etc.
		$last_week  = $now - strtotime( 'last week', $now );
		$last_month = $now - strtotime( 'last month', $now );
		$last_year  = $now - strtotime( 'last year', $now ); //= '12 months ago'

		if ( $ago < $last_week ) {
			$ago_i18n = \__( 'Last week', 'the-seo-framework-extension-manager' );
		} elseif ( $ago < $month ) {
			$x = round( $ago / $week );
			/* translators: %d = weeks */
			$ago_i18n = sprintf( \_n( '%d week ago', '%d weeks ago', $x, 'the-seo-framework-extension-manager' ), $x );
		} elseif ( $ago < $last_month ) {
			$ago_i18n = \__( 'Last month', 'the-seo-framework-extension-manager' );
		} elseif ( $ago < $year ) {
			$x = round( $ago / $month );
			/* translators: %d = months */
			$ago_i18n = sprintf( \_n( '%d month ago', '%d months ago', $x, 'the-seo-framework-extension-manager' ), $x );
		} elseif ( $ago < $last_year ) {
			$ago_i18n = \__( 'Last year', 'the-seo-framework-extension-manager' );
		} else {
			$x = round( $ago / $year );
			/* translators: %d = months */
			$ago_i18n = sprintf( \_n( '%d year ago', '%d years ago', $x, 'the-seo-framework-extension-manager' ), $x );
		}

		ret :;
		return $ago_i18n;
	}

	/**
	 * Returns a rectified translated date by shifting the PHP's timezone to the
	 * site's settings.
	 *
	 * @since 1.5.0
	 * @since 2.2.1 Now enforces datetime via UTC offset, to accomodate for WP 5.3 changes to date_i18n().
	 *
	 * @param string   $format    The Datetime format.
	 * @param int|null $timestamp The UNIX timestamp. When null it uses time().
	 * @return string The formatted i18n date.
	 */
	protected function get_rectified_date_i18n( $format, $timestamp = null ) {

		\is_null( $timestamp )
			and $timestamp = time();

		$offset = round( \get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		// date_i18n() expects an offset, whereas wp_date (WP5.3+) expects a timezone.
		$date = \date_i18n( $format, $timestamp + $offset );

		return $date;
	}

	/**
	 * Scales time according to input.
	 *
	 * @since 1.5.0
	 * @uses $this->_upscale_time()
	 *
	 * @param int <R> $x       The time to convert.
	 * @param string  $x_scale The time scale $x is in.
	 * @param int     $scales  When $precise is true:
	 *                          How often to upscale the time when it's passing a
	 *                          conventional threshold times its value.
	 *                         When $precise is false:
	 *                          The number of time iterations shown.
	 * @param bool    $precise When true, the output maintains the exact offset value.
	 *                         So at scale 2, "3666" seconds won't become "1 hour and 1 minute",
	 *                         but instead it will return "61 minutes and 6 seconds".
	 * @return string Scaled i18n time. Not escaped.
	 */
	protected function scale_time( $x, $x_scale = 'seconds', $scales = 2, $precise = false ) {

		$x = round( $x );

		//= Can't upscale 0.
		if ( $scales && $x )
			return $this->_upscale_time( $x, $x_scale, $scales, $precise );

		$time_i18n = '';

		switch ( $x_scale ) :
			case 'seconds':
				/* translators: %d = seconds */
				$time_i18n = sprintf( \_n( '%d second', '%d seconds', $x, 'the-seo-framework-extension-manager' ), $x );
				break;

			case 'minutes':
				/* translators: %d = minutes */
				$time_i18n = sprintf( \_n( '%d minute', '%d minutes', $x, 'the-seo-framework-extension-manager' ), $x );
				break;

			case 'hours':
				/* translators: %d = hours */
				$time_i18n = sprintf( \_n( '%d hour', '%d hours', $x, 'the-seo-framework-extension-manager' ), $x );
				break;

			case 'days':
				/* translators: %d = days */
				$time_i18n = sprintf( \_n( '%d day', '%d days', $x, 'the-seo-framework-extension-manager' ), $x );
				break;

			case 'weeks':
				/* translators: %d = weeks */
				$time_i18n = sprintf( \_n( '%d week', '%d weeks', $x, 'the-seo-framework-extension-manager' ), $x );
				break;
		endswitch;

		return $time_i18n;
	}

	/**
	 * Upscales time, reiterates over itself until it's happy.
	 *
	 * This is a helper function for $this->scale_time().
	 * Don't call this.
	 *
	 * @since 1.5.0
	 * @access private
	 * @see $this->scale_time()
	 *
	 * @param int <R> $x       The time to convert.
	 * @param string  $x_scale The time scale $x is in.
	 * @param int     $scales  When $precise is true:
	 *                          How often to upscale the time when it's passing a
	 *                          conventional threshold times its value.
	 *                         When $precise is false:
	 *                          The number of time iterations shown.
	 * @param bool    $precise When true, the output maintains the exact offset value.
	 *                         So at scale 2, "3666" seconds won't become "1 hour and 1 minute",
	 *                         but instead it will return "61 minutes and 6 seconds".
	 * @return string Scaled i18n time. Not escaped.
	 */
	protected function _upscale_time( $x, $x_scale, $scales, $precise ) {

		$x_remaining = $x;
		$times       = [];

		//= type => [ threshold_for_next, next ];
		$scale_table = [
			'seconds' => [ 60, 'minutes' ],
			'minutes' => [ 60, 'hours' ],
			'hours'   => [ 24, 'days' ],
			'days'    => [ 7, 'weeks' ],
			'weeks'   => [ PHP_INT_MAX, 'eternity' ],
			// Months and years are too variable for the static purpose of this method.
		];

		while ( $x_remaining ) :
			$_threshold = $scale_table[ $x_scale ][0];

			if ( $x_remaining >= $_threshold                       // > vs >= is 24 hours vs 1 day.
			&& ( ! $precise || ( \count( $times ) < $scales - 1 ) ) // -1 as we're adding another to reach this.
			) {
				if ( $x_remaining % $_threshold ) {
					// Calculate current and next time scale.
					$_next_time    = floor( $x_remaining / $_threshold );
					$_current_time = round( $x_remaining - $_next_time * $_threshold );

					// Found leftovers, use them.
					$times[] = $this->scale_time( $_current_time, $x_scale, 0 );

					$x_remaining = $_next_time;
					$x_scale     = $scale_table[ $x_scale ][1];
				} else {
					//= Rescale up.
					$x_remaining = round( $x_remaining / $_threshold );
					$x_scale     = $scale_table[ $x_scale ][1];
				}
			} else {
				//= Reached threshold through precision or time overlap.
				$times[] = $this->scale_time( $x_remaining, $x_scale, 0 );
				// No need to try upcoming scales, save processing power.
				break;
			}
		endwhile;

		$out = '';

		$times = array_reverse( $times );
		//= Don't return more items than the threshold.
		$count = min( \count( $times ), $scales );

		for ( $i = 0; $i < $count; $i++ ) {
			if ( 0 === $i ) {
				$out .= $times[ $i ];
			} elseif ( $i === $count - 1 ) {
				$out = sprintf(
					/* translators: 1: Greater time, 2: Smaller time */
					\_x( '%1$s and %2$s', '5 minutes and 3 seconds', 'the-seo-framework-extension-manager' ),
					$out,
					$times[ $i ]
				);
			} else {
				$out = sprintf(
					/* translators: 1: Greater time, 2: Smaller time */
					\_x( '%1$s, %2$s', '7 hours, 8 minutes [and...]', 'the-seo-framework-extension-manager' ),
					$out,
					$times[ $i ]
				);
			}
		}
		return $out;
	}

	/**
	 * Returns timestamp format based on TSF's timestamp settings.
	 *
	 * @since 1.5.0
	 *
	 * @return string The timestamp format used for PHP date.
	 */
	protected function get_timestamp_format() {
		static $format;
		return $format ?: $format = \the_seo_framework()->get_timestamp_format();
	}

	/**
	 * Returns the PHP timezone compatible string.
	 * UTC offsets are unreliable.
	 *
	 * @since 1.5.0
	 * @source The SEO Framework 3.0
	 *
	 * @param bool $guess : If true, the timezone will be guessed from the
	 * WordPress core gmt_offset option.
	 * @return string PHP Timezone String.
	 */
	private function get_timezone_string( $guess = false ) {

		$tzstring = \get_option( 'timezone_string' );

		if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
			$tzstring = '';

		if ( $guess && empty( $tzstring ) ) {
			$tzstring = $this->get_tzstring_from_offset( \get_option( 'gmt_offset' ) );
		}

		return $tzstring;
	}

	/**
	 * Fetches the Timezone String from given offset.
	 *
	 * @since 1.5.0
	 * @source The SEO Framework 4.0.
	 *
	 * @param int $offset The GMT offzet.
	 * @return string PHP Timezone String.
	 */
	private function get_tzstring_from_offset( $offset = 0 ) {
		$seconds = round( $offset * HOUR_IN_SECONDS );
		return timezone_name_from_abbr( '', $seconds, 1 );
	}
}
