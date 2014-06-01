<?php
namespace Brainwave\Exception;

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
use \Brainwave\Exception\ExceptionDisplayerInterface;

/**
 * PlainDisplayer
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class PlainDisplayer implements ExceptionDisplayerInterface
{
    /**
     * [$app description]
     * @var [type]
     */
    protected $app;

    /**
     * Displayer language
     * @var string
     */
    private $charset;

    /**
     * @param Workbench $app Brainwave\Workbench\Workbench
     * @param string    $charset language
     */
    public function __construct(Workbench $app, $charset)
    {
        $this->app = $app;
        $this->charset = strtolower($charset);
    }

    /**
     * Error handler
     */
    public function display($exception)
    {
        $this->app->contentType('text/html');

        //Set error status
        $this->app['response']->setStatus(500);
        $title = 'Brainwave Application Error';
        $header = 'The application could not run because of the following error:';
        $footer = 'Copyright &copy; ' . date('Y') . ' Brainwave';

        if ($exception instanceof \Exception) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $trace = str_replace(array('#', '\n'), array('<div>#', '</div>'), $exception->getTraceAsString());
            $class = get_class($exception);

            $content = <<<EOF
<h2><i class="fa fa-circle-o" style="float:none;"></i>Details</h2>
<div class="class">
    <strong>Type:</strong> $class
</div>
EOF;
            if ($code) {
                $content .= <<<EOF
<div class="code">
    <strong>Code:</strong> $code
</div>
EOF;
            }
            if ($message) {
                $content .= <<<EOF
<div class="message">
    <strong>Message:</strong> $message
</div>
EOF;
            }
            if ($file) {
                $content .= <<<EOF
<div class="file">
    <strong>File:</strong> <pre>$file</pre>
</div>
EOF;
            }
            if ($line) {
                $content .= <<<EOF
<div class="line">
    <strong>Line:</strong> $line
</div>
EOF;
            }
            if ($trace) {
                $content .= <<<EOF
<h2><i class="fa fa-circle-o" style="float:none;"></i>Trace</h2>
<pre>$trace</pre>
EOF;
            }

        } else {
            $content = $exception;
        }

        return $this->decorate($title, $header, $content, $footer, $this->getStylesheet());
    }

    /**
     * Generate brainwave template markup
     *
     * This method accepts a title, header, content, footer and css to generate an HTML document layout.
     *
     * @param  string $title   The title of the HTML template
     * @param  string $header  The header title of the HTML template
     * @param  string $content The body content of the HTML template
     * @param  string $footer  The footer of the HTML template
     * @param  string $css     The css of the HTML template
     * @return string
     */
    public function decorate($title, $header, $content, $footer, $css = '', $js = '')
    {
        $footer = $this->app->config('app.footer');
        if (!empty($footer)) {
            $footer = 'Copyright &copy; ' . date('Y') . ' ' . $this->app->config('app.footer');
        }

        print <<<EOF
<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
    <head>
        <meta charset="$this->charset">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>$title</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <!-- start: JS-->
            $js
        <!-- end: Js-->
        <!-- start: CSS-->
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <style>
            $css
        </style>
        <!-- end: CSS-->
    </head>
    <body>
        <div class="console">
            <div class="header">
                <span>
                    <i class="fa fa-leaf"></i>
                    $header
                </span>
            </div>
            <div class="main">
                <div class="code">
                    $content
                </div>
            </div>
            <div class="footer">
                <p>
                    $footer
                </p>
                <div class="logo"></div>
            </div>
        </div>
    </body>
</html>
EOF;
    }

   /**
    * Stylesheet
    * @return string $mode type
    */
    public function getStylesheet($mode = 'exception')
    {
        if ($mode === 'pageNotFound') {
            $css  = 'min-height: 20%;'."\r\n";
            $css .= 'max-height: 210px;';
        } else {
            $css  = 'min-height: 20%;'."\r\n";
            $css .= 'max-height: 619px;';
        }

           return <<<EOF
@import url(http://fonts.googleapis.com/css?family=Open+Sans:300, 400);
body {
    font: 12px/1.5 "Open Sans", Helvetica, Arial, Verdana, sans-serif;
    height: 100%;
    margin: 0;
    padding: 0;
    border: 0;
    background-repeat: repeat;
    background-image: url(data:image/png;base64,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);
}
.console {
    max-width: 72.85714286rem;
    max-width: 1167px\9;
    height: auto;
    $css
    margin-left: -36.42857143rem;
    margin-left: -583px\9;
    margin-top: -309px;
    position: absolute;
    top: 50%;
    left: 50%;
    background-color: #1c1c1c;
    color: #fff;
}
@media screen {
    @media (min-width: 0px) {
        .console {
            max-width: 72.85714286rem;
            height: auto;
            $css
            overflow-x: visible;
            margin: auto;
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background-color: #1c1c1c;
            color: #fff;
        }
    }
}
.console .header {
    background-color: #26ADE4;
    color: #fff;
    display: block;
    max-height: 82px;
    min-height: 25px;
    width: 100%
}
.console .header i {
    margin-right: 14px
}
.console .header span {
    display: block;
    font-size: 14px;
    padding: 12px 20px 10px
}
.console .main {
    display: block;
    padding: 0 20px
}
.console .footer {
    background-color: #272727;
    height: 43px;
    width: 100%;
    position: absolute;
    bottom: 0;
}
.console .footer p {
    padding: 2px 0 0 20px
}
.console .footer .logo {
    background: url(cresk.png) no-repeat;
    height: 54px;
    margin-left: 14%;
    width: 87px;
    position: absolute;
    right: 36px;
    top: -2px;
}
.console .code {
    padding: 20px 0 20px 0
}
.console .file {
    word-break: break-all;
}
.console .file pre {
    max-width: 95.3%;
    min-width: 50%;
    background-color: #111;
    padding: 5px 0 5px 15px;
    overflow-y: hidden;
    display: -webkit-box;
    margin-left: 35px;
    margin-top: -24px;
}
.console .code .class,
.console .code .code,
.console .code .message,
.console .code .file,
.console .code .line {
    padding: 10px 0 5px 32px;
}
.console .code div {
    padding-bottom: 20px
}
.console .code i {
    color: #00DFFC;
    font-size: 14px;
    margin-right: 20px;
    padding-top: 2px;
    float: left;
}
.console .code span {
    text-align: left;
    display: table
}
.code a {
    color: #fff;
}
.code a:hover {
    color: #00DFFC;
}
pre {
    background-color: #111;
    padding: 15px 0 10px 20px;
    overflow-y: hidden;
}
pre div {
    padding: 0px !important;
}
EOF;
    }
}
