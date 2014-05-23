<?php namespace Brainwave\Config\Interfaces;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface LoaderInterface
{
    /**
     * [load description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    public function load($file);

    /**
     * [getData description]
     * @return [type] [description]
     */
    public function getData();

    /**
     * [setData description]
     * @return [type] [description]
     */
    public function setData(array $data);

    /**
     * [getString description]
     * @return [type] [description]
     */
    public function getString();

    /**
     * [setString description]
     * @param array $string [description]
     */
    public function setString(array $string);

    /**
     * [getValues description]
     * @return [type] [description]
     */
    public function getValues();

    /**
     * [setValues description]
     * @param array $values [description]
     */
    public function setValues(array $values);
}
