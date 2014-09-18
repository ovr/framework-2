<?php
namespace Brainwave\Translator\Interfaces;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

/**
 * TranslatorInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface TranslatorInterface
{
    public function setTranslations(array $info, $language, $merge = false);

    public function getTranslations($language);

    public function setTranslation($orig, $translation, $language);

    public function getTranslation($orig, $language = false);

    public function getLocale();

    public function setLocale($defaultLang);
}
