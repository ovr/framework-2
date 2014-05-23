<?php namespace Brainwave\Support\Translator\Interfaces;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
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
