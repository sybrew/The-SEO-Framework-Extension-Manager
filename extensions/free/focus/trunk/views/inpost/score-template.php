<?php
/**
 * @package TSF_Extension_Manager\Extension\Focus\Admin\Views
 * @subpackage TSF_Extension_Manager\Inpost\Audit;
 */
namespace TSF_Extension_Manager\Extension\Focus;

defined( 'ABSPATH' ) and $_class = \TSF_Extension_Manager\Extension\Focus\get_active_class() and $this instanceof $_class or die;

$key = $sub_scores['key'];
$values = $sub_scores['values'];

printf(
	'<span class="hide-if-js attention">%s</span>',
	\esc_html__( 'JavaScript is required to perform a subject analysis. Below you find the previous assessments.', 'the-seo-framework-extension-manager' )
);
printf(
	'<span class="tsfem-e-focus-no-content-wrap hide-if-no-js attention" id=%s>%s</span>',
	\esc_attr( $key . '-no-content-wrap' ),
	\esc_html__( 'No keyword has been set, so no analysis can be made.', 'the-seo-framework-extension-manager' )
);

$_scores = [
	'seoTitle' => [
		'title' => esc_html__( 'Document title', 'the-seo-framework-extension-manager' ),
		'assessment' => 'seoTitle',
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'The keyword was found in the meta title, this is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'The subject was found in the meta title, consider using the keyword instead.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword was not found in the meta title, you should add it.', 'the-seo-framework-extension-manager' ),
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 100,
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
	'pageTitle' => [
		'title' => esc_html__( 'Page title', 'the-seo-framework-extension-manager' ),
		'assessment' => 'pageTitle',
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'The keyword was found in the page title, this is good.', 'the-seo-framework-extension-manager' ),
			33  => esc_html__( 'The subject was found in the page title, consider using the keyword instead.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword was not found in the page title, you should add it.', 'the-seo-framework-extension-manager' ),
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
	'firstParagraph' => [
		'title' => esc_html__( 'First paragraph', 'the-seo-framework-extension-manager' ),
		'assessment' => 'pageContent',
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			67  => esc_html__( 'The subject is found a few times in the first paragraph, this is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'The subject is found in the first paragraph, consider highlighting it a bit more.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The subject is not found in the first paragraph, you should write an introduction on it.', 'the-seo-framework-extension-manager' ),
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 66,
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
		'title' => esc_html__( 'Subject density', 'the-seo-framework-extension-manager' ),
		'assessment' => 'pageContent',
		'maxScore' => 800,
		'minScore' => 0,
		'phrasing' => [
			400  => esc_html__( 'The subject is recognizable from the content, this is good.', 'the-seo-framework-extension-manager' ),
			200  => esc_html__( 'The subject is slightly recognizable from the content, consider highlighting it more.', 'the-seo-framework-extension-manager' ),
			0    => esc_html__( 'The subject is not found in the content, this is bad.', 'the-seo-framework-extension-manager' ),
			/* translators: f = float, bracket/number formatting is for JavaScript compatibility. 1: Found keyword density number. 2: Maximum keyword density number. */
			'th' => esc_html__( 'The subject density is {{1:%1$f}}, the threshold is {{2:%2$f}}; consider lowering it as it seems like spam.', 'the-seo-framework-extension-manager' ),
		],
		'scoring' => [
			'type' => 'p',
			'threshold' => '2.5',
			'keyword' => [
				'each' => 25,
				'part' => 1,
			],
			'subject' => [
				'each' => 17,
				'part' => 1,
			],
		],
	],
	'linking' => [
		'title' => esc_html__( 'Linking', 'the-seo-framework-extension-manager' ),
		'assessment' => 'pageContent',
		'maxScore' => 200,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'A few links have been found related to this subject. This is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'A link has been found related to this subject. This is good, but consider adding more.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'No links have been found related to this subject.', 'the-seo-framework-extension-manager' ),
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 50,
				'per' => 1,
				'max' => 4,
			],
			'subject' => [
				'score' => 50,
				'per' => 1,
				'max' => 4,
			],
		],
	],
	'seoDescription' => [
		'title' => esc_html__( 'Meta description', 'the-seo-framework-extension-manager' ),
		'assessment' => 'seoDescription',
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'The subject was clearly found in the meta description, this is good.', 'the-seo-framework-extension-manager' ),
			50  => esc_html__( 'The subject was found in the meta description, this is good.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword was not found in the meta description, you should add it.', 'the-seo-framework-extension-manager' ),
		],
		'scoring' => [
			'type' => 'n',
			'keyword' => [
				'score' => 75,
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
	'url' => [
		'title' => esc_html__( 'Page URL', 'the-seo-framework-extension-manager' ),
		'assessment' => 'pageUrl',
		'maxScore' => 100,
		'minScore' => 0,
		'phrasing' => [
			100 => esc_html__( 'The keyword was found in the page URL, this is good.', 'the-seo-framework-extension-manager' ),
			33  => esc_html__( 'The subject was found in the page URL, consider using the keyword instead.', 'the-seo-framework-extension-manager' ),
			0   => esc_html__( 'The keyword was not found in the page URL, you should add it.', 'the-seo-framework-extension-manager' ),
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
	return sprintf( '%s[%s]', $type, $key );
};
$get_value = function( $type ) use ( $values ) {
	return isset( $values[ $type ] ) ? $values[ $type ] : '0';
};
$make_data = function( array $data ) {
	$ret = '';
	foreach ( $data as $k => $v ) {
		if ( is_array( $v ) ) {
			$ret .= sprintf( ' data-%s="%s"', $k, htmlspecialchars( json_encode( $v, JSON_UNESCAPED_SLASHES ), ENT_COMPAT, 'UTF-8' ) );
		} else {
			$ret .= sprintf( ' data-%s="%s"', $k, $v );
		}
	}

	return $ret;
};

$_find_max_key_below = function( array $a, $value ) {
	sort( $a, SORT_NUMERIC );
	$count = count( $a );
	$n = null;
	$i = 0;
	for ( ; ++$i < $count; next( $a ) ) {
		$key = key( $a );
		$n = is_numeric( $key )
		   ? ( $key < $value ? $key : $n )
		   : $n;
	}

	return isset( $a[ $n ] ) ? $n : 0;
};

// TODO: use this.
// $max_score = 0;
// $current_score = 0;
// foreach ( $_scores as $type => $data ) {
// 	$max_score += $data['maxScore'];
// 	$current_score += $get_value( $type );
// }

$_title_s = '<strong class=tsfem-e-focus-assessment-title>%s:</strong>';
$_title_format = \is_rtl() ? '%s ' . $_title_s : $_title_s . ' %s';

output_scores :;
	echo '<div class=tsfem-e-focus-scores id=' . \esc_attr( $key . '-scores-wrap' ) . '>';
	foreach ( $_scores as $type => $args ) :
		$_value = \esc_attr( $get_value( $type ) );
		$_id = \esc_attr( $make_score_id( $type ) );
		//! All output below should already be escaped.
		vprintf(
			'<span class=tsfem-e-focus-assessment id=%s%s>%s</span>',
			[
				$_id,
				$make_data( [
					'scores' => [
						'assessment' => $args['assessment'],
						'maxScore'   => $args['maxScore'],
						'minScore'   => $args['minScore'],
						'phrasing'   => $args['phrasing'],
						'scoring'    => $args['scoring'],
					]
				] ),
				sprintf(
					$_title_format,
					$args['title'],
					$args['phrasing'][ $_find_max_key_below( $args['phrasing'], $_value ) ]
				)
			]
		);
		printf(
			'<input type=hidden name=%s value="%s">',
			$_id,
			$_value
		);
	endforeach;
	echo '</div>'; //= END tsfem-e-focus-scores;
