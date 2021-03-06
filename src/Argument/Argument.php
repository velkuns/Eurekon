<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Eurekon\Argument;

/**
 * Class to access and use arguments from command line in PHP CLI scripts.
 *
 * @author Romain Cottard
 */
class Argument
{
    /** @var array List of arguments parsed */
    protected $arguments = [];

    /** @var Argument $instance Current class instance. */
    protected static $instance = null;

    /**
     * Class constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Get class instance (singleton pattern).
     *
     * @return Argument Class instance
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Add specified argument value.
     *
     * @param  string $argument Argument name
     * @param  mixed|null $value Value
     * @return mixed
     */
    public function add($argument, $value): self
    {
        $this->arguments[$argument] = $value;

        return $this;
    }

    /**
     * Get specified argument value.
     *
     * @param  string $argument Argument name
     * @param  string|null $alias Argument alias name (if exists)
     * @param  mixed|null $default Default value if argument does not exists.
     * @return mixed
     */
    public function get($argument, $alias = null, $default = null)
    {
        if (isset($this->arguments[$argument])) {
            return $this->arguments[$argument];
        } else {
            if (!empty($alias) && isset($this->arguments[$alias])) {
                return $this->arguments[$alias];
            } else {
                return $default;
            }
        }
    }

    /**
     * Get all arguments
     *
     * @return array
     */
    public function getAll()
    {
        return $this->arguments;
    }

    /**
     * Check if argument exists.
     *
     * @param string $argument Argument name
     * @param string|null $alias Argument alias name (if exists)
     * @return bool
     */
    public function has($argument, $alias = null)
    {
        if (isset($this->arguments[$argument])) {
            return true;
        } elseif (!empty($alias) && isset($this->arguments[$alias])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Parse argument from command lines.
     *
     * @param array $arguments Parameter for this function is $argv global variable.
     * @return Argument
     */
    public function parse(array $arguments)
    {
        $this->arguments = [];
        $arguments       = new ArgumentIterator($arguments);

        foreach ($arguments as $current) {
            $arguments->next();
            $next = ($arguments->valid() ? $arguments->current() : '');
            $arguments->prev();

            $arg1 = substr($current, 0, 1);
            $arg2 = substr($current, 0, 2);

            if ('--' == $arg2) {

                // ~ Case '--test'
                $arg   = [];
                $match = preg_match('`--([0-9a-z_-]+)="?(.+)"?`', $current, $arg);

                if ($match > 0) {
                    $this->arguments[$arg[1]] = $arg[2];
                } else {
                    if (!empty($next) && '-' !== substr($next, 0, 1)) {
                        $this->arguments[substr($current, 2)] = $next;
                    } else {
                        $this->arguments[substr($current, 2)] = true;
                    }
                }
            } elseif ('-' == $arg1) {

                // ~ case -t
                $arg = substr($current, 1);
                $len = strlen($arg);

                if (1 == $len && $next !== '' && '-' != substr($next, 0, 1)) {
                    $this->arguments[$arg] = $next;
                } else {
                    for ($letter = 0; $letter < $len; $letter++) {
                        $this->arguments[$arg[$letter]] = true;
                    }
                }
            } elseif ($arguments->key() !== 0 && $arg1 !== '-' && $arg2 !== '--' && !isset($this->arguments['__default__'])) {
                $this->arguments['__default__'] = $current;
            }
        }

        return $this;
    }
}
