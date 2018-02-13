<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Focus\get_active_class() and $this instanceof $_class or die;

$key = $sub_scores['key'];
$values = $sub_scores['values'];
$has_keyword = (bool) strlen( $keyword['value'] );

printf(
	'<span class="hide-if-js attention">%s %s</span>',
	\esc_html__( 'JavaScript is required to perform a subject analysis.', 'the-seo-framework-extension-manager' ),
	$has_keyword ? \esc_html__( 'Below you find the previous assessments.', 'the-seo-framework-extension-manager' ) : ''
);
printf(
	'<span class="tsfem-e-focus-no-keyword-wrap hide-if-no-js attention" %s id=%s>%s</span>',
	\esc_attr( $key . '-no-content-wrap' ),
	$has_keyword ? 'style="display:none"' : '',
	\esc_html__( 'No keyword has been set, so no analysis can be made.', 'the-seo-framework-extension-manager' )
);

/**
 * Rating:
 * 0 = unknown.
 * 1 = bad.
 * 2 = warning.
 * 3 = okay.
 * 4 = good.
 */
$_scores = [
	'seoTitle' => [
		'title' => esc_html__( 'Meta title:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'seoTitle',
			'regex' => '/{{kw}}/giu',
			'eval' => [
				'input',
				'innerHTML',
				'placeholder',
			],
		],
		'maxScore' => 200,
		'minScore' => 0,
		'phrasing' => [
			200 => esc_html__( 'The keyword is found in the meta title, this is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'The subject is found in the meta title, consider using the keyword instead.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword is not found in the meta title, you should add it.', 'the-seo-framework-extension-manager' ),
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
			'subject' => [
				'score' => 50,
				'per' => 1,
				'max' => 2,
			],
		],
	],
	'pageTitle' => [
		'title' => esc_html__( 'Page title:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageTitle',
			'regex' => '/{{kw}}/giu',
			'eval' => [
				'input',
			],
		],
		'maxScore' => 150,
		'minScore' => 0,
		'phrasing' => [
			150 => esc_html__( 'The keyword is found in the page title, this is good.', 'the-seo-framework-extension-manager' ),
			66  => esc_html__( 'The subject is found in the page title, consider using the keyword instead.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword is not found in the page title, you should add it.', 'the-seo-framework-extension-manager' ),
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
			'subject' => [
				'score' => 2 / 3 * 100,
				'per' => 1,
				'max' => 2,
			],
		],
	],
	'firstParagraph' => [
		'title' => esc_html__( 'First paragraph:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageContent',
			'regex' => [
				// To simulate the `s` modifier (no webkit support), we use `.|\s`.
				'/^(.|\\s)*?(?=\\r?\\n(\\r?\\n)|$)/giu', // 1: Match first paragraph
				'/(?=>|(.|\\s))[^>]+(?=<)/gi',           // 2: All but tags.
				'/{{kw}}/giu',                           // 3: Match words.
			],
			'eval' => [
				'input',
			],
		],
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			66  => esc_html__( 'The subject is found a few times in the first paragraph, this is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'The subject is found in the first paragraph, consider highlighting it a bit more.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The subject is not found in the first paragraph, you should write an introduction on it.', 'the-seo-framework-extension-manager' ),
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
			'subject' => [
				'score' => 1 / 3 * 100,
				'per' => 1,
				'max' => 3,
			],
		],
	],
	'density' => [
		'title' => esc_html__( 'Subject density:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageContent',
			'regex' => [
				'/[^>]+(?=<|$|^)/gm', // 1: All but tags.
				'/{{kw}}/giu',        // 2: Match words.
			],
			'eval' => [
				'input',
			],
		],
		'maxScore' => 800,
		'minScore' => 0,
		'phrasing' => [
			1200 => esc_html__( 'The subject density is far too high, consider lowering the keyword usage as it may seem like spam.', 'the-seo-framework-extension-manager' ),
			801  => esc_html__( 'The subject density is high, consider lowering the subject usage.', 'the-seo-framework-extension-manager' ),
			400  => esc_html__( 'The subject is recognizable from the content, this is good.', 'the-seo-framework-extension-manager' ),
			200  => esc_html__( 'The subject is slightly recognizable from the content, consider highlighting it more.', 'the-seo-framework-extension-manager' ),
			0    => esc_html__( 'The subject is not recognizable from the content, you should improve this.', 'the-seo-framework-extension-manager' ),
		],
		'rating' => [
			1200 => 1, // threshold 6%
			801  => 2, // threshold 4%
			400  => 4,
			200  => 2,
			0    => 1,
		],
		'scoring' => [
			'type' => 'p',
			'threshold' => 4, // percent
			'penalty' => 3, // 3x the points are deducted per point going over the threshold.
			'keyword' => [
				'weight' => 100, // percent
			],
			'subject' => [
				'weight' => 75, // percent
			],
		],
	],
	'linking' => [
		'title' => esc_html__( 'Linking:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageContent',
			'regex' => '/<a\\W.*href=("|\')?(.*?{{kw}}.*?\\2(\\s|\\W).*?>.*?<\\/a>)|(\\2\W).*?(\\W|>){{kw}}(\\W|>)?.*?<\\/a>/giu',
			'eval' => [
				'input',
			],
		],
		'maxScore' => 200,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'A few links are found related to this subject. This is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'A link is found related to this subject. This is good, but consider adding more.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'No links are found related to this subject.', 'the-seo-framework-extension-manager' ),
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
			'subject' => [
				'score' => 1 / 3 * 100,
				'per' => 1,
				'max' => 4,
			],
		],
	],
	'seoDescription' => [
		'title' => esc_html__( 'Meta description:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'seoDescription',
			'regex' => '/{{kw}}/giu',
			'eval' => [
				'input',
				'placeholder',
			],
		],
		'maxScore' => 50,
		'minScore' => 0,
		'phrasing' => [
			50  => esc_html__( 'The subject is clearly found in the meta description, this is good.', 'the-seo-framework-extension-manager' ),
			25  => esc_html__( 'The subject is found in the meta description, this is good.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword is not found in the meta description, you should add it.', 'the-seo-framework-extension-manager' ),
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
			'subject' => [
				'score' => 25,
				'per' => 1,
				'max' => 2,
			],
		],
	],
	'url' => [
		'title' => esc_html__( 'Page URL:', 'the-seo-framework-extension-manager' ),
		'assessment' => [
			'content' => 'pageUrl',
			'regex' => '/{{kw}}/giu',
			'eval' => [
				'innerHTML',
			],
		],
		'maxScore' => 125,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'The keyword is found in the page URL, this is good.', 'the-seo-framework-extension-manager' ),
			33  => esc_html__( 'The subject is found in the page URL, consider using the keyword instead.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword is not found in the page URL, you should add it.', 'the-seo-framework-extension-manager' ),
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
			'subject' => [
				'score' => 1 / 3 * 100,
				'per' => 1,
				'max' => 2,
			],
		],
	],
];

