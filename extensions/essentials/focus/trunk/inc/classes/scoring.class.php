<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Classes
 */

namespace TSF_Extension_Manager\Extension\Focus;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

$tsfem = \tsfem();

if ( $tsfem->_has_died() or false === ( $tsfem->_verify_instance( $_instance, $bits[1] ) or $tsfem->_maybe_die() ) )
	return;

/**
 * Focus extension for The SEO Framework
 * Copyright (C) 2018-2022 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class TSF_Extension_Manager\Extension\Focus\Scoring
 *
 * @since 1.0.0
 * @uses TSF_Extension_Manager\Traits
 * @implements SPL \Iterator
 * @final
 */
final class Scoring {
	use \TSF_Extension_Manager\Construct_Core_Static_Final_Instance;

	/**
	 * @since 1.0.0
	 * @var array The scoring template to iterate over.
	 */
	public $template = [];

	/**
	 * @since 1.0.0
	 * @var string The current ID prefix key.
	 */
	public $key;

	/**
	 * @since 1.0.0
	 * @var array The current score values.
	 */
	public $values;

	/**
	 * Not utilized.
	 * Can be summed from $this->values[x]['endScore'] (not stored yet)
	 * Can be summed from $this->template[x]['maxScore']-
	 *
	 * @since 1.0.0
	 * @ignore
	 * @var int The current score.
	 */
	public $current_score = 0;

	/**
	 * Not utilized.
	 * Can be summed from $this->values[x]['endScore'] (not stored yet)
	 * Can be summed from $this->template[x]['maxScore']-
	 *
	 * @since 1.0.0
	 * @ignore
	 * @var int The maximum score.
	 */
	public $max_score = 0;

	/**
	 * Returns complete template, or part thereof.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The template type part. Optional.
	 * @return array
	 */
	public function get_template( $type = '' ) {
		return $type ? $this->template[ $type ] : $this->template;
	}

	/**
	 * Returns the scoring DOM ID.
	 *
	 * @param string $type The scoring type.
	 * @return string
	 */
	public function get_id( $type ) {
		return sprintf( '%s[%s]', $this->key, $type );
	}

	/**
	 * Trims the assessment's score format.
	 *
	 * @param int|float|string $score The assessment's value.
	 * @return float $score with 4 decimals.
	 */
	public function trim_score_format( $score ) {
		return number_format( $score, 4 );
	}

	/**
	 * Sanitizes the assessment's score to a two float point.
	 *
	 * @param int|float|string $score The score value.
	 * @return string The assessment's score value.
	 */
	public function sanitize( $score ) {
		return (string) ( rtrim( rtrim( sprintf( '%.2F', (float) $score ), '0' ), '.' ) ?: 0 );
	}

	/**
	 * Returns score value for type.
	 *
	 * @param string $type The assessment type.
	 * @return string The score value.
	 */
	public function get_value( $type ) {
		return $this->sanitize( $this->values[ $type ] ?? 0 );
	}

	/**
	 * Returns score title for type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The assessment's scoring type.
	 * @return string The assessment's description.
	 */
	public function get_title( $type ) {
		return $this->get_template( $type )['title'];
	}

	/**
	 * Returns score phrasing for type based on current score.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The assessment's scoring type.
	 * @return string The assessment's description.
	 */
	public function get_description( $type ) {
		return $this->get_nearest_numeric_index_value(
			$this->get_template( $type )['phrasing'],
			$this->get_value( $type )
		);
	}

	/**
	 * Returns icon class for the assessment type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The assessment's scoring type.
	 * @return string The assessment's icon class.
	 */
	public function get_icon_class( $type ) {

		$index = $this->get_nearest_numeric_index_value( $this->get_template( $type )['rating'], $this->get_value( $type ) );

		$classes = [
			-1 => 'tsfem-e-inpost-icon-error', // reserved, unused.
			0  => 'tsfem-e-inpost-icon-unknown',
			1  => 'tsfem-e-inpost-icon-bad',
			2  => 'tsfem-e-inpost-icon-warning',
			3  => 'tsfem-e-inpost-icon-okay',
			4  => 'tsfem-e-inpost-icon-good',
		];

		return $classes[ $index ] ?? $classes[0];
	}

