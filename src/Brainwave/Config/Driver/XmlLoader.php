<?php namespace Brainwave\Config\Driver;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Config\Interfaces\LoaderInterface;
use \Brainwave\Config\Exception\UndefinedFileException;

/**
*
*/
class XmlLoader implements LoaderInterface
{
    /**
     * [$string description]
     * @var array
     */
    private $string = array();

    /**
     * [$values description]
     * @var array
     */
    private $values = array();

    /**
     * [$data description]
     * @var array
     */
    private $data = array();

    /**
	 * [load description]
	 * @param  [type] $file [description]
	 * @return [type]       [description]
	 */
    public function load($file)
    {
        if ($this->remoteFile($file) === true) {
            $prefix = file_get_contents($file);
        } else {
            if (file_exists($file)) {
                $prefix = file_get_contents($file);
            } else {
                throw new \Exception('Please check if the path to your xml file (' . basename($file) . ') is correct');
            }
        }

        try {
            $Iterator = new \RecursiveIteratorIterator(
                new \SimpleXmlIterator($prefix)
            );
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not read '.$prefix."\n\n".$e->getMessage());
        }

        $string = array();
        $values = array();
        $allConfigData = array();

        foreach ($Iterator as $k => $v) {
            if (is_array($v) && !is_object($v)) {
                $string[] = trim($k);
            } elseif (is_object($v)) {
                $string[] = $v;
            } else {
                $values[] = trim($v);
            }
            $allConfigData[$k] = $v;
        }

        $this->setString($string);
        $this->setValues($values);
        $this->setData($allConfigData);
    }

    /**
     * [getData description]
     * @return [type] [description]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * [setData description]
     * @param array $data [description]
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * [getString description]
     * @return [type] [description]
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * [setString description]
     * @param array $string [description]
     */
    public function setString(array $string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * [getValues description]
     * @return [type] [description]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * [setValues description]
     * @param array $values [description]
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Private function to determine if files are local or remote
     * Used for merge_images() and minify() to determine if filemtime can be used
     *
     * @access private
     * @param string $file
     * @return bool
     */
    private function remoteFile($file)
    {
        if (preg_match("@^(?:http://|https://|//)@i", $file)) {
            return true;
        } else {
            return false;
        }
    }
}
