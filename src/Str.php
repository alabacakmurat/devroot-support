<?php
namespace Devroot\Core\Support;
use Devroot\Core\Support\Arr;
use Devroot\Core\Support\CaseConverter;
final class Str
{
	/**
	 * Constructor will initiate a new bag
	 */
	public function __construct(
		private array $storage = []
	)
	{}

	/**
	 * New dynamic instance from a given data
	 * @return self
	 */
	static public function createFrom(string|null $string)
	{
		return new Str(Arr::wrap($string));
	}

	/**
	 * Make sure a string starts and ends with another string
	 * 
	 * @see Str::startWith
	 * @see Str::endWith
	 * @return string
	 */
	static public function wrapWith(string|null $string, string $affix)
	{
		return Str::startWith(Str::endWith($string, $affix), $affix);
	}

	/**
	 * Make sure a string starts with another string
	 * 
	 * @see Str::prepend
	 * @return string
	 */
	static public function startWith(string|null $string, string $prefix)
	{
        $quoted = preg_quote($prefix, '/');
        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $string);
	}

	/**
     * Determine if a given string starts with a given substring.
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    static public function startsWith(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
                return true;
            }
        }
        return false;
    }

	/**
	 * Make sure a string ends with another string
	 * 
	 * @see Str::append
	 * @return string
	 */
	static public function endWith(string|null $string, string $suffix)
	{
        $quoted = preg_quote($suffix, '/');
        return preg_replace('/(?:'.$quoted.')+$/u', '', $string).$suffix;
	}
	
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    static public function endsWith(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if (
                $needle !== '' && $needle !== null
                && substr($haystack, -strlen($needle)) === (string) $needle
            ) {
                return true;
            }
        }

        return false;
    }

	
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $string
     * @param  string|string[]  $needles
     *
     * @return bool
     */
    static public function containsAny(string $string, array|string $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($string, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains all array values.
     *
     * @param  string  $string
     * @param  string[]  $needles
     *
     * @return bool
     */
    static public function contains(string $string, array|string $needles): bool
    {
        foreach((array) $needles as $needle) {
            if (!str_contains($string, $needle)) {
                return false;
            }
        }
        return true;
    }

	/**
	 * Case converter
	 * @return string
	 */
	static public function caseConvert( string $string, string $case = 'snake' ): string
    {
		return call_user_func_array(['static', $case], [$string]);
    }

	/**
	 * Pascal case conversion
	 * @return string
	 */
	static public function pascal(string $string) 	{
		return (new CaseConverter)->convertToStudlyCaps($string);
	}
	
	/**
	 * Camel case conversion
	 * @return string
	 */
	static public function camel(string $string) 	{
		return (new CaseConverter)->convertToCamelCase($string);
	}
	
	/**
	 * Snake case conversion
	 * @return string
	 */
	static public function snake(string $string) 	{
		return Str::lower((new CaseConverter)->convertToSnakeCase($string));
	}
	
	/**
	 * Kebab case conversion
	 * @return string
	 */
	static public function kebab(string $string) 	{
		return (new CaseConverter)->convertToKebabCase($string);
	}
	
	/**
	 * Title case conversion
	 * @return string
	 */
	static public function title(string $string) 	{
		return (new CaseConverter)->convertToStartCase($string);
	}
	
	/**
	 * Upper case conversion
	 * @return string
	 */
	static public function upper(string $string) 	{
		return (new CaseConverter)->convertToUpperCase($string);
	}

	/**
	 * Lower case conversion
	 * @return string
	 */
	static public function lower(string $string) 	{
		return (new CaseConverter)->convertToLowerCase($string);
	}


	/**
	 * Undefined dynamic calls will be redirect to static
	 * @return
	 */
	public function __call($fn, $params)
	{
		// static available & defined?
		if( method_exists('static', $fn) )
			return call_user_func_array(['static', $fn], [$this->storage, ...$params]);
		else
			return throw new \BadMethodCallException("Str::${fn} doesn't exist");
	}
    /**
     * Static calls for array_X
     * @return mixed
     */
    static public function __callStatic($fn, $params)
    {
		if( function_exists('str_'.Str::snake($fn)) )
			return call_user_func_array('str_'.Str::snake($fn), $params);
		else
			return throw new \BadMethodCallException("Str::${fn} doesn't exist");
    }
}