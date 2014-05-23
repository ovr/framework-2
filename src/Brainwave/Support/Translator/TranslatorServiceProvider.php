<?php namespace Brainwave\Support\Translator;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\Translator\TranslatorManager;
use \Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

/**
 * Provides a Translator as a service for Silex applications.
 */
class TranslatorServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Workbench $app)
    {
        $app['translator'] = function ($app) {
            $translator = new TranslatorManager();
            $translator->setLocale($app['translator.locale']);
            return $translator;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Workbench $app)
    {

    }
}
