<?php
namespace Brainwave\Http;

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
 */

use Brainwave\Contracts\Http\Response as ResponseContract;
use Brainwave\Contracts\Support\JsonableInterface;
use Symfony\Component\HttpFoundation;

/**
 * Response
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class Response extends HttpFoundation\Response implements ResponseContract
{
    /**
     * Parameter encapsulation
     */
    use ResponseParameterTrait;

    /**
     * Set the content on the response.
     *
     * @param  mixed $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->original = $content;
        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {
            $this->headers->set('Content-Type', 'application/json');
            $content = $this->morphToJson($content);
        }
        // If this content implements the "Renderable" interface then we will call the
        // render method on the object so we will avoid any "__toString" exceptions
        // that might be thrown and have their errors obscured by PHP's handling.
        elseif ($content instanceof Renderable) {
            $content = $content->render();
        }

        return parent::setContent($content);
    }

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed  $content
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof JsonableInterface) {
            return $content->toJson();
        }

        return json_encode($content);
    }
    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof JsonableInterface ||
               $content instanceof \ArrayObject ||
               is_array($content);
    }
}
