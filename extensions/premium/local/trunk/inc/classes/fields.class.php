<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Fields
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

/**
 * Holds fields template for package TSF_Extension_Manager\Extension\Local.
 *
 * Methods `set_instance()` and `get_instance()` are available through trait
 * `\TSF_Extension_Manager\Construct_Core_Static_Final_Instance`.
 *
 * @since 1.0.0
 * @access private
 * @uses trait \TSF_Extension_Manager\Enclose_Core_Final
 * @uses trait \TSF_Extension_Manager\Construct_Core_Static_Final_Instance
 * @see TSF_Extension_Manager\Traits\Overload
 * @final Can't be extended.
 */
final class Fields {
	use \TSF_Extension_Manager\Enclose_Core_Final,
		\TSF_Extension_Manager\Construct_Core_Static_Final_Instance;

	/**
	 * Returns all department fields for form iteration.
	 *
	 * @TODO clean up the documentation. This was setup prior to creating the generator class.
	 * @since 1.0.0
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
	public function get_departments_fields() {
		return [
			'department' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'iterate_main',
				'_desc' => [],
				'_iterate_selector' => [
					'count' => [
						'_default' => 1,
						'_edit' => true,
						'_ret' => 'd',
						'_req' => false,
						'_type' => 'number',
						'_desc' => [
							\__( 'Set number of departments', '' ),
							\__( 'Each department must have its own publicly recognizable name and type.', '' ),
							\__( 'For example, if a restaurant has a small shop inside or belonging to the restaurant, then set two departments.', '' ),
						],
						'_range' => [
							0,
							'',
							1,
						],
					],
				],
				'_iterator_title' => [
					\__( 'Main Department', '' ),
					/* translators: %d is department iteration number */
					\__( 'Department %d', '' ),
				],
				'_iterator_title_dynamic' => [
					'single' => 'name',
				],
				'_fields' => $this->get_global_department_fields(),
			],
		];
	}

	/**
	 * Returns iteratable department fields.
	 *
	 * @TODO clean up documentation return value. It was set-up prior to the generation class.
	 * @since 1.0.0
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
	private function get_global_department_fields() {
		return [
			'@type' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'Select supported department type', '' ),
					[
						\__( 'Choose a (sub)type that closely describes the business.', '' ),
						\__( '(Sub)types with an asterisk are pending support.', '' ),
					],
					vsprintf(
						'%s<br>%s',
						[
							\__( 'Select "Local Business" if the department type is not listed.', '' ),
							\__( 'Select "Disabled" to disable this department.', '' ),
						]
					),
				],
				'_data' => [
					'is-type-listener' => '1',
					'set-type-to-if-value' => [
						'FoodEstablishment' => [
							'FoodEstablishment',
							'Bakery',
							'BarOrPub',
							'Brewery',
							'CafeOrCoffeeShop',
							'FastFoodRestaurant',
							'IceCreamShop',
							'Restaurant',
							'Winery',
							'Distillery',
						],
					],
					'showif-catcher' => 'department.type',
				],
				'_select' => $this->get_department_items(),
			],
			'name' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'Department name', '' ),
					[
						\__( 'Fill in the name of the department accurately.', '' ),
						\__( 'For example, myMart and myMart Pharmacy.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'Include the store name with the department name in the following format: <code>{store name} {department name}</code>', '' ),
				],
			],
			'@id' => [
				'_default' => null,
				'_edit' => false,
				'_ret' => 's',
				'_req' => true,
				'_type' => 'text',
			],
			'url' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false,
				'_type' => 'url',
				'_desc' => [
					\__( 'Department URL', '' ),
					[
						\__( 'The fully-qualified URL of this department.', '' ),
						\__( 'If this URL matches a page on this website, then this department data will be outputted there.', '' ),
						\__( 'Leave empty if no assigned page exists.', '' ),
					],
					\__( 'For example, the contact page or home page. It must be a working link and the department location must be described accurately on there.', '' ),
				],
			],
			'address' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Department address', '' ),
					\__( 'Fill in the exact address of the department.', '' ),
					\__( 'If this is not the main department, and this department has the same address as the main department, leave these fields empty.', '' ),
				],
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'action',
				],
				'_fields' => $this->get_address_fields() + $this->get_geo_fields(),
			],
			'telephone' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'tel',
				'_pattern' => '\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$',
				'_desc' => [
					\__( 'Telephone number', '' ),
					\__( 'This phone number meant to be the primary contact method for customers.', '' ),
					\__( 'Be sure to include the country code and area code in the phone number: <code>+15555555555</code>', '' ),
				],
			],
			'openingHoursSpecification' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Department opening hours', '' ),
					\__( 'Specify the hours during which the business location is open.' ),
					\__( 'Be sure to specify all days of the week.' ),
				],
				'_fields' => $this->get_opening_hours_fields(),
			],
			'image' => [
				'_default' => [
					'url' => '',
					'id' => '',
				],
				'_edit' => true,
				'_ret' => 'image',
				'_req' => true, // Must be true if RESTAURANT.
				'_type' => 'image',
				'_desc' => [
					\__( 'Image URL', '' ),
					\__( 'An image of the department or building.', '' ),
				],
			],
			'servesCuisine' => [
				'_default' => [],
				'_edit' => true,
				'_ret' => 's||array',
				'_req' => true,
				'_type' => 'selectmultia11y',
				'_desc' => [
					\__( 'Cuisine', '' ),
					\__( 'Provide the type of cuisine the department serves.', '' ),
					\__( 'This is mandatory for food establishments.', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.type' => 'FoodEstablishment',
					],
				],
				'_select' => $this->get_cuisine_items(),
			],
			'menu' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false,
				'_type' => 'url',
				'_desc' => [
					\__( 'Menu URL', '' ),
					\__( 'Department menu URL, if any.', '' ),
					\__( 'This is mandatory for food establishments.', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.type' => 'FoodEstablishment',
					],
				],
			],
			'reservations' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Reservations', '' ),
					\__( 'Department customers\' reservation specification.', '' ),
					\__( 'These fields are still being tested by Search Engines. Usage will likely yield no effect.', '' ),
				],
				/**
				 * TODO this is incorrect. Only the acceptsReservations should be for FoodEstablishment.
				 * However, this data type is still being prepared by Google. So, we're limiting it
				 * until we know more.
				 */
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.type' => 'FoodEstablishment',
					],
				],
				'_fields' => $this->get_reservation_fields(),
			],
			/*=
			  = The order action might require more price specifications over multiple order types.
			  = For this reason, I've temporarily disabled the fields.
			  = Also, it's in piloting phase. This means the fields don't yield any effect, whatsoever, anyway.
			'orders' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Orders', '' ),
					\__( 'Department customers\' order specification.', '' ),
					\__( 'These fields are still being tested by Search Engines. Usage will likely yield no effect.', '' ),
				],
				'_fields' => $this->get_order_fields(),
			],
			*/
		];
	}

	/**
	 * Returns the address components sub-fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_address_fields() {
		return [
			'streetAddress' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'Street address', '' ),
					'',
					\__( 'Street number, street name, and unit number (if applicable).', '' ),
				],
				'_pattern' => '^((([0-9\/-]+([\/-0-9A-Z]+)?(\s|(,\s)))([\u00a1-\uffffa-zA-Z\.\s]|[0-9_/-])+))|(([\u00a1-\uffffa-zA-Z\.\s]|[0-9_/-])+)((\s|(,\s))([0-9\/-]+([\/-0-9A-Z]+)?))$',
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => [ 'route', 'street_number' ],
				],
			],
			'addressLocality' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'City, town, village', '' ),
				],
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'locality',
				],
			],
			'addressRegion' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'text',
				'_desc' => [
					\__( 'State or province', '' ),
					\__( 'The region. For example, CA for California.' ),
				],
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'region',
				],
			],
			'postalCode' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'Postal or zip code', '' ),
				],
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'postal_code',
				],
			],
			'addressCountry' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => true,
				'_type' => 'select',
				'_desc' => [
					\__( 'Country', '' ),
				],
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'country',
				],
				'_select' => $this->get_country_items(),
			],
		];
	}

	/**
	 * Returns the geo coordinates sub-fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_geo_fields() {
		return [
			'latitude' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => '%.7F',
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
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'lat',
				],
			],
			'longitude' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => '%.7F',
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
				'_data' => [
					'geo-api' => true,
					'geo-api-component' => 'lng',
				],
			],
		];
	}

	/**
	 * Returns iteratable opening hour fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_opening_hours_fields() {
		return [
			'openingHours' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'iterate',
				'_desc' => [],
				'_iterate_selector' => [
					'count' => [
						'_default' => 0,
						'_edit' => true,
						'_ret' => '',
						'_req' => false,
						'_type' => 'number',
						'_desc' => [
							\__( 'Number of opening hours', '' ),
							\__( 'When opening hours fluctuate, change this number to specify more opening hours.', '' ),
							\__( 'Set to 0 or leave empty if unspecified.', '' ),
						],
						'_range' => [
							0,
							7,
							1,
						],
					],
				],
				'_iterator_title' => [
					/* translators: %d is opening hours iteration number */
					\__( 'Opening Hours %d', '' ),
				],
				'_iterator_title_dynamic' => [
					'plural' => 'dayOfWeek',
				],
				'_fields' => $this->get_opening_hours_action_fields(),
			],
		];
	}

	/**
	 * Returns the ISO 3166-1-Alpha-2 country list items.
	 *
	 * @since 1.0.0
	 * @see https://en.wikipedia.org/wiki/ISO_3166-1
	 *
	 * @return array
	 */
	private function get_country_items() {
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
	 * Returns the department types select deep-list.
	 *
	 * @since 1.0.0
	 * @see https://jsfiddle.net/xgk8osdc/4/ for EZ copy-paste i18n generator.
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
	private function get_department_items() {
		return [
			[
				'',
				'&mdash; ' . \__( 'Disabled', '' ) . ' &mdash;',
				null, // No subtypes, doh.
			],
			[
				'LocalBusiness',
				\__( 'Local business', '' ),
				[],
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
	 * Returns the cuisine types a11y select list.
	 *
	 * @since 1.0.0
	 * @see https://en.wikipedia.org/wiki/List_of_cuisines
	 *
	 * @return array
	 */
	private function get_cuisine_items() {
		return [
			[
				'African',
				\__( 'African', '' ),
				[
					[
						'North African',
						\__( 'North African', '' ),
						[
							[
								'Algerian',
								\__( 'Algerian', '' ),
							],
							[
								'Egyptian',
								\__( 'Egyptian', '' ),
							],
							[
								'Libyan',
								\__( 'Libyan', '' ),
							],
							[
								'Mauritanian',
								\__( 'Mauritanian', '' ),
							],
							[
								'Moroccan',
								\__( 'Moroccan', '' ),
							],
							[
								'Sadunese',
								\__( 'Sadunese', '' ),
							],
							[
								'Tunisian',
								\__( 'Tunisian', '' ),
							],
						],
					],
					[
						'Horn of Africa',
						\__( 'Horn of Africa', '' ),
						[
							[
								'Djiboutian',
								\__( 'Djiboutian', '' ),
							],
							[
								'Ethiopian',
								\__( 'Ethiopian', '' ),
							],
							[
								'Eritrean',
								\__( 'Eritrean', '' ),
							],
							[
								'Somali',
								\__( 'Somali', '' ),
							],
						],
					],
					[
						'East African',
						\__( 'East African', '' ),
						[
							[
								'Burundian',
								\__( 'Burundian', '' ),
							],
							[
								'Kenyan',
								\__( 'Kenyan', '' ),
							],
							[
								'Rwandan',
								\__( 'Rwandan', '' ),
							],
							[
								'South Sudanese',
								\__( 'South Sudanese', '' ),
							],
							[
								'Tanzanian',
								\__( 'Tanzanian', '' ),
								[
									[
										'Zanzibari',
										\__( 'Zanzibari', '' ),
									],
								],
							],
							[
								'Ugandan',
								\__( 'Ugandan', '' ),
							],
						],
					],
					[
						'Central African',
						\__( 'Central African', '' ),
						[
							[
								'Angolan',
								\__( 'Angolan', '' ),
							],
							[
								'Cameroonian',
								\__( 'Cameroonian', '' ),
							],
							[
								'Centrafrican',
								\__( 'Centrafrican', '' ),
							],
							[
								'Chadian',
								\__( 'Chadian', '' ),
							],
							[
								'Congolese',
								\__( 'Congolese', '' ),
							],
							[
								'Equatorial Guinean',
								\__( 'Equatorial Guinean', '' ),
							],
							[
								'Gabonese',
								\__( 'Gabonese', '' ),
							],
							[
								'São Toméan',
								\__( 'São Toméan', '' ),
							],
						],
					],
					[
						'Southern African',
						\__( 'Southern African', '' ),
						[
							[
								'Botswanan',
								\__( 'Botswanan', '' ),
							],
							[
								'Comorian',
								\__( 'Comorian', '' ),
							],
							[
								'Lesothoan',
								\__( 'Lesothoan', '' ),
							],
							[
								'Malagasy',
								\__( 'Malagasy', '' ),
							],
							[
								'Malawian',
								\__( 'Malawian', '' ),
							],
							[
								'Mauritian',
								\__( 'Mauritian', '' ),
							],
							[
								'Mozambican',
								\__( 'Mozambican', '' ),
							],
							[
								'Namibian',
								\__( 'Namibian', '' ),
							],
							[
								'Seychellois',
								\__( 'Seychellois', '' ),
							],
							[
								'South African',
								\__( 'South African', '' ),
							],
							[
								'Swazis',
								\__( 'Swazis', '' ),
							],
							[
								'Zambian',
								\__( 'Zambian', '' ),
							],
							[
								'Zimbabwean',
								\__( 'Zimbabwean', '' ),
							],
						],
					],
					[
						'West African',
						\__( 'West African', '' ),
						[
							[
								'Benin',
								\__( 'Benin', '' ),
							],
							[
								'Burkinabé',
								\__( 'Burkinabé', '' ),
							],
							[
								'Cabo Verdean',
								\__( 'Cabo Verdean', '' ),
							],
							[
								'Nigerien',
								\__( 'Nigerien', '' ),
							],
							[
								'Gambian',
								\__( 'Gambian', '' ),
							],
							[
								'Ghanaian',
								\__( 'Ghanaian', '' ),
							],
							[
								'Guinean',
								\__( 'Guinean', '' ),
							],
							[
								'Bissau-Guinean',
								\__( 'Bissau-Guinean', '' ),
							],
							[
								'Ivorian',
								\__( 'Ivorian', '' ),
							],
							[
								'Liberian',
								\__( 'Liberian', '' ),
							],
							[
								'Malian',
								\__( 'Malian', '' ),
							],
							[
								'Nigerian',
								\__( 'Nigerian', '' ),
							],
							[
								'Senegalese',
								\__( 'Senegalese', '' ),
							],
							[
								'Sierra Leonean',
								\__( 'Sierra Leonean', '' ),
							],
							[
								'Togolese',
								\__( 'Togolese', '' ),
							],
						],
					],
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

	/**
	 * Returns the opening hours multi-fields list.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_opening_hours_action_fields() {
		return [
			'dayOfWeek' => [
				'_default' => [],
				'_edit' => true,
				'_ret' => 'array',
				'_req' => true,
				'_type' => 'selectmultia11y',
				'_display' => 'row',
				'_desc' => [
					\__( 'Applied days', '' ),
					\__( 'Select the days from and to which the opening and closing hours specify to.', '' ),
				],
				'_select' => $this->get_days_fields(),
			],
			'isOpen' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'b',
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'State of department', '' ),
					\__( 'Set whether the department is open or closed on the applied days.', '' ),
				],
				'_data' => [
					'is-type-listener' => '1',
					'set-type-to-if-value' => [
						'open' => '0',
						'open24' => '1',
						'closed' => '2',
					],
					'showif-catcher' => 'department.openinghours.type',
				],
				'_select' => [
					[
						'0',
						\__( 'Open', '' ),
					],
					[
						'1',
						\__( 'Open 24 hours', '' ),
					],
					[
						'2',
						\__( 'Closed', '' ),
					],
				],
			],
			'opens' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'time',
				'_req' => true,
				'_type' => 'time',
				'_desc' => [
					\__( 'Opening time', '' ),
					[
						\__( 'Time when the business location opens.', '' ),
						\__( 'This time must be earlier than the closing time.', '' ),
					],
					\__( 'Specify the local time.', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.openinghours.type' => 'open',
					],
				],
			],
			'closes' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'time',
				'_req' => true,
				'_type' => 'time',
				'_desc' => [
					\__( 'Closing time', '' ),
					[
						\__( 'Time when the business location closes.', '' ),
						\__( 'This time must be later than the opening time.', '' ),
					],
					\__( 'Specify the local time.', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.openinghours.type' => 'open',
					],
				],
			],
		];
	}

	/**
	 * Returns the days select list.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_days_fields() {
		return [
			[
				'Monday',
				\__( 'Monday', '' ),
			],
			[
				'Tuesday',
				\__( 'Tuesday', '' ),
			],
			[
				'Wednesday',
				\__( 'Wednesday', '' ),
			],
			[
				'Thursday',
				\__( 'Thursday', '' ),
			],
			[
				'Friday',
				\__( 'Friday', '' ),
			],
			[
				'Saturday',
				\__( 'Saturday', '' ),
			],
			[
				'Sunday',
				\__( 'Sunday', '' ),
			],
		];
	}

	/**
	 * Returns the reservation fields multi-fields list.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_reservation_fields() {
		return [
			'acceptsReservations' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => 'u',
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'Accept reservations', '' ),
					[
						\__( 'Specify whether this department accepts reservations or explicitly doesn\'t.', '' ),
						\__( 'The reservation action must be completed through the website, not through a phonecall.', '' ),
					],
				],
				'_select' => [
					[
						'',
						'&mdash; ' . \__( 'Not specified', '' ) . ' &mdash;',
					],
					[
						1,
						\__( 'Accept reservations', '' ),
					],
					[
						0,
						\__( 'Don\'t accept reservations', '' ),
					],
				],
				'_data' => [
					'is-type-listener' => '1',
					'set-type-to-if-value' => [
						'accept' => '1',
					],
					'showif-catcher' => 'department.acceptsReservations.type',
				],
			],
		] + $this->get_reservation_result_fields() + $this->get_reservation_action_fields();
	}

	/**
	 * Returns the reservation action sub-fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_reservation_action_fields() {
		return [
			'target' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Target specifications', '' ),
					\__( 'Specify where the user can complete a reservation.', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.acceptsReservations.type' => 'accept',
					],
				],
				'_fields' => $this->get_reservation_target_fields(),
			],
		];
	}

	/**
	 * Returns the reservation target sub-fields.
	 *
	 * @TODO specify actionPlatform, currently, this is undocumented by Schema.org
	 * @TODO allow urlTemplate. For now, it's too advanced to explain and yields no
	 * additional benefit to 99.9% of users within WordPress.
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_reservation_target_fields() {
		return [
			'url' => [
				/**
				 * We could also do urlTemplate, but that's a bit too advanced.
				 * @see actionPlatform, which might allow redirecting (i.e. make it work)
				 * if an urlTemplate is specified.
				 */
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false,
				'_type' => 'url',
				'_desc' => [
					\__( 'Form URL', '' ),
					\__( 'The location where the visitor can perform a reservation action.', '' ),
				],
			],
			'inLanguage' => [
				'_default' => \get_bloginfo( 'language' ),
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'text', // TODO convert to select (or datalist) with language items.
				'_desc' => [
					\__( 'Form language', '' ),
					\__( 'Specify the main language code of the form.', '' ),
				],
				//* This pattern is confusing for the user.
				// '_pattern' => '^((?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?:((?:[A-Za-z]{2,3}(?:-([A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-([A-Za-z]{4}))?(?:-([A-Za-z]{2}|[0-9]{3}))?(?:-([A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-([0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*)(?:-(x(?:-[A-Za-z0-9]{1,8})+))?$',
				//* This pattern is quite restrictive, but will work with any language.
				'_pattern' => '^[a-zA-Z]{2,3}((-([a-zA-Z]{2,4})-([a-zA-Z]{2,3}))|(-[a-zA-Z]{2,3})|(-[0-9]{3}))?$',
			],
			/*== These platforms are not specified on Schema.org, Let's omit them for now until they figure out what to do with it.
			'actionPlatform' => [
				'_default' => [],
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => 'selectmultia11y',
				'_desc' => [
					\__( 'Form platforms', '' ),
					\__( 'Specify the supported web platforms', '' ),
					\__( 'For example, if the form URL redirects Android users, then don\'t select it.', '' ),
				],
				'_select' => [
					[
						'desktop',
						__( 'Desktop platforms', '' ),
					],
					[
						'ios',
						__( 'iOS platforms', '' ),
					],
					[
						'android',
						__( 'Android platforms', '' ),
					],
				],
			],*/
		];
	}

	/**
	 * Returns the reservation fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_reservation_result_fields() {
		return [
			'@type' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'select',
				'_desc' => [
					\__( 'Reservation type', '' ),
					\__( 'Choose a type that describes the reservation.', '' ),
					\__( 'If unlisted, select "Reservation".', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.acceptsReservations.type' => 'accept',
					],
				],
				'_select' => $this->get_reservation_type_items(),
			],
			'name' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'text',
				'_desc' => [
					\__( 'Reservation action name', '' ),
					\__( 'Describe the reservation, in a few words.', '' ),
					\__( 'For example: "Reserve table" or "Table for four at Restaurant Name".', '' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.acceptsReservations.type' => 'accept',
					],
				],
			],
			/*= TODO PLACEHOLDER @see http://schema.org/Person ...Redundant?
			'provider' => [
				'_default' => '',
				'_edit' => false,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.acceptsReservations.type' => '1',
					],
				],
				'_fields' => [],
			],
			*/
		];
	}

	/**
	 * Returns the reservation types select list.
	 *
	 * @TODO allow other fields. Currently, it's limited to FoodEstablishment.
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_reservation_type_items() {
		return [
			[
				'',
				'&mdash; ' . \__( 'Not specified', '' ) . ' &mdash;',
			],
			[
				'Reservation',
				\__( 'Reservation', '' ),
			],
			/*
			[
				'BusReservation',
				\__( 'Bus reservation', '' ),
			],
			[
				'EventReservation',
				\__( 'Event reservation', '' ),
			],
			[
				'FlightReservation',
				\__( 'Flight reservation', '' ),
			],
			*/
			[
				'FoodEstablishmentReservation',
				\__( 'Food establishment reservation', '' ),
			],
			/*
			[
				'LodgingReservation',
				\__( 'Lodging reservation', '' ),
			],
			[
				'RentalCarReservation',
				\__( 'Rental car reservation', '' ),
			],
			[
				'ReservationPackage',
				\__( 'Reservation package', '' ),
			],
			[
				'TaxiReservation',
				\__( 'Taxi reservation', '' ),
			],
			[
				'TrainReservation',
				\__( 'Train reservation', '' ),
			],
			*/
		];
	}

	/**
	 * Returns the order fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_order_fields() {
		return [
			'deliveryMethod' => [
				'_default' => [],
				'_edit' => true,
				'_ret' => 'array',
				'_req' => false,
				'_type' => 'selectmultia11y',
				'_desc' => [
					\__( 'Delivery method', '' ),
					\__( 'Specify how the goods and delivered to the customers.', '' ),
					\__( 'Select all that apply.', '' ),
				],
				'_select' => $this->get_order_method_items(),
			],
		]; //+ $this->get_order_price_fields() + $this->get_order_action_fields(); <-- should define priceMin and priceMax
	}

	/**
	 * Returns the order methods select list.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_order_method_items() {
		return [
			[
				'',
				'&mdash; ' . \__( 'Not specified', '' ) . ' &mdash;',
			],
			[
				'pickup',
				\__( 'Pickup', '' ),
			],
			[
				'ownfleet',
				\__( 'Delivery through own fleet', '' ),
			],
			[
				'mail',
				\__( 'Delivery through mail', '' ),
			],
			[
				'freight',
				\__( 'Delivery through freight', '' ),
			],
			[
				'dhl',
				\__( 'Delivery through DHL', '' ),
			],
			[
				'federalexpress',
				\__( 'Delivery through FedEx', '' ),
			],
			[
				'ups',
				\__( 'Delivery through UPS', '' ),
			],
			[
				'download',
				\__( 'Delivery through download', '' ),
			],
		];
	}
}