$make_score_id = function( $type ) use ( $key ) {
	return sprintf( '%s[%s]', $key, $type );
};
$get_score_value = function( $type ) use ( $values ) {
	return isset( $values[ $type ] ) ? round( (float) $values[ $type ] ) : 0;
};
$make_data = function( array $data ) {
	$ret = [];
	foreach ( $data as $k => $v ) {
		if ( is_array( $v ) ) {
			$ret[] = sprintf( 'data-%s="%s"', $k, htmlspecialchars( json_encode( $v, JSON_UNESCAPED_SLASHES ), ENT_COMPAT, 'UTF-8' ) );
		} else {
			$ret[] = sprintf( 'data-%s="%s"', $k, $v );
		}
	}

	return implode( ' ', $ret );
};

/**
 * @param array $a
 * @param int $value
 */
$_get_nearest_numeric_index_value = function( array $a, $value ) {
	ksort( $a, SORT_NUMERIC );
	$ret = null;
	foreach ( $a as $k => $v ) {
		if ( is_numeric( $k ) ) {
			if ( $k <= $value ) {
				$ret = $v;
			} else {
				break;
			}
		}
	}
	return isset( $ret ) ? $ret : array_values( $array )[0];
};
$_get_icon_class = function( array $ratings, $value ) use ( $_get_nearest_numeric_index_value ) {
	$index = $_get_nearest_numeric_index_value( $ratings, $value );
	$classes = [
		-1 => 'tsfem-e-focus-icon-error', // reserved, unused.
		0  => 'tsfem-e-focus-icon-unknown',
		1  => 'tsfem-e-focus-icon-bad',
		2  => 'tsfem-e-focus-icon-warning',
		3  => 'tsfem-e-focus-icon-okay',
		4  => 'tsfem-e-focus-icon-good',
	];
	return isset( $classes[ $index ] ) ? $classes[ $index ] : $classes[0];
};

// TODO: use this.
// $max_score = 0;
// $current_score = 0;
// foreach ( $_scores as $type => $data ) {
// 	$max_score += $data['maxScore'];
// 	$current_score += $get_score_value( $type );
// }

$assessment_classes = [ 'tsfem-e-focus-assessment-wrap', 'tsf-flex' ];
$assessment_class = implode( ' ', $assessment_classes );

$block_style = $has_keyword ? '' : 'style="display:none"';

output_scores :;
	printf(
		'<div class="tsfem-e-focus-scores tsfem-flex" id=%s %s>',
		\esc_attr( $key ),
		$has_keyword ? '' : 'style="display:none"'
	);
	foreach ( $_scores as $type => $args ) :
		$_value = \esc_attr( $get_score_value( $type ) );
		$_id = \esc_attr( $make_score_id( $type ) );
		//! All output below should already be escaped.
		vprintf(
			'<span class="%s" id=%s %s>%s%s%s</span>',
			[
				$assessment_class,
				$_id,
				$make_data( [
					'scores' => [
						// 1: array_intersect_key( $args, array_fill_keys( [ 'assessment', 'maxScore', ...'' ], null ) )
						// 2: array_diff_key( $args, [ 'title' => null ] )
						'assessment' => $args['assessment'],
						'maxScore'   => $args['maxScore'],
						'minScore'   => $args['minScore'],
						'phrasing'   => $args['phrasing'],
						'rating'     => $args['rating'],
						'scoring'    => $args['scoring'],
					]
				] ),
				sprintf(
					'<span class="tsfem-e-focus-assessment-rating tsfem-e-focus-icon %s"></span>',
					$_get_icon_class( $args['rating'], $_value )
				),
				sprintf(
					'<strong class=tsfem-e-focus-assessment-title>%s</strong>',
					$args['title']
				),
				sprintf(
					'<span class=tsfem-e-focus-assessment-description>%s</span>',
					$_get_nearest_numeric_index_value( $args['phrasing'], $_value )
				),
			]
		);
		//= Data capturer.
		printf(
			'<input type=hidden name=%s value="%s">',
			$_id,
			$_value
		);
	endforeach;
	echo '</div>'; //= END tsfem-e-focus-scores;
