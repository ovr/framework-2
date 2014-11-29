<?php
namespace Brainwave\Contracts\Translator;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.4-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

/**
 * Translator
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Translator
{
    /**
     * Provide an array of information to use as translation data
     * for a provided language.
     *
     * @param array   $info     The array of information to use.
     * @param String  $language The language that the translations are written in. (e.g. 'en').
     * @param Boolean $merge    True or false depending on whether the array should be
     *                          merged with existing data for this language if there is any.
     * @return void
     */
    public function setTranslations(array $info, $language, $merge = false);

    /**
     * Get all translation information for a given language.
     *
     * @param  String $language The name of language that this information is made up of.
     * @return array            The language that the translations are written in. (e.g. 'en').
     */
    public function getTranslations($language);

    /**
     * Set the translation of a given term or phrase within a given language.
     *
     * @param String $orig        The original string.
     * @param String $translation The translation.
     * @param String $language    The language that the translation is written in. (e.g. 'en').
     * @return \Brainwave\Translator\Manager
     */
    public function setTranslation($orig, $translation, $language);

    /**
     * Get all translation information for a given language.
     *
     * @param  String $language The name of language that this information is made up of.
     * @return string|false            The language that the translations are written in. (e.g. 'en').
     */
    public function getTranslation($orig, $language = false);

    /**
     * Gets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @return String
     */
    public function getLocale();

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param String $defaultLang A string representing the default language to translate into. (e.g. 'en').
     *
     * @return self
     */
    public function setLocale($defaultLang);
}
