<?php
namespace Devroot\Core\Support;
use Devroot\Core\Support\Str;

/**
 * This class lets you manipulate an array variable.
 * Simple and no need to detail it.
 *
 * @author Murat ALABACAK <alabacakm@gmail.com>
 * @version 1.0
 *
 */
class Arr
{
    public function __construct(
        /**
         * Instance-factory values are stored in this variable
         * 
         * <code><pre>
         *      Arr::createFrom([1,2,3,4])
         * </pre></code>
         * 
         * @var array
         * */
		private array $storage = []
	)
	{}

	/**
	 * You can create an instance so that you can chain methods
     * 
     * <code><pre>
     *      ## These two are the same things
     *      Arr::createFrom([1,2,3,4])->get('key');
     *      Arr::get([1,2,3,4], 'key);
     * </pre></code>
     * 
     * @param array $array Required array value
     * 
	 * @return Arr
     * 
	 */
	static public function createFrom(array $array): Arr
	{
		return new Arr($array);
	}

	/**
	 * Retrieve all of the items in the array.
     * This method is designed for instance-calls, not for static calling
     * 
     * <code><pre>
     *      Arr::createFrom([1,2,3,4])->all();
     * </pre></code>
     * 
     * @message  
     * @param array $array Required array value
     * 
	 * @return array
	 */
	public function all(): array
	{
		return $this->storage;
	}

