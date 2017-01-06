<?php

/**
 * Polylang community package for WP-CLI
 *
 * Helper global functions
 *
 * This is a temporary file, pending integration to Polylang API (api.php).
 * As this API uses global functions starting with pll_, we follow the convention.
 *
 * @package     WP-CLI
 * @subpackage  Polylang
 * @author      Sébastien Santoro aka Dereckson <dereckson@espace-win.org>
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD
 * @filesource
 */

 /**
 * Gets an array with the cached infomrmation about installed languages.
 *
 * @return array the installed languages, each item a PLL_Language object
 */
function pll_get_languages_list () {
	global $polylang;

	return $polylang->model->get_languages_list();
}

/**
 * Gets default language information
 *
 * @param string $language_code ISO 639 or locale code
 * @return array|null the default information for the the specified language, or null if it doesn't exist
 */
function pll_get_default_language_information($language_code) {
	global $polylang;
	require(PLL_SETTINGS_INC . '/languages.php');
	foreach ($languages as $language) {
		if ($language[0] == $language_code || $language[1] == $language_code) {
			$rtl = (count($language) > 3) && ($language[3] == 'rtl');
			return array(
				'code' => $language[0],
				'locale' => $language[1],
				'name' => $language[2],
				'rtl' => $rtl
			);
		}
	}
	return null;
}

/**
 * Determines if the specified language code is a valid one.
 *
 * @param string $language_code ISO 639 or locale code
 * @return bool true if the language code is valid; otherwise, false.
 */
function pll_is_valid_language_code($language_code) {
	return pll_get_default_language_information($language_code) !== null;
}

/**
 * Adds a language with the default locale, name and direction.
 *
 * @param string $language_code ISO 639 or locale code
 * @param int $language_order language order [optional]
 * @param int &$error_code the error code, or 0 if the operation is successful
 * @return bool true if the language has been added; false if an error has occured
 */
function pll_add_language($language_code, $language_order = 0, &$error_code = 0) {
	global $polylang;

	$adminModel = new PLL_Admin_Model($polylang->options);

	$info = pll_get_default_language_information($language_code);

	$args = array(
		'name' => $info['name'],
		'slug' => $info['code'],
		'locale' => $info['locale'],
		'rtl' => $info['rtl'] ? 1 : 0,
		'term_group' => $language_order
	);
	$error_code = $adminModel->add_language($args);
	return $error_code !== 0;
}

/**
 * Determines whether a language is currently installed.
 *
 * @param string $language_code The language slug
 * @return bool true if the language is installed; otherwise, false.
 */
function pll_is_language_installed($language_code) {
	global $polylang;

	$languages = $polylang->model->get_languages_list();
	foreach ($languages as $language) {
		if ($language->slug == $language_code) {
			return true;
		}
	}

	return false;
}
