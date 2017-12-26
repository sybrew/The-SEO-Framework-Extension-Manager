<?php
/**
 * @package TSF_Extension_Manager\Traits\Functions
 */
namespace TSF_Extension_Manager;

defined( 'ABSPATH' ) or die;

/**
 * The SEO Framework - Extension Manager plugin
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
		$hour = $minute * 60;
		$day = $hour * 24;
		$week = $day * 7;

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
		$year = $week * 52;

		//= A more accurate representation. It annotates the beginning of X.
		//* e.g. last week can be up to 13 days ago; last month 60 days ago, etc.
		$last_week = strtotime( 'last week', $now );
		$last_month = strtotime( 'last month', $now );
		$last_year = strtotime( 'last year', $now ); //= '12 months ago'

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
	 * Returns a rectified GMT date by calculating the site's timezone into the
	 * inserted timestamp.
	 *
	 * @since 1.5.0
	 *
	 * @param string   $format    The Datetime format.
	 * @param int|null $timestamp The UNIX timestamp. When null it uses time().
	 * @return string The formatted GMT date including timezone offset.
	 */
	protected function get_rectified_date( $format, $timestamp = null ) {

		is_null( $timestamp )
			and $timestamp = time();

		$offset = \get_option( 'gmt_offset' );
		$seconds = round( $offset * HOUR_IN_SECONDS );

		return gmdate( $format, $timestamp + $seconds );
	}

	/**
	 * Returns a rectified translated date by shifting the PHP's timezone to the
	 * site's settings.
	 *
	 * @since 1.5.0
	 *
	 * @param string   $format    The Datetime format.
	 * @param int|false $timestamp The UNIX timestamp. When false it uses time().
	 * @return string The formatted i18n date.
	 */
	protected function get_rectified_date_i18n( $format, $timestamp = false ) {

		$this->set_timezone();
		$out = \date_i18n( $format, $timestamp );
		$this->reset_timezone();

		return $out;
	}

	/**
	 * Sets and resets the timezone.
	 *
	 * @since 1.5.0
	 * @source The SEO Framework 3.0
	 *
	 * @param string $tzstring Optional. The PHP Timezone string. Best to leave empty to always get a correct one.
	 * @link http://php.net/manual/en/timezones.php
	 * @param bool $reset Whether to reset to default. Ignoring first parameter.
	 * @return bool True on success. False on failure.
	 */
	protected function set_timezone( $tzstring = '', $reset = false ) {

		static $old_tz = null;

		if ( is_null( $old_tz ) ) {
			$old_tz = date_default_timezone_get();
			if ( empty( $old_tz ) )
				$old_tz = 'UTC';
		}

		if ( $reset )
			return date_default_timezone_set( $old_tz );

		if ( empty( $tzstring ) )
			$tzstring = $this->get_timezone_string( true );

		return date_default_timezone_set( $tzstring );
	}

	/**
	 * Resets the timezone to default or UTC.
	 *
	 * @since 1.5.0
	 * @source The SEO Framework 3.0
	 *
	 * @return bool True on success. False on failure.
	 */
	protected function reset_timezone() {
		return $this->set_timezone( '', true );
	}

	/**
	 * Returns timestamp format based on TSF's timestamp settings.
	 *
	 * @since 1.5.0
	 * @staticvar string $format
	 * @requires TSF 3.0+
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
			$offset = \get_option( 'gmt_offset' );
			$tzstring = $this->get_tzstring_from_offset( $offset );
		}

		return $tzstring;
	}

	/**
	 * Fetches the Timezone String from given offset.
	 *
	 * @since 1.5.0
	 * @source The SEO Framework 3.0.
	 *         Modified as we require PHP>5.5.10. See <https://bugs.php.net/bug.php?id=44780>
	 *
	 * @param int $offset The GMT offzet.
	 * @return string PHP Timezone String.
	 */
	private function get_tzstring_from_offset( $offset = 0 ) {
		$seconds = round( $offset * HOUR_IN_SECONDS );
		return timezone_name_from_abbr( '', $seconds, 1 );
	}
}