	/**
	 * Finds the nearest index value of the array.
	 * When the value isn't found, it returns the first index value.
	 *
	 * @since 1.0.0
	 *
	 * @param array $a     The array with values. : {
	 *   int index => mixed value
	 * }
	 * @param int   $value The value to find nearest index of.
	 * @return mixed The nearest index value.
	 */
	public function get_nearest_numeric_index_value( array $a, $value ) {

		ksort( $a, SORT_NUMERIC );

		foreach ( $a as $k => $v ) {
			if ( is_numeric( $k ) ) {
				if ( $k <= $value ) {
					$ret = $v;
				} else {
					break;
				}
			}
		}

		return $ret ?? array_values( $a )[0];
	}

	/**
	 * Returns the scoring data attributes.
	 *
	 * @param string $type The assessment's scoring type.
	 * @return string The data attributes.
	 */
	public function get_data_attributes( $type ) {
		return \The_SEO_Framework\Interpreters\HTML::make_data_attributes( [
			'scores'         => \tsfem()->filter_keys(
				$this->get_template( $type ),
				[ 'assessment', 'maxScore', 'minScore', 'phrasing', 'rating', 'scoring' ]
			),
			'assessmentType' => $type,
		] );
	}
}

// phpcs:disable -- WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned, oh boy, this is a lot.

