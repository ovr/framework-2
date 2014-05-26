<?php
namespace Brainwave\Support\Translator;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\Translator\Interfaces\TranslatorInterface;

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
class TranslatorManager implements TranslatorInterface
{
    /**
     * An array containing all of the translation information.
     *
     * @var array
     */
    protected $translations = array();

    /**
     * [$validLangs description]
     * @var array
     */
    protected $validLangs = array(
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
        'bs'  //bosnian
    );

    /**
     * A string dictating the default language to translate into. (e.g. 'en').
     *
     * @var String
     */
    protected $defaultLang = 'en';

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
     * @param  String $language The name of language that this information is made up of.
     * @return array            The language that the translations are written in. (e.g. 'en').
     */
    public function getTranslations($language)
    {
        //Validate lang
        $this->checkLang($language);

        if (isset($this->translations[$language])) {
            return $this->translations[$language];
        }
        return array();
    }

    /**
     * Set the translation of a given term or phrase within a given language.
     *
     * @param String $orig        The original string.
     * @param String $translation The translation.
     * @param String $language    The language that the translation is written in. (e.g. 'en').
     */
    public function setTranslation($orig, $translation, $language)
    {
        //Validate lang
        $this->checkLang($language);

        if (!isset($this->translations[$language])) {
            $this->translations[$language] = array();
        }

        $this->translations[$language][$orig] = $translation;
    }

    /**
     * Get the translation for a given string.
     *
     * @param  String         $orig     The original string.
     * @param  boolean|String $language The language that the translation is written in. (e.g. 'en').
     *
     * @return String The translated string.
     */
    public function getTranslation($orig, $language = false)
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
     * @param type $str
     * @param type $n
     * @param type $lang_id
     * @return type
     */
    protected function plurals($str, $n = null, $lang_id = null) {

        $lang  = explode("|", $str);

        if (null === $n) {
            return '';
        }

        switch ($lang_id) {
            case 'af': //afrikaans, nplurals=2
                    $s = ( ($n==1) ? 0 :  2);
                    $localized = $lang[ $s ];
            break;
            case 'ar': //arabic, nplurals=6
                    $s = ( ($n== 0) ? 0 : ( ($n==1) ? 1 : ( ($n==2) ? 2 : ( ( ($n % 100 >= 3) && ($n % 100 <= 10) ) ? 3 : ( ( ($n % 100 >= 11) && ($n % 100 <= 99) ) ? 4 : 5 ) ) ) ) );
                    $localized = $lang[ $s ];
            break;

            case 'cz': //czech, nplurals=3
                    $s = ( ($n==1) ? '0' : ($n>=2 && $n<=4) ? 1 : 1 );
                    $localized = $lang[ $s ];
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
                    $s = ( ($n != 1) ? '0' : 1 );
                    $localized = $lang[ $s ];
            break;
            case 'pl': //polskiy, nplurals=3
                    $s = (($n == 1) ? 0 : (( ($n%10>=2) && ($n%10<=4) && ($n%100<10 || $n%100>=20) ) ? 1 : 2 ));
                    $localized = $lang[ $s ];
            break;
            case 'ru': //russian, nplurals=3
                    $s = ( (($n%10==1) && ($n%100!=11)) ? '0' : (( ($n%10>=2) && ($n%10<=4) && ($n%100<10 || $n%100>=20)) ? 1 : 2 ) );
                    $localized = $lang[ $s ];
            break;
            case 'sk': //slovak, nplurals=3
                    $s = ( ($n==1) ? 1 : ( ($n>=2 && $n<=4) ? 1 : '0' ) );
                    $localized = $lang[ $s ];
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
                    $localized = $lang[ $s ];
            break;
            case 'ua': //ukrainian, nplurals=3
                    $s = ( ($n%10==1 && $n%100!=11) ? '0' : ( $n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ) ? 1 : 1 );
                    $localized = $lang[ $s ];
            break;
            case 'lt': //lithuanian, nplurals=3
                    $s = ( ($n%10==1 && $n%100!=11) ? '0' : ( $n%10>=2 && ($n%100<10 || $n%100>=20) ) ? 1 : 1 );
                    $localized = $lang[ $s ];
            break;
            case 'fr': //french, nplurals=2
                    $s = ( $n > 1 ? '0' : 1 );
                    $localized = $lang[ $key.$s ];
            break;
            case 'ie': //irish, nplurals=5;
                    $s = (($n==1)? 0 : (($n==2) ? 1 : (($n<7) ? 2 : (($n<11) ? 3 : 4))));
                    $localized = $lang[ $s ];
            break;
            case 'is': //icelandic, nplurals=2;
            case 'hr': //croatian, nplurals=3;
                    $s = ($n%10!=1 || $n%100==11) ? 0 : 1;
                    $localized = $lang[ $s ];
            break;
            case 'lv': //latvian
                    $s = ( ($n%10==1 && $n%100!=11) ? 0 : (($n != 0) ? 1 : 2));
                    $localized = $lang[ $s ];
            break;
            case 'cy': //welsh, nplurals=4
                    $s =  (($n==1) ? 0 : (($n==2) ? 1 : (($n != 8 && $n != 11) ? 2 : 3)));
                    $localized = $lang[ $s ];
            break;
            case 'be': //belarusian, nplurals=3
            case 'bs': //bosnian, nplurals=3
                    $s =  (($n%10==1 && $n%100!=11) ? 0 : (($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20)) ? 1 : 2));
                    $localized = $lang[ $s ];
            break;
        }

        return $localized;
    }

    /**
     * Gets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @return String
     */
    public function getLocale()
    {
        return $this->defaultLang;
    }

    /**
     * Sets the string dictating the default language to translate into. (e.g. 'en').
     *
     * @param String $defaultLang A string representing the default language to translate into. (e.g. 'en').
     *
     * @return self
     */
    public function setLocale($defaultLang)
    {
        if (!empty($defaultLang)) {
            $this->checkLang($defaultLang);

            $this->defaultLang = $defaultLang;
            return $this;
        }
    }

    /**
     * Check if lang is valid
     * @param string $lang
     * @return true or InvalidArgumentException
     */
    protected function checkLang($checkLang)
    {
        $validLangs = $this->validLangs;

        foreach ($validLangs as $vLang) {
            if ($vLang === $checkLang) {
                return true;
            } else {
                $exception = true;
            }
        }

        if ($exception) {
                throw new \InvalidArgumentException('You selected a invalid lang ' . '"' . $checkLang . '"');
        }
    }
}
