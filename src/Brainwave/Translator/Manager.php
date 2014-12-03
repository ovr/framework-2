<?php
namespace Brainwave\Translator;

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

use Brainwave\Contracts\Translator\Translator as TranslatorContract;
use Brainwave\Filesystem\FileLoader;

/**
 * TranslatorManager
 *
 * the ability to translate strings.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Manager implements TranslatorContract
{
    /**
     * An array containing all of the translation information.
     *
     * @var array
     */
    protected $translations = [];

    /**
     * FileLoader instance
     *
     * @var \Brainwave\Filesystem\FileLoader
     */
    protected $loader;

    /**
     * [$validLangs description]
     *
     * @var array
     */
    protected $validLangs = [
        'af', //afrikaans
        'ar', //arabic
        'cz', //czech
        'de', //german
        'bg', //bulgarian
        'gr', //greek
        'en', //english
        'es', //espanol
        'ee', //estonian
        'il', //hebrew
        'it', //italian
        'mn', //mongolian
        'nl', //dutch
        'sq', //albainian
        'my', //malay
        'pl', //polskiy
        'ru', //russian
        'sk', //slovak
        'fa', //farsi
        'ja', //japan
        'tr', //turkish
        'vn', //vietnamese
        'cn', //chinese +
        'tw', //tradional Chinese (?)
        'kz', //Kazakh
        'ua', //ukrainian
        'lt', //lithuanian
        'fr', //french
        'ie', //irish
        'is', //icelandic
        'hr', //croatian
        'lv', //latvian
        'cy', //welsh
        'be', //belarusian
        'bs',  //bosnian
    ];

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    protected $defaultLang = 'en';

    /**
     * All added replacements
     *
     * @var array
     */
    protected $replacements;

    /**
     * [setLoader description]
     *
     * @param FileLoader $loader \Brainwave\Config\Fileloader
     *
     * @return \Brainwave\Translator\Manager
     */
    public function setLoader(FileLoader $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * [getLoader description]
     *
     * @return [type] [description]
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Provide an array of information to use as translation data
     * for a provided language.
     *
     * @param array   $info     The array of information to use.
     * @param String  $language The language that the translations are written in. (e.g. 'en').
     * @param Boolean $merge    True or false depending on whether the array should be
     *                          merged with existing data for this language if there is any.
     */
    public function setTranslations(array $info, $language, $merge = false)
    {
        //Validate lang
        $this->checkLang($language);

        if (isset($this->translations[$language]) && $merge) {
            $this->translations[$language] = array_merge(
                $this->translations[$language],
                $info
            );
        } else {
            $this->translations[$language] = $info;
        }
    }

    /**
     * Get all translation information for a given language.
     *
     * @param string $language The name of language that this information is made up of.
     *
     * @return array The language that the translations are written in. (e.g. 'en').
     */
    public function getTranslations($language)
    {
        //Validate lang
        $this->checkLang($language);

        if (isset($this->translations[$language])) {
            return $this->translations[$language];
        }

        return [];
    }

    /**
     * Set the translation of a given term or phrase within a given language.
     *
     * @param string $orig        The original string.
     * @param string $translation The translation.
     * @param string $language    The language that the translation is written in. (e.g. 'en').
     *
     * @return \Brainwave\Translator\Manager
     */
    public function setTranslation($orig, $translation, $language)
    {
        //Validate lang
        $this->checkLang($language);

        if (!isset($this->translations[$language])) {
            $this->translations[$language] = [];
        }

        $this->translations[$language][$orig] = $translation;

        return $this;
    }

    /**
     * Get the translation for a given string.
     *
     * @param string         $orig     The original string.
     * @param boolean|string $language The language that the translation is written in. (e.g. 'en').
     *
     * @return string|false The translated string.
     */
    public function getTranslation($orig, $language = false, $replacements = null)
    {
        if (!$language) {
            $language = $this->defaultLang;
        }

        //Validate lang
        $this->checkLang($language);

        if (isset($this->translations[$language][$orig])) {
            return $this->translations[$language][$orig];
        }

        return false;
    }

    /**
     * Description
     *
     * @param string $str
     * @param initer $count
     * @param string $language
     *
     * @return string
     */
    protected function plurals($str, $count = null, $language = null)
    {
        $lang  = explode("|", $str);

        if (null === $count) {
            return '';
        }

        switch ($language) {
            case 'af': //afrikaans, nplurals=2
                $s = (($count == 1) ? 0 :  2);

                return $lang[ $s ];
                break;
            case 'ar': //arabic, nplurals=6
                $s = (($count == 0) ? 0 : (($count == 1) ? 1 : (($count == 2) ? 2 : ((($count % 100 >= 3) && ($count % 100 <= 10)) ? 3 : ((($count % 100 >= 11) && ($count % 100 <= 99)) ? 4 : 5)))));

                return $lang[ $s ];
                break;

            case 'cz': //czech, nplurals=3
                $s = (($count == 1) ? '0' : ($count >= 2 && $count <= 4) ? 1 : 1);

                return $lang[ $s ];
                break;
            case 'de': //german
            case 'bg': //bulgarian
            case 'gr': //greek
            case 'en': //english
            case 'es': //espanol
            case 'ee': //estonian
            case 'il': //hebrew
            case 'it': //italian
            case 'mn': //mongolian
            case 'nl': //dutch
            case 'sq': //albainian
            case 'my': //malay
                       // nplurals=2;
                $s = (($count != 1) ? '0' : 1);

                return $lang[$s];
                break;
            case 'pl': //polskiy, nplurals=3
                $s = (($count == 1) ? 0 : ((($count%10 >= 2) && ($count%10 <= 4) && ($count%100<10 || $count%100 >= 20)) ? 1 : 2));

                return $lang[$s];
                break;
            case 'ru': //russian, nplurals=3
                $s = ((($count%10 == 1) && ($count%100 != 11)) ? '0' : ((($count%10 >= 2) && ($count%10 <= 4) && ($count%100<10 || $count%100 >= 20)) ? 1 : 2));

                return $lang[$s];
                break;
            case 'sk': //slovak, nplurals=3
                $s = (($count == 1) ? 1 : (($count >= 2 && $count <= 4) ? 1 : '0'));

                return $lang[$s];
                break;
            case 'fa': //farsi
            case 'ja': //japan
            case 'tr': //turkish
            case 'vn': //vietnamese
            case 'cn': //chinese +
            case 'tw': //tradional Chinese (?)
            case 'kz': //Kazakh
                       //nplurals=1
                $s = '0';

                return $lang[$s];
                break;
            case 'ua': //ukrainian, nplurals=3
                $s = (($count%10 == 1 && $count%100 != 11) ? '0' : ($count%10 >= 2 && $count%10 <= 4 && ($count%100<10 || $count%100 >= 20)) ? 1 : 1);

                return $lang[$s];
                break;
            case 'lt': //lithuanian, nplurals=3
                $s = (($count%10 == 1 && $count%100 != 11) ? '0' : ($count%10 >= 2 && ($count%100<10 || $count%100 >= 20)) ? 1 : 1);

                return $lang[$s];
                break;
            case 'fr': //french, nplurals=2
                $s = ($count > 1 ? '0' : 1);

                return $lang[$key.$s];
                break;
            case 'ie': //irish, nplurals=5;
                $s = (($count == 1) ? 0 : (($count == 2) ? 1 : (($count<7) ? 2 : (($count<11) ? 3 : 4))));

                return $lang[$s];
                break;
            case 'is' : //icelandic, nplurals=2;
            case 'hr': //croatian, nplurals=3;
                $s = ($count%10 != 1 || $count%100 == 11) ? 0 : 1;

                return $lang[$s];
                break;
            case 'lv': //latvian
                $s = (($count%10 == 1 && $count%100 != 11) ? 0 : (($count != 0) ? 1 : 2));

                return $lang[$s];
                break;
            case 'cy': //welsh, nplurals=4
                $s =  (($count == 1) ? 0 : (($count == 2) ? 1 : (($count != 8 && $count != 11) ? 2 : 3)));

                return $lang[$s];
                break;
            case 'be': //belarusian, nplurals=3
            case 'bs': //bosnian, nplurals=3
                $s =  (($count%10 == 1 && $count%100 != 11) ? 0 : (($count%10 >= 2 && $count%10 <= 4 && ($count%100<10 || $count%100 >= 20)) ? 1 : 2));

                return $lang[$s];
                break;
        }
    }

    /**
     * Load the given lang group.
     *
     * @param string $file
     * @param string $namespace
     * @param string $environment
     * @param string $group
     *
     * @return void
     */
    public function bind($file, $namespace = null, $environment = null, $group = null)
    {
        $validLangs = $this->validLangs;

        foreach ($validLangs as $validLang) {
            if (strpos($group, $validLang) !== false) {
                $language = $group;
            }
        }

        $lang = $this->getLoader()->load($file, $namespace, $environment, $group);

        $this->setTranslations($lang, $language, true);
    }

    /**
     * Description
     *
     * @param string $search
     * @param string $replacement
     *
     * @return Manager
     */
    public function addReplacement($search, $replacement)
    {
        $this->replacements[$search] = $replacement;

        return $this;
    }

    /**
     * Description
     *
     * @param string $search
     *
     * @return Manager
     *
     * @throws \Exception
     */
    public function removeReplacement($search)
    {
        if (!isset($this->replacements[$search])) {
            throw new \Exception("Replacement '$search' was not found.");
        }

        unset($this->replacements[$search]);

        return $this;
    }

    /**
     * Description
     *
     * @param string $message
     * @param array  $args
     *
     * @return string
     */
    private function applyReplacements($message, array $args = [])
    {
        $replacements = $this->replacements;

        foreach ($args as $countame => $value) {
            $replacements[$countame] = $value;
        }

        foreach ($replacements as $countame => $value) {
            if ($value !== false) {
                $message = preg_replace('~%'.$countame.'%~', $value, $message);
            }
        }

        return $message;
    }

    /**
     * Description
     *
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * Gets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->defaultLang;
    }

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param string $defaultLang A string representing the default language to translate into. (e.g. 'en').
     *
     * @return self
     */
    public function setLocale($defaultLang)
    {
        if (!empty($defaultLang)) {
            $this->checkLang($defaultLang);

            $this->defaultLang = $defaultLang;
        }

        return $this;
    }

    /**
     * Check if lang is valid
     *
     * @param string|boolean $checkLang
     *
     * @return boolean|null
     *
     * @throw \InvalidArgumentException
     */
    protected function checkLang($checkLang)
    {
        $validLangs = $this->validLangs;

        foreach ($validLangs as $vLang) {
            if ($vLang === $checkLang) {
                return true;
            }

            throw new \InvalidArgumentException('You selected a invalid lang '.'"'.$checkLang.'"');
        }
    }
}