// Registers template.
// To register more templates, simply call the instance and merge arrays before output.
Scoring::get_instance()->template = [
	'seoTitle' => [
		'title' => \esc_html__( 'Meta title:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'seoTitle',
			'regex' => '/{{kw}}/gi',
		],
		'maxScore' => 200,
		'minScore' => 0,
		'phrasing' => [
			200 => \esc_html__( 'The subject is found in the meta title, this is good.', 'the-seo-framework-extension-manager' ),
			50  => \esc_html__( 'A synonym is found in the meta title, consider using an inflection instead.', 'the-seo-framework-extension-manager' ),
			0   => \esc_html__( 'The subject is not found in the meta title, you should add it.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			200 => 4,
			100 => 3,
			50  => 2,
			0   => 1,
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 200,
				'per' => 1,
				'max' => 1,
			],
			'synonym' => [
				'score' => 50,
				'per' => 1,
				'max' => 2,
			],
		],
	],
	'pageTitle' => [
		'title' => \esc_html__( 'Page title:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageTitle',
			'regex' => '/{{kw}}/gi',
		],
		'maxScore' => 150,
		'minScore' => 0,
		'phrasing' => [
			150 => \esc_html__( 'The subject is found in the page title, this is good.', 'the-seo-framework-extension-manager' ),
			66  => \esc_html__( 'A synonym is found in the page title, consider using an inflection instead.', 'the-seo-framework-extension-manager' ),
			0   => \esc_html__( 'The subject is not found in the page title, you should add it.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			150 => 4,
			122 => 3,
			66  => 2,
			0   => 1,
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 150,
				'per' => 1,
				'max' => 1,
			],
			'synonym' => [
				'score' => 2 / 3 * 100,
				'per' => 1,
				'max' => 2,
			],
		],
	],
	'introduction' => [
		'title' => \esc_html__( 'Introduction:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageContent',
			'regex' => [
				// To simulate the `s` modifier (no webkit support), we use `.|\s`.
				'/(?=([^<>]+))\\1(?=<|$)/gi',                         // 1: All but tags. JS equiv. of `[^<>]++(?=<|$)`
				'/^(.|\\s){0,200}(.|\\s)*?(?=\\r?\\n(\\r?\\n)|$)/gi', // 2: Match first paragraph, or, when it's less than 200 character, the next paragraph(s).
				'/{{kw}}/gi',                                         // 3: Match words.
			],
		],
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			66  => \esc_html__( 'The subject is found a few times in the introduction, this is good.', 'the-seo-framework-extension-manager' ),
			50  => \esc_html__( 'The subject is found in the introduction, consider highlighting it a bit more.', 'the-seo-framework-extension-manager' ),
			0   => \esc_html__( 'The subject is not found in the introduction, you should highlight it.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			100 => 4,
			66  => 3,
			33  => 2,
			0   => 1,
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 50,
				'per' => 1,
				'max' => 2,
			],
			'synonym' => [
				'score' => 1 / 3 * 100,
				'per' => 1,
				'max' => 3,
			],
		],
	],
	'density' => [
		'title' => \esc_html__( 'Subject density:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageContent',
			'regex' => [
				'/(?=([^<>]+))\\1(?=<|$)/gi', // 1: All but tags. JS equiv. of `[^<>]++(?=<|$)`
				'/{{kw}}/gi',                 // 2: Match words.
			],
		],
		'maxScore' => 800,
		'minScore' => 0,
		'phrasing' => [
			1200 => \esc_html__( 'The subject density is far too high, consider lowering subject related word usage as it may seem like spam.', 'the-seo-framework-extension-manager' ),
			801  => \esc_html__( 'The subject density is high, consider lowering the subject usage.', 'the-seo-framework-extension-manager' ),
			400  => \esc_html__( 'The subject is recognizable from the content, this is good.', 'the-seo-framework-extension-manager' ),
			200  => \esc_html__( 'The subject is slightly recognizable from the content, consider highlighting it more.', 'the-seo-framework-extension-manager' ),
			0    => \esc_html__( 'The subject is not recognizable from the content, you should improve this.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			1200 => 1, // threshold 6%
			801  => 2, // threshold 4%
			400  => 4, // = 2%
			200  => 2, // = 1%
			0    => 1, // = 0%
		],
		'scoring' => [
			'type' => 'p',
			'threshold' => 4, // percent
			'penalty' => 3, // 3x the points are deducted per point going over the threshold.
			'keyword' => [
				'weight' => 100, // percent
			],
			'synonym' => [
				'weight' => 75, // percent
			],
		],
	],
	'linking' => [
		'title' => \esc_html__( 'Linking:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageContent',
			// Magic. Get all hyperlinks with the title or href matching, or content matching while href is present.
			'regex' => '/<a[^>]+\\b(((href|title)\\s*=["\'].*?{{kw}}.*?["\'][^<]+)|(href=[^>]*>[^<]*{{kw}}.*?))<\/a>/gi',
		],
		'maxScore' => 200,
		'minScore' => 0,
		'phrasing' => [
			100 => \esc_html__( 'A few links are found related to the subject, this is good.', 'the-seo-framework-extension-manager' ),
			50  => \esc_html__( 'A link is found related to the subject, this is good, but consider adding more.', 'the-seo-framework-extension-manager' ),
			0   => \esc_html__( 'No links are found related to the subject.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			100 => 4,
			66  => 3,
			33  => 2,
			0   => 1,
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 50,
				'per' => 1,
				'max' => 4,
			],
			'synonym' => [
				'score' => 1 / 3 * 100,
				'per' => 1,
				'max' => 4,
			],
		],
	],
	'seoDescription' => [
		'title' => \esc_html__( 'Meta description:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'seoDescription',
			'regex' => '/{{kw}}/gi',
		],
		'maxScore' => 50,
		'minScore' => 0,
		'phrasing' => [
			50  => \esc_html__( 'The subject is clearly found in the meta description, this is good.', 'the-seo-framework-extension-manager' ),
			25  => \esc_html__( 'The subject is found in the meta description, this is good.', 'the-seo-framework-extension-manager' ),
			0   => \esc_html__( 'The subject is not found in the meta description, you should add it.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			50 => 4,
			25 => 3,
			0  => 1,
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 37.5,
				'per' => 1,
				'max' => 1,
			],
			'synonym' => [
				'score' => 25,
				'per' => 1,
				'max' => 2,
			],
		],
	],
	'url' => [
		'title' => \esc_html__( 'Page URL:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageUrl',
			'regex' => [
				'/(?=([^<>]+))\\1(?=<|$)/gi', // 1: All but tags. JS equiv. of `[^<>]++(?=<|$)`
				'/{{kw}}/gi',                 // 2: Match words.
			],
		],
		'maxScore' => 125,
		'minScore' => 0,
		'phrasing' => [
			100 => \esc_html__( 'The subject is found in the page URL, this is good.', 'the-seo-framework-extension-manager' ),
			33  => \esc_html__( 'A synonym is found in the page URL, consider using an inflection instead.', 'the-seo-framework-extension-manager' ),
			0   => \esc_html__( 'The subject is not found in the page URL, you should add it.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			100 => 4,
			66  => 3,
			33  => 2,
			0   => 1,
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 100,
				'per' => 1,
				'max' => 1,
			],
			'synonym' => [
				'score' => 1 / 3 * 100,
				'per' => 1,
				'max' => 2,
			],
		],
	],
];

// phpcs:enable -- WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
