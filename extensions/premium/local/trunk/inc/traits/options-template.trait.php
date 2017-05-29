<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Traits
 */
namespace TSF_Extension_Manager\Extension\Local;

defined( 'ABSPATH' ) or die;

/**
 * Local extension for The SEO Framework
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

// TODO @see https://angular.io/docs/ts/latest/cookbook/form-validation.html
// TODO @see https://developers.google.com/maps/documentation/geocoding/intro (note STATUS result.. we require an API key bound to an account)

/**
 * Holds options template for package TSF_Extension_Manager\Extension\Local.
 *
 * @since 1.0.0
 * @access private
 */
trait Options_Template {

	function get_template_output( $option, $default ) {
		yield [ $option => $this->get_option( $option ) ?: $default ];
	}

	//* Number of departments set.
	function get_department_count() { }

	//* The deparments set, in order??
	function get_departments() { }

	/**
	 *
	 * @link https://developers.google.com/search/docs/data-types/local-businesses
	 *
	 * @return array : {
	 *    string Option name => array Option attributes : {
	 *       '_default' : mixed  Default value. Null if there's no default.
	 *       '_edit'    : bool   Whether field is editable,
	 *       '_ret'     : string Field return type,
	 *       '_req'     : bool   Whether field is required,
	 *       '_type'    : array  Fields output type(s),
	 *       '_desc'    : array Description fields : {
	 *                       0 : string Title,
	 *                       1 : string Description,
	 *                       2 : string Additional description,
	 *                    },
	 *       '_range'   : array Range fields : {
	 *                       0 : int|float Min value,
	 *                       1 : int|float Max value,
	 *                       2 : int|float Step iteration,
	 *                    },
	 *    }
	 * }
	 */
	function get_departments_head_fields() {
		return [
			'depAmount' => [
				'_default' => 1,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'number',
				'_desc' => [
					\__( 'Set number of departments', '' ),
					\__( 'Each department must have its own publicly recognizable name and type.', '' ),
					\__( 'For example, if you have a small shop inside your restaurant, then set two departments.', '' ),
				],
				'_range' => [
					0,
					'',
					1,
				],
				'_dd' => $this->get_department_fields( false ),
			],
		];
	}

