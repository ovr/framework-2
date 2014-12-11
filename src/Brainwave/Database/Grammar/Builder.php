<?php
namespace Brainwave\Database\Grammar;

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

/**
 * Builder
 *
 * @package Narrowspark/Database
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
class Builder
{
    /**
     * Database connection instance
     *
     * @var bool
     */
    protected $connection;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '!<', '!>', '><',
        '<>', '!=', '|=', '^=', '!', '#=', '&', '|',
        '^', '#', '<<', '>>', '&=', '~', '~%', '%~',
        '~-', '~+', '~%+', 'like', 'not like', 'between',
        'ilike', 'rlike', 'regexp', 'not regexp',
    ];

    /**
     * All supported aggregations
     *
     * @var array
     */
    protected $aggregations = ['AVG', 'SUM', 'MIN', 'COUNT', 'MAX'];

    /**
     * The key that should be used when caching the query.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * The number of minutes to cache the query.
     *
     * @var int
     */
    protected $cacheMinutes;

    /**
     * The tags for the query cache.
     *
     * @var array
     */
    protected $cacheTags;

    /**
     * The cache driver to be used.
     *
     * @var string
     */
    protected $cacheDriver;

    /**
     * The grammar table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Create a new builder instance.
     *
     * @param  \Brainwave\Database\Connection\Interfaces\ConnectionInterface $connection
     * @return void
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Check if operators exist
     *
     * @param  string $operator
     * @return bool
     */
    public function operatorExist($operator)
    {
        return in_array(strtolower($operator), $this->operators, true);
    }

    /**
     * Determine if the given value is a raw expression.
     *
     * @param  mixed $value
     * @return bool
     */
    public function isExpression($value)
    {
        return $value instanceof Expression;
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.000';
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string $value
     * @return string
     */
    public function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        return '`'.str_replace('`', '``', $value).'`';
    }

    /**
     * Wrap a single string in keyword identifiers and replace JSON.
     *
     * @param  string $string
     * @return string
     */
    public function wrapColumn($string)
    {
        return ' `'.str_replace('.', '`.`', preg_replace('/(^#|\(JSON\))/', '', $string)).'` ';
    }

    /**
     * Wrap a array value
     *
     * @param  array  $array
     * @return string
     */
    public function wrapArray(array $array)
    {
        $temp = [];

        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->wrapValue($value);
        }

        return implode($temp, ',');
    }

    /**
     * Wrap a single function name
     *
     * @param  string $column
     * @param  string $string
     * @return string
     */
    public function wrapFunctionName($column, $string)
    {
        return (strpos($column, '#') === 0 && preg_match('/^[A-Z0-9\_]*\([^)]*\)$/', $string)) ?

        $string :

        $this->wrapValue($string);
    }

    /**
     * [columnPush description]
     *
     * @param  string|array $columns
     * @return string
     */
    public function columnPush($columns)
    {
        if ($columns === '*') {
            return $columns;
        }

        if (is_string($columns) || is_int($columns)) {
            $columns = [(string) $columns];
        }

        $stack = [];

        foreach ($columns as $key => $value) {
            preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-\*]*)\)/i', $value, $match);

            if (isset($match[1], $match[2])) {
                if (in_array(strtoupper($match[1]), $this->aggregations)) {
                    array_push(
                        $stack,
                        $match[1].'('.($match[2] === '*' ?
                            $match[2] :
                            $this->wrapColumn($match[2])).')'
                    );
                } else {
                    array_push(
                        $stack,
                        $this->wrapColumn($match[1]).' AS '.$this->wrapColumn($match[2])
                    );
                }
            } else {
                if ($value === "1") {
                    array_push($stack, $value);
                } else {
                    array_push($stack, $this->wrapColumn($value));
                }
            }
        }

        return implode($stack, ',');
    }

    /**
     * Get the cache object with tags assigned, if applicable.
     *
     * @return \Brainwave\Cache\CacheManager
     */
    protected function getCache()
    {
        $connection = $this->connection;
        $cache = $connection->getCacheManager()->driver($this->cacheDriver);

        return $this->cacheTags ? $cache->tags($this->cacheTags) : $cache;
    }

    /**
     * Get the cache key and cache minutes as an array.
     *
     * @return array
     */
    protected function getCacheInfo()
    {
        return array($this->getCacheKey(), $this->cacheMinutes);
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey ?: $this->generateCacheKey();
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey()
    {
        $name = $this->connection->getName();
        //TODO
        //return md5($name.$this->toSql().serialize($this->getBindings()));
    }

    /**
     * Get the grammar's table prefix.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     *
     * @param  string $prefix
     * @return $this
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        return $this;
    }
}
