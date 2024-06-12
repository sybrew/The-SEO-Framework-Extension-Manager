<?php
/**
 * @package TSF_Extension_Manager\Extension\Local\Fields
 */

namespace TSF_Extension_Manager\Extension\Local;

\defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) or die;

/**
 * Local extension for The SEO Framework
 * Copyright (C) 2017 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

// phpcs:disable, WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned -- Nothing here can be aligned in the right mind.

/**
 * Holds fields template for package TSF_Extension_Manager\Extension\Local.
 *
 * Methods `set_instance()` and `get_instance()` are available through trait
 * `\TSF_Extension_Manager\Construct_Core_Static_Final_Instance`.
 *
 * @since 1.0.0
 * @access private
 * @uses trait \TSF_Extension_Manager\Construct_Core_Static_Final_Instance
 * @see TSF_Extension_Manager\Traits\Overload
 * @final Can't be extended.
 */
final class Fields {
	use \TSF_Extension_Manager\Construct_Core_Static_Final_Instance;

	/**
	 * Returns all department fields for form iteration.
	 *
	 * @TODO clean up the documentation. This was setup prior to creating the generator class.
	 * @since 1.0.0
	 * @link https://developers.google.com/search/docs/advanced/structured-data/local-business
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
						'_pattern' => '[0-9]*',
						'_desc' => [
							\__( 'Set number of departments', 'the-seo-framework-extension-manager' ),
							\__( 'Each department must have its own publicly recognizable name and type.', 'the-seo-framework-extension-manager' ),
							\__( 'For example, if a restaurant has a small shop inside or belonging to the restaurant, then set two departments.', 'the-seo-framework-extension-manager' ),
						],
						'_range' => [
							0, // min
							0, // max allowed per CPU instruction set.
							1, // step
						],
					],
				],
				'_iterator_title' => [
					\__( 'Main Department', 'the-seo-framework-extension-manager' ),
					/* translators: %d is department iteration number */
					\__( 'Department %d', 'the-seo-framework-extension-manager' ),
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
	 * @link https://developers.google.com/search/docs/advanced/structured-data/local-business
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
					\__( 'Select supported department type', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Choose a (sub)type that closely describes the business.', 'the-seo-framework-extension-manager' ),
						\__( '(Sub)types with an asterisk are pending support.', 'the-seo-framework-extension-manager' ),
					],
					vsprintf(
						'%s<br>%s',
						[
							\__( 'Select "Local Business" if the department type is not listed.', 'the-seo-framework-extension-manager' ),
							\__( 'Select "Department disabled" to disable this department.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Department name', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Fill in the name of the department accurately.', 'the-seo-framework-extension-manager' ),
						\__( 'For example, myMart and myMart Pharmacy.', 'the-seo-framework-extension-manager' ),
					],
					/* translators: backticks are markdown for code blocks! */
					\__( 'Include the store name with the department name in the following format: `{store name} {department name}`', 'the-seo-framework-extension-manager' ),
				],
				'_md' => true,
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
					\__( 'Department URL', 'the-seo-framework-extension-manager' ),
					[
						\__( 'The fully-qualified URL of this department.', 'the-seo-framework-extension-manager' ),
						\__( 'If this URL matches a page on this website, then this department data will be outputted there.', 'the-seo-framework-extension-manager' ),
						\__( 'Leave empty if no assigned page exists.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'For example, the contact page or homepage. It must be a working link and the department location must be described accurately on there.', 'the-seo-framework-extension-manager' ),
				],
			],
			'address' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Department address', 'the-seo-framework-extension-manager' ),
					\__( 'Fill in the exact address of the department.', 'the-seo-framework-extension-manager' ),
					\__( 'If this is not the main department, and this department has the same address as the main department, leave these fields empty.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Telephone number', 'the-seo-framework-extension-manager' ),
					\__( 'This phone number meant to be the primary contact method for customers.', 'the-seo-framework-extension-manager' ),
					/* translators: backticks are markdown for code blocks! */
					\__( 'Be sure to include the country code and area code in the phone number: `+15555555555`', 'the-seo-framework-extension-manager' ),
				],
				'_md' => true,
			],
			'openingHoursSpecification' => [
				'_default' => null,
				'_edit' => true,
				'_ret' => '',
				'_req' => false,
				'_type' => 'multi',
				'_desc' => [
					\__( 'Department opening hours', 'the-seo-framework-extension-manager' ),
					\__( 'Specify the hours during which the business location is open.', 'the-seo-framework-extension-manager' ),
					\__( 'Be sure to specify all days of the week.', 'the-seo-framework-extension-manager' ),
				],
				'_fields' => $this->get_opening_hours_fields(),
			],
			'priceRange' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'text',
				'_pattern' => '^\p{Sc}{1,4}$|\p{Sc}[0-9\.]*\s*-\s*\p{Sc}?(([1-9][0-9]*(\.{0,1}[0-9]+)?)|(0\.(0[1-9]+|[1-9][0-9]*)))', // This expects unicode support. https://regex101.com/r/N3f5lF/6
				'_desc' => [
					\__( 'Price range', 'the-seo-framework-extension-manager' ),
					[
						\__( 'The price range of the items or services this department provides.', 'the-seo-framework-extension-manager' ),
						/* translators: backticks are markdown for code blocks! */
						\__( 'Can be a range with absolute numbers such as `$1-5`, or `$10 - $50.95`.', 'the-seo-framework-extension-manager' ),
						/* translators: backticks are markdown for code blocks! */
						\__( 'Can be a relative price indication, where `$` means relatively inexpensive and `$$$$` extremely expensive.', 'the-seo-framework-extension-manager' ),
						/* translators: backticks are markdown for code blocks! */
						\__( 'All other currency symbols are accepted. Thousand separators are not allowed, only dots may be used to specify cents.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'Use a decimal point when working with cents; not a comma. Use a hyphen to specify a range. This field is seen as an indication, so the value may be off by a little.', 'the-seo-framework-extension-manager' ),
				],
				'_md' => true,
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
					\__( 'Image URL', 'the-seo-framework-extension-manager' ),
					\__( 'An image of the department or building.', 'the-seo-framework-extension-manager' ),
				],
			],
			'servesCuisine' => [
				'_default' => [],
				'_edit' => true,
				'_ret' => 's||array',
				'_req' => false,
				'_type' => 'selectmultia11y',
				'_desc' => [
					\__( 'Cuisine', 'the-seo-framework-extension-manager' ),
					\__( 'Select the types of cuisine the department serves.', 'the-seo-framework-extension-manager' ),
					\__( 'Multiple cuisines are allowed.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Menu URL', 'the-seo-framework-extension-manager' ),
					\__( 'The department menu URL.', 'the-seo-framework-extension-manager' ),
					\__( 'This must be a fully-qualified URL.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Reservations', 'the-seo-framework-extension-manager' ),
					\__( 'Department customers\' reservation specification.', 'the-seo-framework-extension-manager' ),
					\__( 'These fields are still being tested by Search Engines. Usage will likely yield no effect.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Orders', 'the-seo-framework-extension-manager' ),
					\__( 'Department customers\' order specification.', 'the-seo-framework-extension-manager' ),
					\__( 'These fields are still being tested by Search Engines. Usage will likely yield no effect.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Street address', 'the-seo-framework-extension-manager' ),
					'',
					\__( 'Street number, street name, and unit number (if applicable).', 'the-seo-framework-extension-manager' ),
				],
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
					\__( 'City, town, village', 'the-seo-framework-extension-manager' ),
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
					\__( 'State or province', 'the-seo-framework-extension-manager' ),
					\__( 'The region. For example, CA for California.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Postal or zip code', 'the-seo-framework-extension-manager' ),
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
					\__( 'Country', 'the-seo-framework-extension-manager' ),
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
				'_pattern' => '[0-9\.]*',
				'_range' => [
					-90,  // min
					90,   // max
					1e-7, // step
				],
				'_desc' => [
					\__( 'Latitude', 'the-seo-framework-extension-manager' ),
					'',
					\__( 'The geographic latitude.', 'the-seo-framework-extension-manager' ),
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
				'_pattern' => '[0-9\.]*',
				'_range' => [
					-180, // min
					180,  // max
					1e-7, // step
				],
				'_desc' => [
					\__( 'Longitude', 'the-seo-framework-extension-manager' ),
					'',
					\__( 'The geographic longitude.', 'the-seo-framework-extension-manager' ),
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
						'_pattern' => '[0-9]*',
						'_desc' => [
							\__( 'Number of opening hours', 'the-seo-framework-extension-manager' ),
							\__( 'When opening hours fluctuate, increase this number to specify more opening hours.', 'the-seo-framework-extension-manager' ),
							\__( 'Set to 0 or leave empty if unspecified.', 'the-seo-framework-extension-manager' ),
						],
						'_range' => [
							0,  // min
							50, // max
							1,  // step
						],
					],
				],
				'_iterator_title' => [
					/* translators: %d is opening hours iteration number */
					\__( 'Opening Hours %d', 'the-seo-framework-extension-manager' ),
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
	 * @TODO i18n.
	 *
	 * @return array
	 */
	private function get_country_items() {
		return [
			[
				'',
				'&mdash; ' . \__( 'No country selected', 'the-seo-framework-extension-manager' ) . ' &mdash;',
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
				'&mdash; ' . \__( 'Department disabled', 'the-seo-framework-extension-manager' ) . ' &mdash;',
				null, // No subtypes, doh.
			],
			[
				'LocalBusiness',
				\__( 'Local business', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'AnimalShelter',
				\__( 'Animal shelter', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'AutomotiveBusiness',
				\__( 'Automotive business', 'the-seo-framework-extension-manager' ),
				[
					[
						'AutoBodyShop',
						\__( 'Auto body shop', 'the-seo-framework-extension-manager' ),
					],
					[
						'AutoDealer',
						\__( 'Auto dealer', 'the-seo-framework-extension-manager' ),
					],
					[
						'AutoPartsStore',
						\__( 'Auto parts store', 'the-seo-framework-extension-manager' ),
					],
					[
						'AutoRental',
						\__( 'Auto rental', 'the-seo-framework-extension-manager' ),
					],
					[
						'AutoRepair',
						\__( 'Auto repair', 'the-seo-framework-extension-manager' ),
					],
					[
						'AutoWash',
						\__( 'Auto wash', 'the-seo-framework-extension-manager' ),
					],
					[
						'GasStation',
						\__( 'Gas station', 'the-seo-framework-extension-manager' ),
					],
					[
						'MotorcycleDealer',
						\__( 'Motorcycle dealer', 'the-seo-framework-extension-manager' ),
					],
					[
						'MotorcycleRepair',
						\__( 'Motorcycle repair', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'ChildCare',
				\__( 'Child care', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'MedicalBusiness',
				\__( 'Medical business', 'the-seo-framework-extension-manager' ),
				[
					// Names with asterisk are still under review by Schema.org and are currently Extensions...
					[
						'CommunityHealth',
						\__( 'Community health', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Dentist',
						\__( 'Dentist', 'the-seo-framework-extension-manager' ),
					],
					[
						'Dermatology',
						\__( 'Dermatology', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'DietNutrition',
						\__( 'Diet / Nutrition', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Emergency',
						\__( 'Emergency / Trauma', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Geriatric',
						\__( 'Geriatric', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Gynecologic',
						\__( 'Gynecologic', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'MedicalClinic',
						\__( 'Medical clinic', 'the-seo-framework-extension-manager' ),
					],
					[
						'Midwifery',
						\__( 'Midwifery', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Nursing',
						\__( 'Nursing', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Obstetric',
						\__( 'Obstetric', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Oncologic',
						\__( 'Oncologic', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Optician',
						\__( 'Optician', 'the-seo-framework-extension-manager' ),
					],
					[
						'Optometric',
						\__( 'Optometric', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Otolaryngologic',
						\__( 'Otolaryngologic', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Pediatric',
						\__( 'Pediatric', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Pharmacy',
						\__( 'Pharmacy', 'the-seo-framework-extension-manager' ),
					],
					[
						'Physician',
						\__( 'Physician', 'the-seo-framework-extension-manager' ),
					],
					[
						'Physiotherapy',
						\__( 'Physiotherapy', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'PlasticSurgery',
						\__( 'Plastic surgery', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Podiatric',
						\__( 'Podiatric', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'PrimaryCare',
						\__( 'Primary care', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'Psychiatric',
						\__( 'Psychiatric', 'the-seo-framework-extension-manager' ) . ' *',
					],
					[
						'PublicHealth',
						\__( 'Public health', 'the-seo-framework-extension-manager' ) . ' *',
					],
				],
			],
			[
				'DryCleaningOrLaundry',
				\__( 'Dry cleaning or laundry', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'EmergencyService',
				\__( 'Emergency service', 'the-seo-framework-extension-manager' ),
				[
					[
						'FireStation',
						\__( 'Fire station', 'the-seo-framework-extension-manager' ),
					],
					[
						'Hospital',
						\__( 'Hospital', 'the-seo-framework-extension-manager' ),
					],
					[
						'PoliceStation',
						\__( 'Police station', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'EmploymentAgency',
				\__( 'Employment agency', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'EntertainmentBusiness',
				\__( 'Entertainment business', 'the-seo-framework-extension-manager' ),
				[
					[
						'AdultEntertainment',
						\__( 'Adult entertainment', 'the-seo-framework-extension-manager' ),
					],
					[
						'AmusementPark',
						\__( 'Amusement park', 'the-seo-framework-extension-manager' ),
					],
					[
						'ArtGallery',
						\__( 'Art gallery', 'the-seo-framework-extension-manager' ),
					],
					[
						'Casino',
						\__( 'Casino', 'the-seo-framework-extension-manager' ),
					],
					[
						'ComedyClub',
						\__( 'Comedy club', 'the-seo-framework-extension-manager' ),
					],
					[
						'MovieTheater',
						\__( 'Movie theater', 'the-seo-framework-extension-manager' ),
					],
					[
						'NightClub',
						\__( 'Night club', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'FinancialService',
				\__( 'Financial service', 'the-seo-framework-extension-manager' ),
				[
					[
						'AccountingService',
						\__( 'Accounting service', 'the-seo-framework-extension-manager' ),
					],
					[
						'AutomatedTeller',
						\__( 'Automated teller', 'the-seo-framework-extension-manager' ),
					],
					[
						'BankOrCreditUnion',
						\__( 'Bank-or credit union', 'the-seo-framework-extension-manager' ),
					],
					[
						'InsuranceAgency',
						\__( 'Insurance agency', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'FoodEstablishment',
				\__( 'Food establishment', 'the-seo-framework-extension-manager' ),
				[
					[
						'Bakery',
						\__( 'Bakery', 'the-seo-framework-extension-manager' ),
					],
					[
						'BarOrPub',
						\__( 'Bar or pub', 'the-seo-framework-extension-manager' ),
					],
					[
						'Brewery',
						\__( 'Brewery', 'the-seo-framework-extension-manager' ),
					],
					[
						'CafeOrCoffeeShop',
						\__( 'Cafe or coffee shop', 'the-seo-framework-extension-manager' ),
					],
					[
						'FastFoodRestaurant',
						\__( 'Fast food restaurant', 'the-seo-framework-extension-manager' ),
					],
					[
						'IceCreamShop',
						\__( 'Ice cream shop', 'the-seo-framework-extension-manager' ),
					],
					[
						'Restaurant',
						\__( 'Restaurant', 'the-seo-framework-extension-manager' ),
					],
					[
						'Winery',
						\__( 'Winery', 'the-seo-framework-extension-manager' ),
					],
					[
						'Distillery',
						\__( 'Distillery', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'GovernmentOffice',
				\__( 'Government office', 'the-seo-framework-extension-manager' ),
				[
					[
						'PostOffice',
						\__( 'Post office', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'HealthAndBeautyBusiness',
				\__( 'Health and beauty business', 'the-seo-framework-extension-manager' ),
				[
					[
						'BeautySalon',
						\__( 'Beauty salon', 'the-seo-framework-extension-manager' ),
					],
					[
						'DaySpa',
						\__( 'Day spa', 'the-seo-framework-extension-manager' ),
					],
					[
						'HairSalon',
						\__( 'Hair salon', 'the-seo-framework-extension-manager' ),
					],
					[
						'HealthClub',
						\__( 'Health club', 'the-seo-framework-extension-manager' ),
					],
					[
						'NailSalon',
						\__( 'Nail salon', 'the-seo-framework-extension-manager' ),
					],
					[
						'TattooParlor',
						\__( 'Tattoo parlor', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'HomeAndConstructionBusiness',
				\__( 'Home and construction business', 'the-seo-framework-extension-manager' ),
				[
					[
						'Electrician',
						\__( 'Electrician', 'the-seo-framework-extension-manager' ),
					],
					[
						'GeneralContractor',
						\__( 'General contractor', 'the-seo-framework-extension-manager' ),
					],
					[
						'HVACBusiness',
						/* translators: Keep it short! Or, keep it HVAC. */
						\_x( 'HVAC business', 'Heating, Ventilation, Air Conditioning', 'the-seo-framework-extension-manager' ),
					],
					[
						'HousePainter',
						\__( 'House painter', 'the-seo-framework-extension-manager' ),
					],
					[
						'Locksmith',
						\__( 'Locksmith', 'the-seo-framework-extension-manager' ),
					],
					[
						'MovingCompany',
						\__( 'Moving company', 'the-seo-framework-extension-manager' ),
					],
					[
						'Plumber',
						\__( 'Plumber', 'the-seo-framework-extension-manager' ),
					],
					[
						'RoofingContractor',
						\__( 'Roofing contractor', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'InternetCafe',
				\__( 'Internet cafe', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'LegalService',
				\__( 'Legal service', 'the-seo-framework-extension-manager' ),
				[
					[
						'Attorney',
						\__( 'Attorney', 'the-seo-framework-extension-manager' ),
					],
					[
						'Notary',
						\__( 'Notary', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Library',
				\__( 'Library', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'LodgingBusiness',
				\__( 'Lodging business', 'the-seo-framework-extension-manager' ),
				[
					[
						'BedAndBreakfast',
						\__( 'Bed and breakfast', 'the-seo-framework-extension-manager' ),
					],
					[
						'Campground',
						\__( 'Campground', 'the-seo-framework-extension-manager' ),
					],
					[
						'Hostel',
						\__( 'Hostel', 'the-seo-framework-extension-manager' ),
					],
					[
						'Hotel',
						\__( 'Hotel', 'the-seo-framework-extension-manager' ),
					],
					[
						'Motel',
						\__( 'Motel', 'the-seo-framework-extension-manager' ),
					],
					[
						'Resort',
						\__( 'Resort', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'ProfessionalService',
				\__( 'Professional service', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'RadioStation',
				\__( 'Radio station', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'RealEstateAgent',
				\__( 'Real estate agent', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'RecyclingCenter',
				\__( 'Recycling center', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'SelfStorage',
				\__( 'Self storage', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'ShoppingCenter',
				\__( 'Shopping center', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'SportsActivityLocation',
				\__( 'Sports activity location', 'the-seo-framework-extension-manager' ),
				[
					[
						'BowlingAlley',
						\__( 'Bowling alley', 'the-seo-framework-extension-manager' ),
					],
					[
						'ExerciseGym',
						\__( 'Exercise gym', 'the-seo-framework-extension-manager' ),
					],
					[
						'GolfCourse',
						\__( 'Golf course', 'the-seo-framework-extension-manager' ),
					],
					[
						'PublicSwimmingPool',
						\__( 'Public swimming pool', 'the-seo-framework-extension-manager' ),
					],
					[
						'SkiResort',
						\__( 'Ski resort', 'the-seo-framework-extension-manager' ),
					],
					[
						'SportsClub',
						\__( 'Sports club', 'the-seo-framework-extension-manager' ),
					],
					[
						'StadiumOrArena',
						\__( 'Stadium or arena', 'the-seo-framework-extension-manager' ),
					],
					[
						'TennisComplex',
						\__( 'Tennis complex', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Store',
				\__( 'Store', 'the-seo-framework-extension-manager' ),
				[
					[
						'AutoPartsStore',
						\__( 'Auto parts store', 'the-seo-framework-extension-manager' ),
					],
					[
						'BikeStore',
						\__( 'Bike store', 'the-seo-framework-extension-manager' ),
					],
					[
						'BookStore',
						\__( 'Book store', 'the-seo-framework-extension-manager' ),
					],
					[
						'ClothingStore',
						\__( 'Clothing store', 'the-seo-framework-extension-manager' ),
					],
					[
						'ComputerStore',
						\__( 'Computer store', 'the-seo-framework-extension-manager' ),
					],
					[
						'ConvenienceStore',
						\__( 'Convenience store', 'the-seo-framework-extension-manager' ),
					],
					[
						'DepartmentStore',
						\__( 'Department store', 'the-seo-framework-extension-manager' ),
					],
					[
						'ElectronicsStore',
						\__( 'Electronics store', 'the-seo-framework-extension-manager' ),
					],
					[
						'Florist',
						\__( 'Florist', 'the-seo-framework-extension-manager' ),
					],
					[
						'FurnitureStore',
						\__( 'Furniture store', 'the-seo-framework-extension-manager' ),
					],
					[
						'GardenStore',
						\__( 'Garden store', 'the-seo-framework-extension-manager' ),
					],
					[
						'GroceryStore',
						\__( 'Grocery store', 'the-seo-framework-extension-manager' ),
					],
					[
						'HardwareStore',
						\__( 'Hardware store', 'the-seo-framework-extension-manager' ),
					],
					[
						'HobbyShop',
						\__( 'Hobby shop', 'the-seo-framework-extension-manager' ),
					],
					[
						'HomeGoodsStore',
						\__( 'Home goods store', 'the-seo-framework-extension-manager' ),
					],
					[
						'JewelryStore',
						\__( 'Jewelry store', 'the-seo-framework-extension-manager' ),
					],
					[
						'LiquorStore',
						\__( 'Liquor store', 'the-seo-framework-extension-manager' ),
					],
					[
						'MensClothingStore',
						\__( 'Mens clothing store', 'the-seo-framework-extension-manager' ),
					],
					[
						'MobilePhoneStore',
						\__( 'Mobile phone store', 'the-seo-framework-extension-manager' ),
					],
					[
						'MovieRentalStore',
						\__( 'Movie rental store', 'the-seo-framework-extension-manager' ),
					],
					[
						'MusicStore',
						\__( 'Music store', 'the-seo-framework-extension-manager' ),
					],
					[
						'OfficeEquipmentStore',
						\__( 'Office equipment store', 'the-seo-framework-extension-manager' ),
					],
					[
						'OutletStore',
						\__( 'Outlet store', 'the-seo-framework-extension-manager' ),
					],
					[
						'PawnShop',
						\__( 'Pawn shop', 'the-seo-framework-extension-manager' ),
					],
					[
						'PetStore',
						\__( 'Pet store', 'the-seo-framework-extension-manager' ),
					],
					[
						'ShoeStore',
						\__( 'Shoe store', 'the-seo-framework-extension-manager' ),
					],
					[
						'SportingGoodsStore',
						\__( 'Sporting goods store', 'the-seo-framework-extension-manager' ),
					],
					[
						'TireShop',
						\__( 'Tire shop', 'the-seo-framework-extension-manager' ),
					],
					[
						'ToyStore',
						\__( 'Toy store', 'the-seo-framework-extension-manager' ),
					],
					[
						'WholesaleStore',
						\__( 'Wholesale store', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'TelevisionStation',
				\__( 'Television station', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'TouristInformationCenter',
				\__( 'Tourist information center', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'TravelAgency',
				\__( 'Travel agency', 'the-seo-framework-extension-manager' ),
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
				\__( 'African', 'the-seo-framework-extension-manager' ),
				[
					[
						'North African',
						\__( 'North African', 'the-seo-framework-extension-manager' ),
						[
							[
								'Algerian',
								\__( 'Algerian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Egyptian',
								\__( 'Egyptian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Libyan',
								\__( 'Libyan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Mauritanian',
								\__( 'Mauritanian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Moroccan',
								\__( 'Moroccan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Sudanese',
								\__( 'Sudanese', 'the-seo-framework-extension-manager' ),
							],
							[
								'Tunisian',
								\__( 'Tunisian', 'the-seo-framework-extension-manager' ),
							],
						],
					],
					[
						'Horn of Africa',
						\__( 'Horn of Africa', 'the-seo-framework-extension-manager' ),
						[
							[
								'Djiboutian',
								\__( 'Djiboutian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Ethiopian',
								\__( 'Ethiopian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Eritrean',
								\__( 'Eritrean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Somali',
								\__( 'Somali', 'the-seo-framework-extension-manager' ),
							],
						],
					],
					[
						'East African',
						\__( 'East African', 'the-seo-framework-extension-manager' ),
						[
							[
								'Burundian',
								\__( 'Burundian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Kenyan',
								\__( 'Kenyan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Rwandan',
								\__( 'Rwandan', 'the-seo-framework-extension-manager' ),
							],
							[
								'South Sudanese',
								\__( 'South Sudanese', 'the-seo-framework-extension-manager' ),
							],
							[
								'Tanzanian',
								\__( 'Tanzanian', 'the-seo-framework-extension-manager' ),
								[
									[
										'Zanzibari',
										\__( 'Zanzibari', 'the-seo-framework-extension-manager' ),
									],
								],
							],
							[
								'Ugandan',
								\__( 'Ugandan', 'the-seo-framework-extension-manager' ),
							],
						],
					],
					[
						'Central African',
						\__( 'Central African', 'the-seo-framework-extension-manager' ),
						[
							[
								'Angolan',
								\__( 'Angolan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Cameroonian',
								\__( 'Cameroonian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Centrafrican',
								\__( 'Centrafrican', 'the-seo-framework-extension-manager' ),
							],
							[
								'Chadian',
								\__( 'Chadian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Congolese',
								\__( 'Congolese', 'the-seo-framework-extension-manager' ),
							],
							[
								'Equatorial Guinean',
								\__( 'Equatorial Guinean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Gabonese',
								\__( 'Gabonese', 'the-seo-framework-extension-manager' ),
							],
							[
								'São Toméan',
								\__( 'São Toméan', 'the-seo-framework-extension-manager' ),
							],
						],
					],
					[
						'Southern African',
						\__( 'Southern African', 'the-seo-framework-extension-manager' ),
						[
							[
								'Botswanan',
								\__( 'Botswanan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Comorian',
								\__( 'Comorian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Lesothoan',
								\__( 'Lesothoan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Malagasy',
								\__( 'Malagasy', 'the-seo-framework-extension-manager' ),
							],
							[
								'Malawian',
								\__( 'Malawian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Mauritian',
								\__( 'Mauritian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Mozambican',
								\__( 'Mozambican', 'the-seo-framework-extension-manager' ),
							],
							[
								'Namibian',
								\__( 'Namibian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Seychellois',
								\__( 'Seychellois', 'the-seo-framework-extension-manager' ),
							],
							[
								'South African',
								\__( 'South African', 'the-seo-framework-extension-manager' ),
							],
							[
								'Swazis',
								\__( 'Swazis', 'the-seo-framework-extension-manager' ),
							],
							[
								'Zambian',
								\__( 'Zambian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Zimbabwean',
								\__( 'Zimbabwean', 'the-seo-framework-extension-manager' ),
							],
						],
					],
					[
						'West African',
						\__( 'West African', 'the-seo-framework-extension-manager' ),
						[
							[
								'Benin',
								\__( 'Benin', 'the-seo-framework-extension-manager' ),
							],
							[
								'Burkinabé',
								\__( 'Burkinabé', 'the-seo-framework-extension-manager' ),
							],
							[
								'Cabo Verdean',
								\__( 'Cabo Verdean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Nigerien',
								\__( 'Nigerien', 'the-seo-framework-extension-manager' ),
							],
							[
								'Gambian',
								\__( 'Gambian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Ghanaian',
								\__( 'Ghanaian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Guinean',
								\__( 'Guinean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Bissau-Guinean',
								\__( 'Bissau-Guinean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Ivorian',
								\__( 'Ivorian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Liberian',
								\__( 'Liberian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Malian',
								\__( 'Malian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Nigerian',
								\__( 'Nigerian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Senegalese',
								\__( 'Senegalese', 'the-seo-framework-extension-manager' ),
							],
							[
								'Sierra Leonean',
								\__( 'Sierra Leonean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Togolese',
								\__( 'Togolese', 'the-seo-framework-extension-manager' ),
							],
						],
					],
				],
			],
			[
				'North American',
				\__( 'North American', 'the-seo-framework-extension-manager' ),
				[
					[
						'Canadian',
						\__( 'Canadian', 'the-seo-framework-extension-manager' ),
					],
					[
						'American',
						\__( 'American', 'the-seo-framework-extension-manager' ),
						[
							[
								'Alabamian',
								\__( 'Alabamian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Alaskan',
								\__( 'Alaskan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Arizonan',
								\__( 'Arizonan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Arkansan',
								\__( 'Arkansan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Californian',
								\__( 'Californian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Coloradan',
								\__( 'Coloradan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Connecticuter',
								\__( 'Connecticuter', 'the-seo-framework-extension-manager' ),
							],
							[
								'Delawarean',
								\__( 'Delawarean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Floridian',
								\__( 'Floridian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Georgian',
								\__( 'Georgian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Hawaiian',
								\__( 'Hawaiian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Idahoan',
								\__( 'Idahoan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Illinoisan',
								\__( 'Illinoisan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Hoosier',
								\__( 'Hoosier', 'the-seo-framework-extension-manager' ),
							],
							[
								'Iowan',
								\__( 'Iowan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Kansan',
								\__( 'Kansan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Kentuckian',
								\__( 'Kentuckian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Louisianian',
								\__( 'Louisianian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Mainer',
								\__( 'Mainer', 'the-seo-framework-extension-manager' ),
							],
							[
								'Marylander',
								\__( 'Marylander', 'the-seo-framework-extension-manager' ),
							],
							[
								'Massachusettsan',
								\__( 'Massachusettsan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Michiganian',
								\__( 'Michiganian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Minnesotan',
								\__( 'Minnesotan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Mississippian',
								\__( 'Mississippian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Missourian',
								\__( 'Missourian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Montanan',
								\__( 'Montanan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Nebraskan',
								\__( 'Nebraskan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Nevadan',
								\__( 'Nevadan', 'the-seo-framework-extension-manager' ),
							],
							[
								'New Hampshirite',
								\__( 'New Hampshirite', 'the-seo-framework-extension-manager' ),
							],
							[
								'New Jerseyan',
								\__( 'New Jerseyan', 'the-seo-framework-extension-manager' ),
							],
							[
								'New Mexican',
								\__( 'New Mexican', 'the-seo-framework-extension-manager' ),
							],
							[
								'New Yorker',
								\__( 'New Yorker', 'the-seo-framework-extension-manager' ),
							],
							[
								'North Carolinian',
								\__( 'North Carolinian', 'the-seo-framework-extension-manager' ),
							],
							[
								'North Dakotan',
								\__( 'North Dakotan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Ohioan',
								\__( 'Ohioan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Oklahoman',
								\__( 'Oklahoman', 'the-seo-framework-extension-manager' ),
							],
							[
								'Oregonian',
								\__( 'Oregonian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Pennsylvanian',
								\__( 'Pennsylvanian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Puerto Rican',
								\__( 'Puerto Rican', 'the-seo-framework-extension-manager' ),
							],
							[
								'Rhode Islander',
								\__( 'Rhode Islander', 'the-seo-framework-extension-manager' ),
							],
							[
								'South Carolinian',
								\__( 'South Carolinian', 'the-seo-framework-extension-manager' ),
							],
							[
								'South Dakotan',
								\__( 'South Dakotan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Tennessean',
								\__( 'Tennessean', 'the-seo-framework-extension-manager' ),
							],
							[
								'Texan',
								\__( 'Texan', 'the-seo-framework-extension-manager' ),
							],
							[
								'Utahn',
								\__( 'Utahn', 'the-seo-framework-extension-manager' ),
							],
							[
								'Vermonter',
								\__( 'Vermonter', 'the-seo-framework-extension-manager' ),
							],
							[
								'Virginian',
								\__( 'Virginian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Washingtonian',
								\__( 'Washingtonian', 'the-seo-framework-extension-manager' ),
							],
							[
								'West Virginian',
								\__( 'West Virginian', 'the-seo-framework-extension-manager' ),
							],
							[
								'Wisconsinite',
								\__( 'Wisconsinite', 'the-seo-framework-extension-manager' ),
							],
							[
								'Wyomingite',
								\__( 'Wyomingite', 'the-seo-framework-extension-manager' ),
							],
						],
					],
					[
						'Mexican',
						\__( 'Mexican', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Central American',
				\__( 'Central American', 'the-seo-framework-extension-manager' ),
				[
					[
						'Belizean',
						\__( 'Belizean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Costa Rican',
						\__( 'Costa Rican', 'the-seo-framework-extension-manager' ),
					],
					[
						'Salvadoran',
						\__( 'Salvadoran', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guatemalan',
						\__( 'Guatemalan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Honduran',
						\__( 'Honduran', 'the-seo-framework-extension-manager' ),
					],
					[
						'Nicaraguan',
						\__( 'Nicaraguan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Panamanian',
						\__( 'Panamanian', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'South American',
				\__( 'South American', 'the-seo-framework-extension-manager' ),
				[
					[
						'Brazilian',
						\__( 'Brazilian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Colombian',
						\__( 'Colombian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Argentine',
						\__( 'Argentine', 'the-seo-framework-extension-manager' ),
					],
					[
						'Peruvian',
						\__( 'Peruvian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Venezuelan',
						\__( 'Venezuelan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Chilean',
						\__( 'Chilean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Ecuadorian',
						\__( 'Ecuadorian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bolivian',
						\__( 'Bolivian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Paraguayan',
						\__( 'Paraguayan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Uruguayan',
						\__( 'Uruguayan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guyanese',
						\__( 'Guyanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Surinamese',
						\__( 'Surinamese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guyanese',
						\__( 'Guyanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guianan',
						\__( 'Guianan', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Caribbean',
				\__( 'Caribbean', 'the-seo-framework-extension-manager' ),
				[
					[
						'Anguillan',
						\__( 'Anguillan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Antiguan',
						\__( 'Antiguan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Aruban',
						\__( 'Aruban', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bahamian',
						\__( 'Bahamian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Barbadian',
						\__( 'Barbadian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Belizean',
						\__( 'Belizean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bermudian',
						\__( 'Bermudian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Caymanian',
						\__( 'Caymanian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Cuban',
						\__( 'Cuban', 'the-seo-framework-extension-manager' ),
					],
					[
						'Curaçaoan',
						\__( 'Curaçaoan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Dominican',
						\__( 'Dominican', 'the-seo-framework-extension-manager' ),
					],
					[
						'Grenadian',
						\__( 'Grenadian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guadeloupean',
						\__( 'Guadeloupean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guianan',
						\__( 'Guianan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guyanese',
						\__( 'Guyanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Haitian',
						\__( 'Haitian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Jamaican',
						\__( 'Jamaican', 'the-seo-framework-extension-manager' ),
					],
					[
						'Kittitian',
						\__( 'Kittitian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Martinican',
						\__( 'Martinican', 'the-seo-framework-extension-manager' ),
					],
					[
						'Montserratian',
						\__( 'Montserratian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Nevisian',
						\__( 'Nevisian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Saint Lucian',
						\__( 'Saint Lucian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Tobagonian',
						\__( 'Tobagonian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Trinidadian',
						\__( 'Trinidadian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Turks and Caicos Islanders',
						\__( 'Turks and Caicos Islanders', 'the-seo-framework-extension-manager' ),
					],
					[
						'Vincentian',
						\__( 'Vincentian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Virgin Islander',
						\__( 'Virgin Islander', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Asian',
				\__( 'Asian', 'the-seo-framework-extension-manager' ),
				[
					[
						'Afghan',
						\__( 'Afghan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Armenian',
						\__( 'Armenian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Azerbaijani',
						\__( 'Azerbaijani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bahraini',
						\__( 'Bahraini', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bangladeshi',
						\__( 'Bangladeshi', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bhutanese',
						\__( 'Bhutanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bruneian',
						\__( 'Bruneian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Burmese',
						\__( 'Burmese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Cambodian',
						\__( 'Cambodian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Chinese',
						\__( 'Chinese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Cypriot',
						\__( 'Cypriot', 'the-seo-framework-extension-manager' ),
					],
					[
						'Emirati',
						\__( 'Emirati', 'the-seo-framework-extension-manager' ),
					],
					[
						'Filipino',
						\__( 'Filipino', 'the-seo-framework-extension-manager' ),
					],
					[
						'Georgian',
						\__( 'Georgian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Indian',
						\__( 'Indian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Indonesian',
						\__( 'Indonesian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Iranian',
						\__( 'Iranian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Iraqi',
						\__( 'Iraqi', 'the-seo-framework-extension-manager' ),
					],
					[
						'Israeli',
						\__( 'Israeli', 'the-seo-framework-extension-manager' ),
					],
					[
						'Japanese',
						\__( 'Japanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Jordanian',
						\__( 'Jordanian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Kazakhstani',
						\__( 'Kazakhstani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Kuwaiti',
						\__( 'Kuwaiti', 'the-seo-framework-extension-manager' ),
					],
					[
						'Kyrgyzstani',
						\__( 'Kyrgyzstani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Lao',
						\__( 'Lao', 'the-seo-framework-extension-manager' ),
					],
					[
						'Lebanese',
						\__( 'Lebanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Malaysian',
						\__( 'Malaysian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Maldivian',
						\__( 'Maldivian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Mongolian',
						\__( 'Mongolian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Nepali',
						\__( 'Nepali', 'the-seo-framework-extension-manager' ),
					],
					[
						'North Korean',
						\__( 'North Korean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Omani',
						\__( 'Omani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Pakistani',
						\__( 'Pakistani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Palestinian',
						\__( 'Palestinian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Qatari',
						\__( 'Qatari', 'the-seo-framework-extension-manager' ),
					],
					[
						'Russian',
						\__( 'Russian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Saudi',
						\__( 'Saudi', 'the-seo-framework-extension-manager' ),
					],
					[
						'Singaporean',
						\__( 'Singaporean', 'the-seo-framework-extension-manager' ),
					],
					[
						'South Korean',
						\__( 'South Korean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Sri Lankan',
						\__( 'Sri Lankan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Syrian',
						\__( 'Syrian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Taiwanese',
						\__( 'Taiwanese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Tajikistani',
						\__( 'Tajikistani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Thai',
						\__( 'Thai', 'the-seo-framework-extension-manager' ),
					],
					[
						'Timorese',
						\__( 'Timorese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Turkish',
						\__( 'Turkish', 'the-seo-framework-extension-manager' ),
					],
					[
						'Turkmen',
						\__( 'Turkmen', 'the-seo-framework-extension-manager' ),
					],
					[
						'Uzbekistani',
						\__( 'Uzbekistani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Vietnamese',
						\__( 'Vietnamese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Yemeni',
						\__( 'Yemeni', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'European',
				\__( 'European', 'the-seo-framework-extension-manager' ),
				[
					[
						'Albanian',
						\__( 'Albanian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Andorran',
						\__( 'Andorran', 'the-seo-framework-extension-manager' ),
					],
					[
						'Austrian',
						\__( 'Austrian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Azerbaijani',
						\__( 'Azerbaijani', 'the-seo-framework-extension-manager' ),
					],
					[
						'Belarusian',
						\__( 'Belarusian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Belgian',
						\__( 'Belgian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bosnian',
						\__( 'Bosnian', 'the-seo-framework-extension-manager' ),
					],
					[
						'British',
						\__( 'British', 'the-seo-framework-extension-manager' ),
					],
					[
						'Bulgarian',
						\__( 'Bulgarian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Croatian',
						\__( 'Croatian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Czech',
						\__( 'Czech', 'the-seo-framework-extension-manager' ),
					],
					[
						'Danish',
						\__( 'Danish', 'the-seo-framework-extension-manager' ),
					],
					[
						'Dutch',
						\__( 'Dutch', 'the-seo-framework-extension-manager' ),
					],
					[
						'Estonian',
						\__( 'Estonian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Finnish',
						\__( 'Finnish', 'the-seo-framework-extension-manager' ),
					],
					[
						'French',
						\__( 'French', 'the-seo-framework-extension-manager' ),
					],
					[
						'German',
						\__( 'German', 'the-seo-framework-extension-manager' ),
					],
					[
						'Greek',
						\__( 'Greek', 'the-seo-framework-extension-manager' ),
					],
					[
						'Herzegovinian',
						\__( 'Herzegovinian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Hungarian',
						\__( 'Hungarian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Icelandic',
						\__( 'Icelandic', 'the-seo-framework-extension-manager' ),
					],
					[
						'Irish',
						\__( 'Irish', 'the-seo-framework-extension-manager' ),
					],
					[
						'Italian',
						\__( 'Italian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Kosovar',
						\__( 'Kosovar', 'the-seo-framework-extension-manager' ),
					],
					[
						'Latvian',
						\__( 'Latvian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Liechtensteiner',
						\__( 'Liechtensteiner', 'the-seo-framework-extension-manager' ),
					],
					[
						'Lithuanian',
						\__( 'Lithuanian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Luxembourg',
						\__( 'Luxembourg', 'the-seo-framework-extension-manager' ),
					],
					[
						'Macedonian',
						\__( 'Macedonian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Maltese',
						\__( 'Maltese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Moldovan',
						\__( 'Moldovan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Montenegrin',
						\__( 'Montenegrin', 'the-seo-framework-extension-manager' ),
					],
					[
						'Monégasque',
						\__( 'Monégasque', 'the-seo-framework-extension-manager' ),
					],
					[
						'Norwegian',
						\__( 'Norwegian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Polish',
						\__( 'Polish', 'the-seo-framework-extension-manager' ),
					],
					[
						'Portuguese',
						\__( 'Portuguese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Romanian',
						\__( 'Romanian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Sammarinese',
						\__( 'Sammarinese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Serbian',
						\__( 'Serbian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Slovak',
						\__( 'Slovak', 'the-seo-framework-extension-manager' ),
					],
					[
						'Slovenian',
						\__( 'Slovenian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Spanish',
						\__( 'Spanish', 'the-seo-framework-extension-manager' ),
					],
					[
						'Swedish',
						\__( 'Swedish', 'the-seo-framework-extension-manager' ),
					],
					[
						'Swiss',
						\__( 'Swiss', 'the-seo-framework-extension-manager' ),
					],
					[
						'Ukrainian',
						\__( 'Ukrainian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Vatican',
						\__( 'Vatican', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Oceanic',
				\__( 'Oceanic', 'the-seo-framework-extension-manager' ),
				[
					[
						'American Samoan',
						\__( 'American Samoan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Australian',
						\__( 'Australian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Cook Islander',
						\__( 'Cook Islander', 'the-seo-framework-extension-manager' ),
					],
					[
						'Micronesian',
						\__( 'Micronesian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Fijian',
						\__( 'Fijian', 'the-seo-framework-extension-manager' ),
					],
					[
						'French Polynesian',
						\__( 'French Polynesian', 'the-seo-framework-extension-manager' ),
					],
					[
						'Futunan',
						\__( 'Futunan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Guamanian',
						\__( 'Guamanian', 'the-seo-framework-extension-manager' ),
					],
					[
						'I-Kiribati',
						\__( 'I-Kiribati', 'the-seo-framework-extension-manager' ),
					],
					[
						'Marshallese',
						\__( 'Marshallese', 'the-seo-framework-extension-manager' ),
					],
					[
						'Nauruan',
						\__( 'Nauruan', 'the-seo-framework-extension-manager' ),
					],
					[
						'New Caledonian',
						\__( 'New Caledonian', 'the-seo-framework-extension-manager' ),
					],
					[
						'New Zealander',
						\__( 'New Zealander', 'the-seo-framework-extension-manager' ),
					],
					[
						'Niuean',
						\__( 'Niuean', 'the-seo-framework-extension-manager' ),
					],
					[
						'Norfolk Islander',
						\__( 'Norfolk Islander', 'the-seo-framework-extension-manager' ),
					],
					[
						'Northern Marianan',
						\__( 'Northern Marianan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Palauan',
						\__( 'Palauan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Papuan',
						\__( 'Papuan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Pitcairn Islander',
						\__( 'Pitcairn Islander', 'the-seo-framework-extension-manager' ),
					],
					[
						'Samoan',
						\__( 'Samoan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Solomon Islander',
						\__( 'Solomon Islander', 'the-seo-framework-extension-manager' ),
					],
					[
						'Tokelauan',
						\__( 'Tokelauan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Tongan',
						\__( 'Tongan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Tuvaluan',
						\__( 'Tuvaluan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Vanuatuan',
						\__( 'Vanuatuan', 'the-seo-framework-extension-manager' ),
					],
					[
						'Wallisian',
						\__( 'Wallisian', 'the-seo-framework-extension-manager' ),
					],
				],
			],
			[
				'Barbecue',
				\__( 'Barbecue', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'Fast Food',
				\__( 'Fast Food', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'Vegetarian',
				\__( 'Vegetarian', 'the-seo-framework-extension-manager' ),
				[],
			],
			[
				'Vegan',
				\__( 'Vegan', 'the-seo-framework-extension-manager' ),
				[],
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
					\__( 'Applied days', 'the-seo-framework-extension-manager' ),
					\__( 'Select the days from and to which the opening and closing hours specify to.', 'the-seo-framework-extension-manager' ),
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
					\__( 'State of department', 'the-seo-framework-extension-manager' ),
					\__( 'Set whether the department is open or closed on the applied days.', 'the-seo-framework-extension-manager' ),
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
						\__( 'Open', 'the-seo-framework-extension-manager' ),
					],
					[
						'1',
						\__( 'Open 24 hours', 'the-seo-framework-extension-manager' ),
					],
					[
						'2',
						\__( 'Closed', 'the-seo-framework-extension-manager' ),
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
					\__( 'Opening time', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Time when the business location opens.', 'the-seo-framework-extension-manager' ),
						\__( 'This time must be earlier than the closing time.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'Specify the local time.', 'the-seo-framework-extension-manager' ),
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
					\__( 'Closing time', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Time when the business location closes.', 'the-seo-framework-extension-manager' ),
						\__( 'This time must be later than the opening time.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'Specify the local time.', 'the-seo-framework-extension-manager' ),
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.openinghours.type' => 'open',
					],
				],
			],
			'scheduled' => [
				'_default' => null,
				'_edit'    => true,
				'_ret'     => 's',
				'_req'     => false,
				'_type'    => 'checkbox',
				'_desc'    => [
					\__( 'Scheduled', 'the-seo-framework-extension-manager' ),
					'',
					'',
				],
				'_check'   => [
					\__( 'Schedule these opening hours?', 'the-seo-framework-extension-manager' ),
				],
				'_data'    => [
					'is-type-listener'     => '1',
					'set-type-to-if-value' => [
						'enabled'  => '1',
						'disabled' => '0',
					],
					'showif-catcher'       => 'department.openinghours.scheduled',
				],
			],
			'validFrom' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'date',
				'_req' => true,
				'_type' => 'date',
				'_desc' => [
					\__( 'Valid from date', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Date from when this schedule starts.', 'the-seo-framework-extension-manager' ),
						\__( 'This date must be before the valid through date.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'Specify the local date.', 'the-seo-framework-extension-manager' ),
				],
				'_range' => [
					'2020-01-01', // min
					'2120-01-01', // max
					'', // step
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.openinghours.scheduled' => 'enabled',
					],
				],
			],
			'validThrough' => [
				'_default' => '',
				'_edit' => true,
				'_ret' => 'date',
				'_req' => true,
				'_type' => 'date',
				'_desc' => [
					\__( 'Valid through date', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Date through when this schedule ends.', 'the-seo-framework-extension-manager' ),
						\__( 'This date must be after the valid from date.', 'the-seo-framework-extension-manager' ),
					],
					\__( 'Specify the local date.', 'the-seo-framework-extension-manager' ),
				],
				'_range' => [
					'2020-01-02', // min
					'2120-01-02', // max
					'', // step
				],
				'_data' => [
					'is-showif-listener' => '1',
					'showif' => [
						'department.openinghours.scheduled' => 'enabled',
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
				\__( 'Monday', 'the-seo-framework-extension-manager' ),
			],
			[
				'Tuesday',
				\__( 'Tuesday', 'the-seo-framework-extension-manager' ),
			],
			[
				'Wednesday',
				\__( 'Wednesday', 'the-seo-framework-extension-manager' ),
			],
			[
				'Thursday',
				\__( 'Thursday', 'the-seo-framework-extension-manager' ),
			],
			[
				'Friday',
				\__( 'Friday', 'the-seo-framework-extension-manager' ),
			],
			[
				'Saturday',
				\__( 'Saturday', 'the-seo-framework-extension-manager' ),
			],
			[
				'Sunday',
				\__( 'Sunday', 'the-seo-framework-extension-manager' ),
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
					\__( 'Accept reservations', 'the-seo-framework-extension-manager' ),
					[
						\__( 'Specify whether this department accepts reservations or explicitly doesn\'t.', 'the-seo-framework-extension-manager' ),
						\__( 'The reservation action must be completed through the website, not through a phonecall.', 'the-seo-framework-extension-manager' ),
					],
				],
				'_select' => [
					[
						'',
						'&mdash; ' . \__( 'Not specified', 'the-seo-framework-extension-manager' ) . ' &mdash;',
					],
					[
						1,
						\__( 'Accept reservations', 'the-seo-framework-extension-manager' ),
					],
					[
						0,
						\__( 'Don\'t accept reservations', 'the-seo-framework-extension-manager' ),
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
					\__( 'Target specifications', 'the-seo-framework-extension-manager' ),
					\__( 'Specify where visitors can complete a reservation.', 'the-seo-framework-extension-manager' ),
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
				 *
				 * @see actionPlatform, which might allow redirecting (i.e. make it work)
				 * if an urlTemplate is specified.
				 */
				'_default' => '',
				'_edit' => true,
				'_ret' => 'url',
				'_req' => false,
				'_type' => 'url',
				'_desc' => [
					\__( 'Form location URL', 'the-seo-framework-extension-manager' ),
					\__( 'The location where visitors can perform a reservation action.', 'the-seo-framework-extension-manager' ),
				],
			],
			'inLanguage' => [
				'_default' => \get_bloginfo( 'language' ),
				'_edit' => true,
				'_ret' => 's',
				'_req' => false,
				'_type' => 'text', // TODO convert to select (or datalist) with language items.
				'_desc' => [
					\__( 'Form language', 'the-seo-framework-extension-manager' ),
					\__( 'Specify the main language code of the form.', 'the-seo-framework-extension-manager' ),
				],
				// This pattern is confusing for the user.
				// '_pattern' => '^((?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE))|(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang)))|(?:((?:[A-Za-z]{2,3}(?:-([A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?)|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-([A-Za-z]{4}))?(?:-([A-Za-z]{2}|[0-9]{3}))?(?:-([A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-([0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+))*)(?:-(x(?:-[A-Za-z0-9]{1,8})+))?$',
				// This pattern is quite restrictive, but will work with any language.
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
					\__( 'Form platforms', 'the-seo-framework-extension-manager' ),
					\__( 'Specify the supported web platforms', 'the-seo-framework-extension-manager' ),
					\__( 'For example, if the form URL redirects Android users, then don\'t select it.', 'the-seo-framework-extension-manager' ),
				],
				'_select' => [
					[
						'desktop',
						__( 'Desktop platforms', 'the-seo-framework-extension-manager' ),
					],
					[
						'ios',
						__( 'iOS platforms', 'the-seo-framework-extension-manager' ),
					],
					[
						'android',
						__( 'Android platforms', 'the-seo-framework-extension-manager' ),
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
				'_req' => true,
				'_type' => 'select',
				'_desc' => [
					\__( 'Reservation type', 'the-seo-framework-extension-manager' ),
					\__( 'Choose a type that describes the reservation.', 'the-seo-framework-extension-manager' ),
					\__( 'If unlisted, select "Reservation".', 'the-seo-framework-extension-manager' ),
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
				'_req' => true,
				'_type' => 'text',
				'_desc' => [
					\__( 'Reservation action name', 'the-seo-framework-extension-manager' ),
					\__( 'Describe the reservation, in a few words.', 'the-seo-framework-extension-manager' ),
					\__( 'For example: "Reserve table" or "Table for four at Restaurant Name".', 'the-seo-framework-extension-manager' ),
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
				'&mdash; ' . \__( 'Not specified', 'the-seo-framework-extension-manager' ) . ' &mdash;',
			],
			[
				'Reservation',
				\__( 'Reservation', 'the-seo-framework-extension-manager' ),
			],
			/*
			[
				'BusReservation',
				\__( 'Bus reservation', 'the-seo-framework-extension-manager' ),
			],
			[
				'EventReservation',
				\__( 'Event reservation', 'the-seo-framework-extension-manager' ),
			],
			[
				'FlightReservation',
				\__( 'Flight reservation', 'the-seo-framework-extension-manager' ),
			],
			*/
			[
				'FoodEstablishmentReservation',
				\__( 'Food establishment reservation', 'the-seo-framework-extension-manager' ),
			],
			/*
			[
				'LodgingReservation',
				\__( 'Lodging reservation', 'the-seo-framework-extension-manager' ),
			],
			[
				'RentalCarReservation',
				\__( 'Rental car reservation', 'the-seo-framework-extension-manager' ),
			],
			[
				'ReservationPackage',
				\__( 'Reservation package', 'the-seo-framework-extension-manager' ),
			],
			[
				'TaxiReservation',
				\__( 'Taxi reservation', 'the-seo-framework-extension-manager' ),
			],
			[
				'TrainReservation',
				\__( 'Train reservation', 'the-seo-framework-extension-manager' ),
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
					\__( 'Delivery method', 'the-seo-framework-extension-manager' ),
					\__( 'Specify how the goods and delivered to the customers.', 'the-seo-framework-extension-manager' ),
					\__( 'Select all that apply.', 'the-seo-framework-extension-manager' ),
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
				'&mdash; ' . \__( 'Not specified', 'the-seo-framework-extension-manager' ) . ' &mdash;',
			],
			[
				'pickup',
				\__( 'Pickup', 'the-seo-framework-extension-manager' ),
			],
			[
				'ownfleet',
				\__( 'Delivery through own fleet', 'the-seo-framework-extension-manager' ),
			],
			[
				'mail',
				\__( 'Delivery through mail', 'the-seo-framework-extension-manager' ),
			],
			[
				'freight',
				\__( 'Delivery through freight', 'the-seo-framework-extension-manager' ),
			],
			[
				'dhl',
				\__( 'Delivery through DHL', 'the-seo-framework-extension-manager' ),
			],
			[
				'federalexpress',
				\__( 'Delivery through FedEx', 'the-seo-framework-extension-manager' ),
			],
			[
				'ups',
				\__( 'Delivery through UPS', 'the-seo-framework-extension-manager' ),
			],
			[
				'download',
				\__( 'Delivery through download', 'the-seo-framework-extension-manager' ),
			],
		];
	}
}
