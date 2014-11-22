<?php
namespace Brainwave\Database\Migrations\Driver;

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

use \Brainwave\Filesystem\Filesystem;
use \Brainwave\Database\Migrations\Driver\Interfaces\DriverInterface;

/**
 * FileDriver
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class FileDriver implements DriverInterface
{
    /**
     * @string
     */
    protected $filename = null;

    /**
     * Filesystem instance
     *
     * @var bool
     */
    protected $files;

    /**
     * Construct
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->files = new Filesystem();
    }

    /**
     * Get all migrated version numbers
     *
     * @return array
     */
    public function fetchAll()
    {
        $versions = file($this->filename, FILE_IGNORE_NEW_LINES);
        sort($versions);

        return $versions;
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return null|FileDriver
     */
    public function up(Migration $migration)
    {
        $versions = $this->fetchAll();

        if (in_array($migration->getVersion(), $versions)) {
            return;
        }

        $versions[] = $migration->getVersion();

        $this->write($versions);

        return $this;
    }

    /**
     * Down
     *
     * @param Migration $migration
     * @return null|FileDriver
     */
    public function down(Migration $migration)
    {
        $versions = $this->fetchAll();

        if (!in_array($migration->getVersion(), $versions)) {
            return;
        }

        unset($versions[array_search($migration->getVersion(), $versions)]);

        $this->write($versions);

        return $this;
    }

    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        return $this->files->exists($this->filename);
    }

    /**
     * Create Schema
     *
     * @return FileDriver
     */
    public function createSchema()
    {
        if (!$this->files->isWritable(dirname($this->filename))) {
            throw new \InvalidArgumentException(sprintf('The file "%s" is not writeable', $this->filename));
        }

        if (false === touch($this->filename)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" could not be written to', $this->filename));
        }

        return $this;
    }

    /**
     * Write to file
     */
    protected function write($versions)
    {
        if (false === file_put_contents($this->filename, implode("\n", $versions))) {
            throw new \RuntimeException(sprintf('The file "%s" could not be written to', $this->filename));
        }
    }
}
