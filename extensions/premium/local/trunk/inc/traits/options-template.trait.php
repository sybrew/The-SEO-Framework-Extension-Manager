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
	 *       '_range'   : array Range fields : {
	 *                       0 : int|float Min value,
	 *                       1 : int|float Max value,
	 *                       2 : int|float Step iteration,
	 *                    },
	 *       '_select'  : array Select fields : {
	 *                       0 : mixed  Option return value,
	 *                       1 : string Option description,
	 *                       2 : mixed  Option subtypes, if any : {
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
					[
						\__( 'Choose a (sub)type that exactly describes the business.', '' ),
						\__( '(Sub)types with an asterisk are pending support.', '' ),
					],
					\__( 'Leave unspecified if your department type is not listed.', '' ),
				],
				'_select' => $this->get_department_fields( false ),
			],
			'name' => [
				'_default' => '',
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
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Department address', '' ),
					\__( 'Fill in the exact address of the department.', '' ),
				],
				'_fields' => $this->get_address_fields() + $this->get_geo_fields(),
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
			//* This is still in a piloting program for Google by select businesses. Let user know through link or disable completely for the time being...
			// 'potentialAction' => [ // SPLIT THIS?? -> i.e. "You can order.. We deliver... + fields..." TODO
			// 	'_default' => null,
			// 	'_edit' => true,
			// 	'_ret' => 'array',
			// 	'_req' => false,
			// 	'_type' => '', // TODO,
			// 	'_desc' => [
			// 		\__( 'Geographic coordinates of the department', '' ),
			// 	],
			// //	'_fields' => $this->get_potential_action_fields(), // TODO
			// ],
			'openingHoursSpecification' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => 'multi', // TODO,
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
			'reservations' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Reservations', '' ),
					\__( 'Department customers\' reservation specification.', '' ),
					\__( 'For food establishments.', '' ),
				],
				'_fields' => $this->get_reservation_fields(),
			],
			'image' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false, // Must be true if RESTAURANT.
				'_type' => 'image',
				'_desc' => [
					\__( 'Image', '' ),
					\__( 'An image of the department or building.', '' ),
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
				'_select' => $this->get_cuisine_fields(),
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
			'addressRegion' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'text',
				'_desc' => [
					\__( 'State or province', '' ),
					\__( 'The region. For example, CA for California.' ),
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
					\__( 'Country', '' ),
				],
				'_select' => $this->get_country_fields(),
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

	/**
	 *
	 *
	 * @see https://jsfiddle.net/xgk8osdc/4/ for EZ i18n generator.
	 *
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
				'',
				'&mdash; ' . \__( 'Not specified', '' ) . ' &mdash;',
				null, // No subtypes, doh.
			],
			[
				'AnimalShelter',
				\__( 'Animal shelter', '' ),
				[],
			],
			[
				'AutomotiveBusiness',
				\__( 'Automotive business', '' ),
				[
					[
						'AutoBodyShop',
						\__( 'Auto body shop', '' ),
					],
					[
						'AutoDealer',
						\__( 'Auto dealer', '' ),
					],
					[
						'AutoPartsStore',
						\__( 'Auto parts store', '' ),
					],
					[
						'AutoRental',
						\__( 'Auto rental', '' ),
					],
					[
						'AutoRepair',
						\__( 'Auto repair', '' ),
					],
					[
						'AutoWash',
						\__( 'Auto wash', '' ),
					],
					[
						'GasStation',
						\__( 'Gas station', '' ),
					],
					[
						'MotorcycleDealer',
						\__( 'Motorcycle dealer', '' ),
					],
					[
						'MotorcycleRepair',
						\__( 'Motorcycle repair', '' ),
					],
				],
			],
			[
				'ChildCare',
				\__( 'Child care', '' ),
				[],
			],
			[
				'MedicalBusiness',
				\__( 'Medical business', '' ) . ' *',
				[
					//= Names with asterisk are still under review by Schema.org and are currently Extensions...
					[
						'CommunityHealth',
						\__( 'Community health', '' ) . ' *',
					],
					[
						'Dentist',
						\__( 'Dentist', '' ),
					],
					[
						'Dermatology',
						\__( 'Dermatology', '' ) . ' *',
					],
					[
						'DietNutrition',
						\__( 'Diet / Nutrition', '' ) . ' *',
					],
					[
						'Emergency',
						\__( 'Emergency / Trauma', '' ) . ' *',
					],
					[
						'Geriatric',
						\__( 'Geriatric', '' ) . ' *',
					],
					[
						'Gynecologic',
						\__( 'Gynecologic', '' ) . ' *',
					],
					[
						'MedicalClinic',
						\__( 'Medical clinic', '' ) . ' *',
					],
					[
						'Midwifery',
						\__( 'Midwifery', '' ) . ' *',
					],
					[
						'Nursing',
						\__( 'Nursing', '' ) . ' *',
					],
					[
						'Obstetric',
						\__( 'Obstetric', '' ) . ' *',
					],
					[
						'Oncologic',
						\__( 'Oncologic', '' ) . ' *',
					],
					[
						'Optician',
						\__( 'Optician', '' ) . ' *',
					],
					[
						'Optometric',
						\__( 'Optometric', '' ) . ' *',
					],
					[
						'Otolaryngologic',
						\__( 'Otolaryngologic', '' ) . ' *',
					],
					[
						'Pediatric',
						\__( 'Pediatric', '' ) . ' *',
					],
					[
						'Pharmacy',
						\__( 'Pharmacy', '' ),
					],
					[
						'Physician',
						\__( 'Physician', '' ),
					],
					[
						'Physiotherapy',
						\__( 'Physiotherapy', '' ) . ' *',
					],
					[
						'PlasticSurgery',
						\__( 'Plastic surgery', '' ) . ' *',
					],
					[
						'Podiatric',
						\__( 'Podiatric', '' ) . ' *',
					],
					[
						'PrimaryCare',
						\__( 'Primary care', '' ) . ' *',
					],
					[
						'Psychiatric',
						\__( 'Psychiatric', '' ) . ' *',
					],
					[
						'PublicHealth',
						\__( 'Public health', '' ) . ' *',
					],
				],
			],
			[
				'DryCleaningOrLaundry',
				\__( 'Dry cleaning or laundry', '' ),
				[],
			],
			[
				'EmergencyService',
				\__( 'Emergency service', '' ),
				[
					[
						'FireStation',
						\__( 'Fire station', '' ),
					],
					[
						'Hospital',
						\__( 'Hospital', '' ),
					],
					[
						'PoliceStation',
						\__( 'Police station', '' ),
					],
				],
			],
			[
				'EmploymentAgency',
				\__( 'Employment agency', '' ),
				[],
			],
			[
				'EntertainmentBusiness',
				\__( 'Entertainment business', '' ),
				[
					[
						'AdultEntertainment',
						\__( 'Adult entertainment', '' ),
					],
					[
						'AmusementPark',
						\__( 'Amusement park', '' ),
					],
					[
						'ArtGallery',
						\__( 'Art gallery', '' ),
					],
					[
						'Casino',
						\__( 'Casino', '' ),
					],
					[
						'ComedyClub',
						\__( 'Comedy club', '' ),
					],
					[
						'MovieTheater',
						\__( 'Movie theater', '' ),
					],
					[
						'NightClub',
						\__( 'Night club', '' ),
					],
				],
			],
			[
				'FinancialService',
				\__( 'Financial service', '' ),
				[
					[
						'AccountingService',
						\__( 'Accounting service', '' ),
					],
					[
						'AutomatedTeller',
						\__( 'Automated teller', '' ),
					],
					[
						'BankOrCreditUnion',
						\__( 'Bank-or credit union', '' ),
					],
					[
						'InsuranceAgency',
						\__( 'Insurance agency', '' ),
					],
				],
			],
			[
				'FoodEstablishment',
				\__( 'Food establishment', '' ),
				[
					[
						'Bakery',
						\__( 'Bakery', '' ),
					],
					[
						'BarOrPub',
						\__( 'Bar or pub', '' ),
					],
					[
						'Brewery',
						\__( 'Brewery', '' ),
					],
					[
						'CafeOrCoffeeShop',
						\__( 'Cafe or coffee shop', '' ),
					],
					[
						'FastFoodRestaurant',
						\__( 'Fast food restaurant', '' ),
					],
					[
						'IceCreamShop',
						\__( 'Ice cream shop', '' ),
					],
					[
						'Restaurant',
						\__( 'Restaurant', '' ),
					],
					[
						'Winery',
						\__( 'Winery', '' ),
					],
					[
						'Distillery',
						\__( 'Distillery', '' ) . ' *',
					],
				],
			],
			[
				'GovernmentOffice',
				\__( 'Government office', '' ),
				[
					[
						'PostOffice',
						\__( 'Post office', '' ),
					],
				],
			],
			[
				'HealthAndBeautyBusiness',
				\__( 'Health and beauty business', '' ),
				[
					[
						'BeautySalon',
						\__( 'Beauty salon', '' ),
					],
					[
						'DaySpa',
						\__( 'Day spa', '' ),
					],
					[
						'HairSalon',
						\__( 'Hair salon', '' ),
					],
					[
						'HealthClub',
						\__( 'Health club', '' ),
					],
					[
						'NailSalon',
						\__( 'Nail salon', '' ),
					],
					[
						'TattooParlor',
						\__( 'Tattoo parlor', '' ),
					],
				],
			],
			[
				'HomeAndConstructionBusiness',
				\__( 'Home and construction business', '' ),
				[
					[
						'Electrician',
						\__( 'Electrician', '' ),
					],
					[
						'GeneralContractor',
						\__( 'General contractor', '' ),
					],
					[
						'HVACBusiness',
						/* translators: Keep it short! Or, keep it HVAC. */
						\_x( 'HVAC business', 'Heating, Ventalation, Air Conditioning', '' ),
					],
					[
						'Locksmith',
						\__( 'Locksmith', '' ),
					],
					[
						'MovingCompany',
						\__( 'Moving company', '' ),
					],
					[
						'Plumber',
						\__( 'Plumber', '' ),
					],
					[
						'RoofingContractor',
						\__( 'Roofing contractor', '' ),
					],
				],
			],
			[
				'InternetCafe',
				\__( 'Internet cafe', '' ),
				[],
			],
			[
				'LegalService',
				\__( 'Legal service', '' ),
				[
					[
						'Attorney',
						\__( 'Attorney', '' ),
					],
					[
						'Notary',
						\__( 'Notary', '' ),
					],
				],
			],
			[
				'Library',
				\__( 'Library', '' ),
				[],
			],
			[
				'LodgingBusiness',
				\__( 'Lodging business', '' ),
				[
					[
						'BedAndBreakfast',
						\__( 'Bed and breakfast', '' ),
					],
					[
						'Campground',
						\__( 'Campground', '' ),
					],
					[
						'Hostel',
						\__( 'Hostel', '' ),
					],
					[
						'Hotel',
						\__( 'Hotel', '' ),
					],
					[
						'Motel',
						\__( 'Motel', '' ),
					],
					[
						'Resort',
						\__( 'Resort', '' ),
					],
				],
			],
			// MORE FOUND HERE: http://schema.org/ProfessionalService
			[
				'RadioStation',
				\__( 'Radio station', '' ),
				[],
			],
			[
				'RealEstateAgent',
				\__( 'Real estate agent', '' ),
				[],
			],
			[
				'RecyclingCenter',
				\__( 'Recycling center', '' ),
				[],
			],
			[
				'SelfStorage',
				\__( 'Self storage', '' ),
				[],
			],
			[
				'ShoppingCenter',
				\__( 'Shopping center', '' ),
				[],
			],
			[
				'SportsActivityLocation',
				\__( 'Sports activity location', '' ),
				[
					[
						'BowlingAlley',
						\__( 'Bowling alley', '' ),
					],
					[
						'ExerciseGym',
						\__( 'Exercise gym', '' ),
					],
					[
						'GolfCourse',
						\__( 'Golf course', '' ),
					],
					[ //= DUPE
						'HealthClub',
						\__( 'Health club', '' ),
					],
					[
						'PublicSwimmingPool',
						\__( 'Public swimming pool', '' ),
					],
					[
						'SkiResort',
						\__( 'Ski resort', '' ),
					],
					[
						'SportsClub',
						\__( 'Sports club', '' ),
					],
					[
						'StadiumOrArena',
						\__( 'Stadium or arena', '' ),
					],
					[
						'TennisComplex',
						\__( 'Tennis complex', '' ),
					],
				],
			],
			[
				'Store',
				\__( 'Store', '' ),
				[
					[
						'AutoPartsStore',
						\__( 'Auto parts store', '' ),
					],
					[
						'BikeStore',
						\__( 'Bike store', '' ),
					],
					[
						'BookStore',
						\__( 'Book store', '' ),
					],
					[
						'ClothingStore',
						\__( 'Clothing store', '' ),
					],
					[
						'ComputerStore',
						\__( 'Computer store', '' ),
					],
					[
						'ConvenienceStore',
						\__( 'Convenience store', '' ),
					],
					[
						'DepartmentStore',
						\__( 'Department store', '' ),
					],
					[
						'ElectronicsStore',
						\__( 'Electronics store', '' ),
					],
					[
						'Florist',
						\__( 'Florist', '' ),
					],
					[
						'FurnitureStore',
						\__( 'Furniture store', '' ),
					],
					[
						'GardenStore',
						\__( 'Garden store', '' ),
					],
					[
						'GroceryStore',
						\__( 'Grocery store', '' ),
					],
					[
						'HardwareStore',
						\__( 'Hardware store', '' ),
					],
					[
						'HobbyShop',
						\__( 'Hobby shop', '' ),
					],
					[
						'HomeGoodsStore',
						\__( 'Home goods store', '' ),
					],
					[
						'JewelryStore',
						\__( 'Jewelry store', '' ),
					],
					[
						'LiquorStore',
						\__( 'Liquor store', '' ),
					],
					[
						'MensClothingStore',
						\__( 'Mens clothing store', '' ),
					],
					[
						'MobilePhoneStore',
						\__( 'Mobile phone store', '' ),
					],
					[
						'MovieRentalStore',
						\__( 'Movie rental store', '' ),
					],
					[
						'MusicStore',
						\__( 'Music store', '' ),
					],
					[
						'OfficeEquipmentStore',
						\__( 'Office equipment store', '' ),
					],
					[
						'OutletStore',
						\__( 'Outlet store', '' ),
					],
					[
						'PawnShop',
						\__( 'Pawn shop', '' ),
					],
					[
						'PetStore',
						\__( 'Pet store', '' ),
					],
					[
						'ShoeStore',
						\__( 'Shoe store', '' ),
					],
					[
						'SportingGoodsStore',
						\__( 'Sporting goods store', '' ),
					],
					[
						'TireShop',
						\__( 'Tire shop', '' ),
					],
					[
						'ToyStore',
						\__( 'Toy store', '' ),
					],
					[
						'WholesaleStore',
						\__( 'Wholesale store', '' ),
					],
				],
			],
			[
				'TelevisionStation',
				\__( 'Television station', '' ),
				[],
			],
			[
				'TouristInformationCenter',
				\__( 'Tourist information center', '' ),
				[],
			],
			[
				'TravelAgency',
				\__( 'Travel agency', '' ),
				[],
			],
		];
	}

	/**
	 * @see https://en.wikipedia.org/wiki/List_of_cuisines
	 */
	function get_cuisine_fields() {
		return [
			[
				'',
				'&mdash; ' . \__( 'No cuisine selected', '' ) . ' &mdash;',
				null, // No subtypes, duh.
			],
			[
				'African',
				\__( 'African', '' ),
				[

				],
			],
			[
				'American',
				\__( 'American', '' ),
				[

				],
			],
			[
				'Asian',
				\__( 'Asian', '' ),
				[

				],
			],
			[
				'Latin',
				\__( 'Latin', '' ),
				[

				],
			],
			[
				'European',
				\__( 'European', '' ),
				[

				],
			],
			[
				'Oceanic',
				\__( 'Oceanic', '' ),
				[

				],
			],
			[
				'Vegan',
				\__( 'Vegan', '' ),
				[

				],
			],
			[
				'Vegetarian',
				\__( 'Vegetarian', '' ),
				[

				],
			],
		];
	}

	function get_reservation_fields() {
		return [
			'acceptsReservations' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'string',
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'Accept Reservations', '' ),
					\__( 'Specify whether this department accepts reservations or explicitly doesn\'t.' ),
				],
				'_select' => $this->get_reservation_action_fields(),
			],
			// TODO: ReserveAction fields.... it needs to loop...
		];
	}

	function get_reservation_action_fields() {
		return [
			[
				'',
				'&mdash; ' . \__( 'Not specified', '' ) . ' &mdash;',
			],
			[
				0,
				\__( 'Accept reservations', '' ),
			],
			[
				1,
				\__( 'Don\'t accept reservations', '' ),
			],
		];
	}
}
