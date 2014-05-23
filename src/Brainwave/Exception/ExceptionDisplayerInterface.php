<?php namespace Brainwave\Exception;

use \Brainwave\Workbench\Workbench;

interface ExceptionDisplayerInterface
{

    /**
     * [__construct description]
     * @param App $app [description]
     */
    public function __construct(Workbench $app, $charset);

    /**
     * Display the given exception to the user.
     *
     * @param  \Exception  $exception
     */
    public function display($exception);
}
