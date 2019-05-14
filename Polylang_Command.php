<?php

/**
 * Polylang community package for WP-CLI
 *
 * Polylang_Command class — `wp polylang` commands
 *
 * @package     WP-CLI
 * @subpackage  Polylang
 * @author      Sébastien Santoro aka Dereckson <dereckson@espace-win.org>
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD
 * @filesource
 */

if (!defined('WP_CLI')) {
    return;
}

require_once 'PolylangHelperFunctions.php';

/**
 * Implements polylang command, to interact with the Polylang plug-in.
 */
class Polylang_Command extends WP_CLI_Command {

    /**
     * Prints the languages currently available
     *
     * ## EXAMPLES
     *
     *     wp polylang languages
     *
     * @alias langs
     */
    function languages ($args, $assocArgs) {
        $languages = pll_get_languages_list();
        if (!count($languages)) {
            WP_CLI::success("Less than two languages are currently configurated.");
            return;
        }

        $default = pll_default_language();
        foreach ($languages as $language) {
            $line = "$language->slug — $language->name";

            if ($language->slug == $default) {
                $line .= ' [DEFAULT]';
            }

            WP_CLI::line($line);
        }
    }

    /**
     * Gets the site homepage URL in the specified language
     *
     * ## OPTIONS
     *
     * <language-code>
     * : The language to get the home URL to.
     *
     * ## EXAMPLES
     *
     *   wp polylang home
     *   wp polylang home fr
     *
     * @synopsis [<language-code>]
     */
    function home ($args, $assocArgs) {
        $lang = (count($args) == 1) ?  $args[0] : '';

        $url = pll_home_url($lang);
        WP_CLI::line($url);
    }

    /**
     * Gets a post or a term in the specified language
     *
     * ## OPTIONS
     *
     * <data-type>
     * : 'post' or 'term'
     *
     * <data-id>
     * : the ID of the object to get
     *
     * <language-count>
     * : the language (if omitted, will be returned in the default language)
     *
     * ## EXAMPLES
     *
     *   wp polylang get post 1 fr
     *
     * @synopsis <data-type> <data-id> [<language-code>]
     */
    function get($args, $assocArgs) {
        $lang = (count($args) == 2) ?  '' : $args[2];

        switch ($what = $args[0]) {
            case 'post':
            case 'term':
                $method = 'pll_get_' . $what;
                break;

            default:
                WP_CLI::error("Expected: wp polylang get <post or term> ..., not '$what'");
        }

        $id = $method($args[1], $lang);
        WP_CLI::line($id);
    }

    /**
     * Adds, gets information about or removes a language
     *
     * ## OPTIONS
     *
     * <operation>
     * : the language operation (add, info, del)
     *
     * <language-code>
     * : the language code
     *
     * <order>
     * : for add operation, indicates the order of the language
     *
     * ## EXAMPLES
     *
     *   wp polylang language add fr
     *   wp polylang language add nl 2
     *   wp polylang language info vec
     *   wp polylang language del vec
     *
     * @synopsis <operation> <language-code> [<order>]
     * @alias lang
     */
    function language ($args, $assocArgs) {
        $language_code = $args[1];
        $language_order = (count($args) == 3) ? $args[2] : 0;
        $language_info = pll_get_default_language_information($language_code);
        if ($language_info === null) {
             WP_CLI::error("$language_code isn't a valid language code.");
             return;
        }
        $language_installed = pll_is_language_installed($language_code);

        switch ($args[0]) {
            case 'info':
                WP_CLI::line('Code:      ' . $language_info['code']);
                WP_CLI::line('Locale     ' . $language_info['locale']);
                WP_CLI::line('Name:      ' . $language_info['name']);
                WP_CLI::line('Dir:       ' . $language_info['dir']);
                WP_CLI::line('Installed: ' . ($language_installed ? 'yes' : 'no'));
                break;

            case 'add':
                if ($language_installed) {
                    WP_CLI::warning("This language is already installed.");
                    return;
                }

                if (pll_add_language($language_code, $language_order, $error_code)) {
                    WP_CLI::success("Language added.");
                    return;
                }

                WP_CLI::error("Can't add the language.");
                break;

            case 'del':
                if (!$language_installed) {
                    WP_CLI::warning("This language isn't installed.");
                    return;
                }

                WP_CLI::error("Not implemented: del language");
                break;

            default:
                WP_CLI::error("Unknown command: polylang language $args[0]. Expected: add/del/info.");
        }
    }
}

WP_CLI::add_command('polylang', 'Polylang_Command');
WP_CLI::add_command('pll', 'Polylang_Command'); //alias for the users expecting to use the API shortname.