	/**
	 *
	 * @link https://developers.google.com/search/docs/data-types/local-businesses
	 *
	 * @return array : {
	 *    string Option name => array Option attributes : {
	 *       '_default' : mixed  Default value. Null if there's no default.
	 *       '_edit'    : bool   Whether field is editable,
	 *       '_ret'     : string Field return type,
	 *       '_req'     : bool   Whether field is required,
	 *       '_type'    : string Fields output type,
	 *       '_desc'    : array Description fields : {
	 *                       0 : string Title,
	 *                       1 : string Description,
	 *                       2 : string Additional description,
	 *                    },
	 *       '_dd'      : array Dropdown fields : {
	 *                       0 : mixed  Option return value,
	 *                       1 : string Option description,
	 *                       2 : mixed  Option subtypes, if '_type' is `[ dropdown, double ]`, : {
	 *                          0 : mixed  Option return value,
	 *                          1 : string Option description,
	 *                          2 : string Option additional description,
	 *                       },
	 *                    },
	 *       '_range'   : array Range fields : {
	 *                       0 : int|float Min value,
	 *                       1 : int|float Max value,
	 *                       2 : int|float Step iteration,
	 *                    },
	 *       '_select'  : array Select fields : {
	 *                       0 : mixed  Option return value,
	 *                       1 : string Option description,
	 *                       2 : mixed  Option subtypes, if '_type' is `[ dropdown, double ]`, : {
	 *                          0 : mixed  Option return value,
	 *                          1 : string Option description,
	 *                          2 : string Option additional description,
	 *                       },
	 *                    },
	 *       '_fields'  : array Object text fields : {
	 *                       '_default' : mixed  Default value. Null if there's no default.
	 *                       '_edit'    : bool   Whether field is editable,
	 *                       '_ret'     : string Field return type,
	 *                       '_req'     : bool   Whether field is required,
	 *                       '_type'    : array  Fields output type(s),
	 *                       '_desc'    : array Description fields : {
	 *                          0 : string Title,
	 *                          1 : string Description,
	 *                          2 : string Additional description,
	 *                       },
	 *                    },
	 *    }
	 * }
	 */
	function get_global_department_fields() {
		return [
			'type' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'Select supported department type', '' ),
					\__( 'Select a department type that exactly describes the practiced business.', '' ),
					\__( 'Leave empty if your department type is not listed.', '' ),
				],
				'_dd' => $this->get_department_fields( false ),
			],
			'name' => [
				'_default' => \the_seo_framework()->get_blogname(),
				'_edit' => true,
				'_ret' => 'string',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'Name of the department', '' ),
					\__( 'Fill in the name of the department accurately.', '' ),
				],
			],
			'@id' => [
				'_default' => null,
				'_edit' => false,
				'_ret' => 'string',
				'_req' => true,
				'_type' => 'text',
			],
			'address' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'array',
				'_req' => true,
				'_type' => 'address',
				'_desc' => [
					\__( 'Department address', '' ),
					\__( 'Fill in the exact address of the department.', '' ),
				],
				'_fields' => $this->get_address_fields(),
			],
			'url' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false,
				'_type' => 'url',
				'_desc' => [
					\__( 'Department URL', '' ),
					\__( 'The fully-qualified URL of the specific department location.', '' ),
					\__( 'For example, your contact page or home page. It must be a working link and the department location must be described on there.', '' ),
				],
			],
			'geo' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => 'text',
				'_desc' => [
					\__( 'Geographic coordinates of the department.', '' ),
				],
				'_fields' => $this->get_geo_fields(),
			],
			'telephone' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'tel',
				'_desc' => [
					\__( 'Telephone number', '' ),
					\__( 'A business phone number meant to be the primary contact method for customers.', '' ),
					\__( 'Be sure to include the country code and area code in the phone number.', '' ),
				],
			],
			'potentialAction' => [ // SPLIT THIS?? -> i.e. "You can order.. We deliver... + fields..." TODO
				'_default' => null,
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => '', // TODO,
				'_desc' => [
					\__( 'Geographic coordinates of the department', '' ),
				],
			//	'_fields' => $this->get_potential_action_fields(), // TODO
			],
			'openingHoursSpecification' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => '', // TODO,
				'_desc' => [
					\__( 'Department opening hours', '' ),
				],
			//	'_fields' => $this->get_opening_hours_fields(), // TODO
			],
			// THESE ARE FOOD ESTABLISHMENT SPECIFIC... TODO split?
			'menu' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false,
				'_type' => 'url',
				'_desc' => [
					\__( 'Menu URL', '' ),
					\__( 'Department menu URL, if any.', '' ),
				],
			],
			'acceptsReservations' => [
				'_default' => 0,
				'_edit' => true,
				'_ret' => 'integer', // Actually, a boolean...
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'Accept reservations', '' ),
					\__( 'If you accept reservations, set this options. If you explicitly don\'t accept reservations as a food establishment, also set this option.', '' ),
				],
			//	'_dd' => $this->get_reservation_fields(), // 0, 1, 2... 0 = not specified, 1 = nope, 2 = yup TODO
			],
			'image' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false, // Must be true if RESTAURANT.
				'_type' => 'image',
				'_desc' => [
					\__( 'Image', '' ),
					\__( 'An image of the business.', '' ),
				],
			],
			'servesCuisine' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false, // Must be true if RESTAURANT.
				'_type' => 'select',
				'_desc' => [
					\__( 'Cuisine', '' ),
					\__( 'Provide the type of cuisine the department serves.', '' ),
				],
			//	'_dd' => $this->get_cuisine_fields(), // TODO make list of cuisine types... there's no default list yet??
			],
		];
	}

	function get_address_fields() {
		return [
			'streetaddress' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'text',
				'_desc' => [
					\__( 'Street address', '' ),
					'',
					\__( 'Street number, street name, and unit number (if applicable).', '' ),
				],
			],
			'addressLocality' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'City, town, village', '' ),
				],
			],
			'addressRegion' => [ // THIS IS INCORRECT!! TODO... must be state/provide code.
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'text',
				'_desc' => [
					\__( 'State or province', '' ),
				],
			],
			'postalCode' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'Postal or zip code', '' ),
				],
			],
			'addressCountry' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => true,
				'_type' => 'select',
				'_desc' => [
					\__( 'State or province', '' ),
				],
				'_select' => $this->get_country_fields(),
			],
		];
	}

	/**
	 * Use Generator Class + Iterator for dropdown parsing?
	 * http://php.net/manual/en/class.generator.php
	 */
	function get_country_fields() {
		return [
			[
				'',
				'&mdash; ' . \__( 'No country selected', '' ) . ' &mdash;',
			],
			[
				'AF',
				'Afghanistan',
			],
			[
				'AX',
				'Åland Islands',
			],
			[
				'AL',
				'Albania',
			],
			[
				'DZ',
				'Algeria',
			],
			[
				'AS',
				'American Samoa',
			],
			[
				'AD',
				'Andorra',
			],
			[
				'AO',
				'Angola',
			],
			[
				'AI',
				'Anguilla',
			],
			[
				'AQ',
				'Antarctica',
			],
			[
				'AG',
				'Antigua and Barbuda',
			],
			[
				'AR',
				'Argentina',
			],
			[
				'AM',
				'Armenia',
			],
			[
				'AW',
				'Aruba',
			],
			[
				'AU',
				'Australia',
			],
			[
				'AT',
				'Austria',
			],
			[
				'AZ',
				'Azerbaijan',
			],
			[
				'BS',
				'Bahamas',
			],
			[
				'BH',
				'Bahrain',
			],
			[
				'BD',
				'Bangladesh',
			],
			[
				'BB',
				'Barbados',
			],
			[
				'BY',
				'Belarus',
			],
			[
				'BE',
				'Belgium',
			],
			[
				'BZ',
				'Belize',
			],
			[
				'BJ',
				'Benin',
			],
			[
				'BM',
				'Bermuda',
			],
			[
				'BT',
				'Bhutan',
			],
			[
				'BO',
				'Bolivia (Plurinational State of)',
			],
			[
				'BQ',
				'Bonaire, Sint Eustatius and Saba',
			],
			[
				'BA',
				'Bosnia and Herzegovina',
			],
			[
				'BW',
				'Botswana',
			],
			[
				'BV',
				'Bouvet Island',
			],
			[
				'BR',
				'Brazil',
			],
			[
				'IO',
				'British Indian Ocean Territory',
			],
			[
				'BN',
				'Brunei Darussalam',
			],
			[
				'BG',
				'Bulgaria',
			],
			[
				'BF',
				'Burkina Faso',
			],
			[
				'BI',
				'Burundi',
			],
			[
				'CV',
				'Cabo Verde',
			],
			[
				'KH',
				'Cambodia',
			],
			[
				'CM',
				'Cameroon',
			],
			[
				'CA',
				'Canada',
			],
			[
				'KY',
				'Cayman Islands',
			],
			[
				'CF',
				'Central African Republic',
			],
			[
				'TD',
				'Chad',
			],
			[
				'CL',
				'Chile',
			],
			[
				'CN',
				'China',
			],
			[
				'CX',
				'Christmas Island',
			],
			[
				'CC',
				'Cocos (Keeling) Islands',
			],
			[
				'CO',
				'Colombia',
			],
			[
				'KM',
				'Comoros',
			],
			[
				'CG',
				'Congo',
			],
			[
				'CD',
				'Congo (Democratic Republic of the)',
			],
			[
				'CK',
				'Cook Islands',
			],
			[
				'CR',
				'Costa Rica',
			],
			[
				'CI',
				'Côte d\'Ivoire',
			],
			[
				'HR',
				'Croatia',
			],
			[
				'CU',
				'Cuba',
			],
			[
				'CW',
				'Curaçao',
			],
			[
				'CY',
				'Cyprus',
			],
			[
				'CZ',
				'Czechia',
			],
			[
				'DK',
				'Denmark',
			],
			[
				'DJ',
				'Djibouti',
			],
			[
				'DM',
				'Dominica',
			],
			[
				'DO',
				'Dominican Republic',
			],
			[
				'EC',
				'Ecuador',
			],
			[
				'EG',
				'Egypt',
			],
			[
				'SV',
				'El Salvador',
			],
			[
				'GQ',
				'Equatorial Guinea',
			],
			[
				'ER',
				'Eritrea',
			],
			[
				'EE',
				'Estonia',
			],
			[
				'ET',
				'Ethiopia',
			],
			[
				'FK',
				'Falkland Islands (Malvinas)',
			],
			[
				'FO',
				'Faroe Islands',
			],
			[
				'FJ',
				'Fiji',
			],
			[
				'FI',
				'Finland',
			],
			[
				'FR',
				'France',
			],
			[
				'GF',
				'French Guiana',
			],
			[
				'PF',
				'French Polynesia',
			],
			[
				'TF',
				'French Southern Territories',
			],
			[
				'GA',
				'Gabon',
			],
			[
				'GM',
				'Gambia',
			],
			[
				'GE',
				'Georgia',
			],
			[
				'DE',
				'Germany',
			],
			[
				'GH',
				'Ghana',
			],
			[
				'GI',
				'Gibraltar',
			],
			[
				'GR',
				'Greece',
			],
			[
				'GL',
				'Greenland',
			],
			[
				'GD',
				'Grenada',
			],
			[
				'GP',
				'Guadeloupe',
			],
			[
				'GU',
				'Guam',
			],
			[
				'GT',
				'Guatemala',
			],
			[
				'GG',
				'Guernsey',
			],
			[
				'GN',
				'Guinea',
			],
			[
				'GW',
				'Guinea-Bissau',
			],
			[
				'GY',
				'Guyana',
			],
			[
				'HT',
				'Haiti',
			],
			[
				'HM',
				'Heard Island and McDonald Islands',
			],
			[
				'VA',
				'Holy See',
			],
			[
				'HN',
				'Honduras',
			],
			[
				'HK',
				'Hong Kong',
			],
			[
				'HU',
				'Hungary',
			],
			[
				'IS',
				'Iceland',
			],
			[
				'IN',
				'India',
			],
			[
				'ID',
				'Indonesia',
			],
			[
				'IR',
				'Iran (Islamic Republic of)',
			],
			[
				'IQ',
				'Iraq',
			],
			[
				'IE',
				'Ireland',
			],
			[
				'IM',
				'Isle of Man',
			],
			[
				'IL',
				'Israel',
			],
			[
				'IT',
				'Italy',
			],
			[
				'JM',
				'Jamaica',
			],
			[
				'JP',
				'Japan',
			],
			[
				'JE',
				'Jersey',
			],
			[
				'JO',
				'Jordan',
			],
			[
				'KZ',
				'Kazakhstan',
			],
			[
				'KE',
				'Kenya',
			],
			[
				'KI',
				'Kiribati',
			],
			[
				'KP',
				'Korea (Democratic People\'s Republic of)',
			],
			[
				'KR',
				'Korea (Republic of)',
			],
			[
				'KW',
				'Kuwait',
			],
			[
				'KG',
				'Kyrgyzstan',
			],
			[
				'LA',
				'Lao People\'s Democratic Republic',
			],
			[
				'LV',
				'Latvia',
			],
			[
				'LB',
				'Lebanon',
			],
			[
				'LS',
				'Lesotho',
			],
			[
				'LR',
				'Liberia',
			],
			[
				'LY',
				'Libya',
			],
			[
				'LI',
				'Liechtenstein',
			],
			[
				'LT',
				'Lithuania',
			],
			[
				'LU',
				'Luxembourg',
			],
			[
				'MO',
				'Macao',
			],
			[
				'MK',
				'Macedonia (the former Yugoslav Republic of)',
			],
			[
				'MG',
				'Madagascar',
			],
			[
				'MW',
				'Malawi',
			],
			[
				'MY',
				'Malaysia',
			],
			[
				'MV',
				'Maldives',
			],
			[
				'ML',
				'Mali',
			],
			[
				'MT',
				'Malta',
			],
			[
				'MH',
				'Marshall Islands',
			],
			[
				'MQ',
				'Martinique',
			],
			[
				'MR',
				'Mauritania',
			],
			[
				'MU',
				'Mauritius',
			],
			[
				'YT',
				'Mayotte',
			],
			[
				'MX',
				'Mexico',
			],
			[
				'FM',
				'Micronesia (Federated States of)',
			],
			[
				'MD',
				'Moldova (Republic of)',
			],
			[
				'MC',
				'Monaco',
			],
			[
				'MN',
				'Mongolia',
			],
			[
				'ME',
				'Montenegro',
			],
			[
				'MS',
				'Montserrat',
			],
			[
				'MA',
				'Morocco',
			],
			[
				'MZ',
				'Mozambique',
			],
			[
				'MM',
				'Myanmar',
			],
			[
				'NA',
				'Namibia',
			],
			[
				'NR',
				'Nauru',
			],
			[
				'NP',
				'Nepal',
			],
			[
				'NL',
				'Netherlands',
			],
			[
				'NC',
				'New Caledonia',
			],
			[
				'NZ',
				'New Zealand',
			],
			[
				'NI',
				'Nicaragua',
			],
			[
				'NE',
				'Niger',
			],
			[
				'NG',
				'Nigeria',
			],
			[
				'NU',
				'Niue',
			],
			[
				'NF',
				'Norfolk Island',
			],
			[
				'MP',
				'Northern Mariana Islands',
			],
			[
				'NO',
				'Norway',
			],
			[
				'OM',
				'Oman',
			],
			[
				'PK',
				'Pakistan',
			],
			[
				'PW',
				'Palau',
			],
			[
				'PS',
				'Palestine, State of',
			],
			[
				'PA',
				'Panama',
			],
			[
				'PG',
				'Papua New Guinea',
			],
			[
				'PY',
				'Paraguay',
			],
			[
				'PE',
				'Peru',
			],
			[
				'PH',
				'Philippines',
			],
			[
				'PN',
				'Pitcairn',
			],
			[
				'PL',
				'Poland',
			],
			[
				'PT',
				'Portugal',
			],
			[
				'PR',
				'Puerto Rico',
			],
			[
				'QA',
				'Qatar',
			],
			[
				'RE',
				'Réunion',
			],
			[
				'RO',
				'Romania',
			],
			[
				'RU',
				'Russian Federation',
			],
			[
				'RW',
				'Rwanda',
			],
			[
				'BL',
				'Saint Barthélemy',
			],
			[
				'SH',
				'Saint Helena, Ascension and Tristan da Cunha',
			],
			[
				'KN',
				'Saint Kitts and Nevis',
			],
			[
				'LC',
				'Saint Lucia',
			],
			[
				'MF',
				'Saint Martin (French part)',
			],
			[
				'PM',
				'Saint Pierre and Miquelon',
			],
			[
				'VC',
				'Saint Vincent and the Grenadines',
			],
			[
				'WS',
				'Samoa',
			],
			[
				'SM',
				'San Marino',
			],
			[
				'ST',
				'Sao Tome and Principe',
			],
			[
				'SA',
				'Saudi Arabia',
			],
			[
				'SN',
				'Senegal',
			],
			[
				'RS',
				'Serbia',
			],
			[
				'SC',
				'Seychelles',
			],
			[
				'SL',
				'Sierra Leone',
			],
			[
				'SG',
				'Singapore',
			],
			[
				'SX',
				'Sint Maarten (Dutch part)',
			],
			[
				'SK',
				'Slovakia',
			],
			[
				'SI',
				'Slovenia',
			],
			[
				'SB',
				'Solomon Islands',
			],
			[
				'SO',
				'Somalia',
			],
			[
				'ZA',
				'South Africa',
			],
			[
				'GS',
				'South Georgia and the South Sandwich Islands',
			],
			[
				'SS',
				'South Sudan',
			],
			[
				'ES',
				'Spain',
			],
			[
				'LK',
				'Sri Lanka',
			],
			[
				'SD',
				'Sudan',
			],
			[
				'SR',
				'Suriname',
			],
			[
				'SJ',
				'Svalbard and Jan Mayen',
			],
			[
				'SZ',
				'Swaziland',
			],
			[
				'SE',
				'Sweden',
			],
			[
				'CH',
				'Switzerland',
			],
			[
				'SY',
				'Syrian Arab Republic',
			],
			[
				'TW',
				'Taiwan (Province of China)',
			],
			[
				'TJ',
				'Tajikistan',
			],
			[
				'TZ',
				'Tanzania, United Republic of',
			],
			[
				'TH',
				'Thailand',
			],
			[
				'TL',
				'Timor-Leste',
			],
			[
				'TG',
				'Togo',
			],
			[
				'TK',
				'Tokelau',
			],
			[
				'TO',
				'Tonga',
			],
			[
				'TT',
				'Trinidad and Tobago',
			],
			[
				'TN',
				'Tunisia',
			],
			[
				'TR',
				'Turkey',
			],
			[
				'TM',
				'Turkmenistan',
			],
			[
				'TC',
				'Turks and Caicos Islands',
			],
			[
				'TV',
				'Tuvalu',
			],
			[
				'UG',
				'Uganda',
			],
			[
				'UA',
				'Ukraine',
			],
			[
				'AE',
				'United Arab Emirates',
			],
			[
				'GB',
				'United Kingdom of Great Britain and Northern Ireland',
			],
			[
				'US',
				'United States of America',
			],
			[
				'UM',
				'United States Minor Outlying Islands',
			],
			[
				'UY',
				'Uruguay',
			],
			[
				'UZ',
				'Uzbekistan',
			],
			[
				'VU',
				'Vanuatu',
			],
			[
				'VE',
				'Venezuela (Bolivarian Republic of)',
			],
			[
				'VN',
				'Viet Nam',
			],
			[
				'VG',
				'Virgin Islands (British)',
			],
			[
				'VI',
				'Virgin Islands (U.S.)',
			],
			[
				'WF',
				'Wallis and Futuna',
			],
			[
				'EH',
				'Western Sahara',
			],
			[
				'YE',
				'Yemen',
			],
			[
				'ZM',
				'Zambia',
			],
			[
				'ZW',
				'Zimbabwe',
			],
		];
	}

	function get_geo_fields() {
		return [
			'latitude' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'number',
				'_range' => [
					-90,
					90,
					1e-7,
				],
				'_desc' => [
					\__( 'Latitude', '' ),
					'',
					\__( 'The geographic latitude.', '' ),
				],
			],
			'longitude' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'number',
				'_range' => [
					-180,
					180,
					1e-7,
				],
				'_desc' => [
					\__( 'Longitude', '' ),
					'',
					\__( 'The geographic longitude.', '' ),
				],
			],
		];
	}

	/**
	 * @return array : {
	 *   0 : mixed  Option return value,
	 *   1 : string Option description,
	 *   2 : mixed  Option subtypes : {
	 *      0 : mixed  Option return value,
	 *      1 : string Option description,
	 *   },
	 * }
	 */
	function get_department_fields( $get_subfield = false ) {
		return [
			[
				0,
				'&mdash; ' . \__( 'No department selected', '' ) . ' &mdash;',
				null, // No subtypes, doh.
			],
			[
				'AnimalShelter',
				\__( 'Animal shelter.', '' ),
				null, // No subtypes.
			],
			[
				'AutomotiveBusiness',
				\__( 'Car repair, sales, or parts.', '' ),
				[
					[
						'AutoBodyShop',
						\__( 'Auto body shop.', '' ),
					],
					[
						'AutoDealer',
						\__( 'A car dealership.', '' ),
					],
					[
						'AutoPartsStore',
						\__( 'An auto parts store.', '' ),
					],
					[
						'AutoRental',
						\__( 'A car rental business.', '' ),
					],
					[
						'AutoRepair',
						\__( 'Car repair business.', '' ),
					],
					[
						'AutoWash',
						\__( 'A car wash business.', '' ),
					],
					[
						'GasStation',
						\__( 'A gas station.', '' ),
					],
					[
						'MotorcycleDealer',
						\__( 'A motorcycle dealer.', '' ),
					],
					[
						'MotorcycleRepair',
						\__( 'A motorcycle repair shop.', '' ),
					],
				],
			],
			[
				'ChildCare',
				\__( 'A Childcare center.', '' ),
				null, // No subtypes.
			],
			[
				'MedicalBusiness',
				\__( 'A particular physical or virtual business of an organization for medical purposes.', '' ),
				[
					[
						'CommunityHealth',
						\__( 'A field of public health focusing on improving health characteristics of a defined population in relation with their geographical or environment areas.', '' ),
					],
					[
						'Dentist',
						\__( 'A dentist.', '' ),
					],
					[
						'Dermatology',
						\__( 'A specific branch of medical science that pertains to diagnosis and treatment of disorders of skin.', '' ),
					],
					[
						'DietNutrition',
						\__( 'Dietetic and nutrition as a medical speciality.', '' ),
					],
					[
						'Emergency',
						\__( 'A specific branch of medical science that deals with the evaluation and initial treatment of medical conditions caused by trauma or sudden illness.', '' ),
					],
					[
						'Geriatric',
						\__( 'A specific branch of medical science that is concerned with the diagnosis and treatment of diseases, debilities and provision of care to the aged.', '' ),
					],
					[
						'Gynecologic',
						\__( 'A specific branch of medical science that pertains to the health care of women, particularly in the diagnosis and treatment of disorders affecting the female reproductive system.', '' ),
					],
					[
						'MedicalClinic',
						\__( 'A specific branch of medical science that pertains to the health care of women, particularly in the diagnosis and treatment of disorders affecting the female reproductive system.', '' ),
					],
					[
						'Midwifery',
						\__( 'A nurse-like health profession that deals with pregnancy, childbirth, and the postpartum period (including care of the newborn), besides sexual and reproductive health of women throughout their lives.', '' ),
					],
					[
						'Nursing',
						\__( 'A health profession of a person formally educated and trained in the care of the sick or infirm person.', '' ),
					],
					[
						'Obstetric',
						\__( 'A specific branch of medical science that specializes in the care of women during the prenatal and postnatal care and with the delivery of the child.', '' ),
					],
					[
						'Oncologic',
						\__( 'A specific branch of medical science that deals with benign and malignant tumors, including the study of their development, diagnosis, treatment and prevention.', '' ),
					],
					[
						'Optician',
						\__( 'A store that sells reading glasses and similar devices for improving vision.', '' ),
					],
					[
						'Optometric',
						\__( 'The science or practice of testing visual acuity and prescribing corrective lenses.', '' ),
					],
					[
						'Otolaryngologic',
						\__( 'A specific branch of medical science that is concerned with the ear, nose and throat and their respective disease states.', '' ),
					],
					[
						'Pediatric',
						\__( 'A specific branch of medical science that specializes in the care of infants, children and adolescents.', '' ),
					],
					[
						'Pharmacy',
						\__( 'A pharmacy or drugstore.', '' ),
					],
					[
						'Physician',
						\__( 'A doctor\'s office.', '' ),
					],
					[
						'Physiotherapy',
						\__( 'The practice of treatment of disease, injury, or deformity by physical methods such as massage, heat treatment, and exercise rather than by drugs or surgery.', '' ),
					],
					[
						'PlasticSurgery',
						\__( 'A specific branch of medical science that pertains to therapeutic or cosmetic repair or re-formation of missing, injured or malformed tissues or body parts by manual and instrumental means.', '' ),
					],
					[
						'Podiatric',
						\__( 'Podiatry is the care of the human foot, especially the diagnosis and treatment of foot disorders.', '' ),
					],
					[
						'PrimaryCare',
						\__( 'The medical care by a physician, or other health-care professional, who is the patient\'s first contact with the health-care system and who may recommend a specialist if necessary.', '' ),
					],
					[
						'Psychiatric',
						\__( 'A specific branch of medical science that is concerned with the study, treatment, and prevention of mental illness, using both medical and psychological therapies.', '' ),
					],
					[
						'PublicHealth',
						\__( 'Branch of medicine that pertains to the health services to improve and protect community health, especially epidemiology, sanitation, immunization, and preventive medicine.', '' ),
					],
				],
			],
			[
				'DryCleaningOrLaundry',
				\__( 'A dry-cleaning business.', '' ),
				null, // No subtypes.
			],
			[
				'EmergencyService',
				\__( 'An emergency service, such as a fire station or ER.', '' ),
				null, // TODO
			],
			[
				'EmploymentAgency',
				\__( 'An employment agency.', '' ),
				null, // TODO
			],
			[
				'EntertainmentBusiness',
				\__( 'A business providing entertainment.', '' ),
				null, // TODO
			],
			[
				'FinancialService',
				\__( 'Financial services business.', '' ),
				null, // TODO
			],
			[
				'FoodEstablishment',
				\__( 'A food-related business.', '' ),
				null, // TODO
			],
			[
				'GovernmentOffice',
				\__( 'A government office—for example, an IRS or DMV office.', '' ),
				null, // TODO
			],
			[
				'HealthAndBeautyBusiness',
				\__( 'Health and beauty.', '' ),
				null, // TODO
			],
			[
				'HomeAndConstructionBusiness',
				\__( 'A construction business.', '' ),
				null, // TODO
			],
			[
				'InternetCafe',
				\__( 'An internet cafe.', '' ),
				null, // TODO
			],
			[
				'LegalService',
				\__( 'A LegalService is a business that provides legally-oriented services, advice and representation, e.g. law firms.', '' ),
				null, // TODO
			],
			[
				'Library',
				\__( 'A library.', '' ),
				null, // TODO
			],
			[
				'LodgingBusiness',
				\__( 'A lodging business, such as a motel, hotel, or inn.', '' ),
				null, // TODO
			],
			// MORE FOUND HERE: http://schema.org/ProfessionalService
			[
				'RadioStation',
				\__( 'A radio station.', '' ),
				null, // TODO
			],
			[
				'RealEstateAgent',
				\__( 'A real-estate agent.', '' ),
				null, // TODO
			],
			[
				'RecyclingCenter',
				\__( 'A recycling center.', '' ),
				null, // TODO
			],
			[
				'SelfStorage',
				\__( 'A self-storage facility.', '' ),
				null, // TODO
			],
			[
				'ShoppingCenter',
				\__( 'A shopping center or mall.', '' ),
				null, // TODO
			],
			[
				'SportsActivityLocation',
				\__( 'A sports location, such as a playing field.', '' ),
				null, // TODO
			],
			[
				'Store',
				\__( 'A retail good store.', '' ),
				null, // TODO
			],
			[
				'TelevisionStation',
				\__( 'A television station.', '' ),
				null, // TODO
			],
			[
				'TouristInformationCenter',
				\__( 'A tourist information center.', '' ),
				null, // TODO
			],
			[
				'TravelAgency',
				\__( 'A travel agency.', '' ),
				null, // TODO
			],
		];
	}
}