	/**
	 * Retrieve a single value by specifying a key or a default value if the key doesn't exist.
     * 
     * <code><pre>
     *      ## Example 1
     *      Arr::createFrom([1,2,3,4])->get('key', function() {
     *          return 'Another default with lambda';
     *      });
     * 
     *      ## Example 2
     *      Arr::createFrom([1,2,3,4])->get('key', 'Another default with lambda');
     * </pre></code>
     * 
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param string $key Which key will be looked for?
     * @param mixed $default Default fallback value if the requested data is missing
     * 
	 * @return mixed
	 */
	static public function get(array $array, string $key, $default = null)
    {
		if (!Arr::accessible($array)) {
            return static::valueOf($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (Arr::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? static::valueOf($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (Arr::accessible($array) && Arr::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::valueOf($default);
            }
        }

        return $array;
    }


    /**
     * Slice array items
     * 
     * @see http://php.net/array-slice
     * 
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param int $offset What is the starting position?
     * @param int $length How many items will be sliced and returned?
     * @param bool $preserveKeys Do the sliced items will have the same keys as before?
     * 
     * @return array
     */
    static public function slice(array $array, int $offset, int $length, bool $preserveKeys = false): array
    {
        return array_slice($array, $offset, $length, $preserveKeys);
    }

	
    /**
     * Return the first value in an array passing a given optional truth test.
     *
     * @param iterable $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param callable|null  $callback Filtering callback ($value, $key) as its parameters
     * @param mixed $default Default fallback value if the requested data is missing
     * 
     * @return mixed
     */
    static public function first(iterable $array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return static::valueOf($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return static::valueOf($default);
    }

	
    /**
     * Return the last element in an array passing a given optional truth test.
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param callable|null  $callback Filtering callback ($value, $key) as its parameters
     * @param mixed $default Default fallback value if the requested data is missing
     * 
     * @return mixed
     */
    static public function last(array $array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? static::valueOf($default) : end($array);
        }

        return Arr::first(array_reverse($array, true), $callback, $default);
    }

	
    /**
     * Get all of the given array except for a specified array of keys.
     * 
     * <code><pre>
     *      Arr::createFrom([1,2,3,4])->except(['ignoredKey']);
     * </pre></code>
     * 
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param array|string $keys Which keys will be ignored?
     *
     * @return array
     */
    static public function except(array $array, $keys): array
    {
        Arr::forget($array, $keys);

        return $array;
    }

	
    /**
     * Get a subset of the items from the given array.
     * 
     * <code><pre>
     *      Arr::createFrom([1,2,3,4])->only(['acceptedKey']);
     * </pre></code>
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param array|string $keys Which keys will be protected and the rest ignored?
     * 
     * @return array
     */
    static public function only(array $array, $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }


	
    /**
     * Pluck an array of values from an array.
     *
     * @param iterable $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param  string|array|int|null $value
     * @param  string|array|null $key
     *
     * @return array
     */
    static public function pluck(iterable $array, $value, $key = null): array
    {
        $results = [];

        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        foreach ($array as $item) {
            $itemValue = static::get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = static::get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

	
    /**
     * Push an item onto the beginning of an array.
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param mixed $value
     * @param mixed|null $key
     *
     * @return array
     */
    static public function prepend(array $array, $value, $key = null): array
    {
        if (func_num_args() === 2) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }
	
    /**
     * Push an item into an array as the last item
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param mixed $value
     * @param mixed|null $key
     *
     * @return array
     */
    static public function append(array $array, $value, $key = null): array
    {
        if (func_num_args() === 2) {
            array_push($array, $value);
        } else {
            $array = $array + [$key => $value];
        }

        return $array;
    }

	
    /**
     * Convert the array into a query string.
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * 
     * @return string
     */
    static public function query(array $array): string
    {
        return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Get one or a specified number of random values from an array.
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param int|null $number
     * @param bool|false $preserveKeys
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    static public function random(array $array, int $number = null, bool $preserveKeys)
    {
        $requested = is_null($number) ? 1 : $number;

        $count = count($array);

        if ($requested > $count) {
            throw new \InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($number)) {
            return $array[array_rand($array)];
        }

        if ((int) $number === 0) {
            return [];
        }

        $keys = array_rand($array, $number);

        $results = [];

        if ($preserveKeys) {
            foreach ((array) $keys as $key) {
                $results[$key] = $array[$key];
            }
        } else {
            foreach ((array) $keys as $key) {
                $results[] = $array[$key];
            }
        }

        return $results;
    }

	
    /**
     * Set an array item to a given value using "dot" notation. If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param string|null $key
     * @param mixed $value
     *
     * @return array
     */
    static public function set(array &$array, ?string $key, $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
	
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param array|string $keys
     *
     * @return void
     */
    static public function forget(array &$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (Arr::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

	
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param iterable $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param int $depth
     * 
     * @return array
     */
    static public function flatten(iterable $array, int $depth): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
	
    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param string|array $keys
     *
     * @return bool
     */
    static public function has($array, $keys): bool
    {
        $keys = (array) $keys;

        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (Arr::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (Arr::accessible($subKeyArray) && Arr::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

	
    /**
     * Determine if any of the keys exist in an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param string|array $keys
     * 
     * @return bool
     */
    static public function hasAny($array, $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (!$array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (Arr::has($array, $key)) {
                return true;
            }
        }

        return false;
    }
	
    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * 
     * @return bool
     */
    static public function accessible($value): bool
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param \ArrayAccess|array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param string|int $key
     * 
     * @see http://php.net/array-key-exists
     *
     * @return bool
     */
    static public function exists($array, $key): bool
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

	/**
     * Collapse an array of arrays into a single array.
     *
     * @param iterable $array Required array value (This parameter is skipped when the method is called from an instance)
     * @return array
     */
    static public function collapse(iterable $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

	/**
	 * Return if the array has this $item as one of its values
     * 
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param mixed $item 
     * 
	 * @return bool
	 */
	static public function contains(array $array, $item)
	{
		foreach( $array as $key => $value )
			if( $value == $item )
				return true;

		return false;
	}

    /**
     * Look for $values in ${array} and ignore unwanted values. Be aware of the case-sensitiveness
     * 
     * @param array $array Required array value (This parameter is skipped when the method is called from an instance)
     * @param array|string $values Accepted values in the ${array} 
     * 
     * @return array
     */
    static public function acceptValues(array $array, array|string $values)
    {
        $values = Arr::wrap($values);
        $new = [];
        foreach( $array as $key => $value )
            if( Arr::contains($values, $value) )
                $new[$key] = $value;

        return $new;
    }

	
    /**
     * If the given value is not an array and not null, wrap it in one.
     * 
     * @param mixed $value
     * 
     * @return array
     */
    static public function wrap($value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Return the value of a var. This function runs the lambda functions.
     * 
     * @param mixed $value
     * 
     * @return mixed
     */
    static public function valueOf($var)
    {
        return $var instanceOf \Closure ? $var() : $var;
    }

	/**
	 * Print as string
     * 
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->all());
	}

	/**
	 * Undefined dynamic calls will be redirected to static first or to the native php's array_{function} attributes
     * 
	 * @return mixed
     * @throws \BadMethodCallException
	 */
	public function __call($fn, $params)
	{
		// static available & defined?
		if( method_exists('static', $fn) )
			return call_user_func_array(['static', $fn], [$this->all(), ...$params]);
        else if( function_exists('array_'.Str::snake($fn)) )
			return call_user_func_array('array_'.Str::snake($fn), [$this->all(), ...$params]);
		else
			return throw new \BadMethodCallException("Arr::${fn} doesn't exist");
	}

    /**
     * Static calls for php's native array_{function} attributes
     * 
     * @return mixed
     */
    static public function __callStatic($fn, $params)
    {
		if( function_exists('array_'.Str::snake($fn)) )
			return call_user_func_array('array_'.Str::snake($fn), $params);
		else
			return throw new \BadMethodCallException("Arr::${fn} doesn't exist");
    }
}