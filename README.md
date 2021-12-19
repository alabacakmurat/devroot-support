> **NOTICE**: This project is still under development! It may have bugs.

# What is Devroot Project?

Devroot is being developed for public use but it hadn't always had this purpose. Once upon a time, another project called oak-framework had a repository in this profile. It started with the same purpose but became so personal and had so many libraries for personal use that I decided to use it for my own projects. As it was just a stack composed of svelte for frontend, php for backend, Nette's Latte engine for static templates and so on. `

# What is Devroot/Support?

Devroot/Support is a headless helper library that lets you manipulate strings and arrays.

## How to install Devroot/Support?

```
composer require devroot/support
```

## How to integrate Devroot/Support?

Supposing you have your composer's autoloader loaded in your project,

```php
use Devroot\Core\Support\{Arr, Str};

## Test strings
$test = "Merhaba Dünya!";

## Snake
print_r( Str::snake($test) );  					// Expected: merhaba-dunya
print_r( Str::camel(Str::snake($test)) );  		// Expected: merhabaDunya
print_r( Str::startWith($test, '"') );  		// Expected: "Merhaba Dünya!
print_r( Str::endWith($test, '"') );  			// Expected: Merhaba Dünya!"
print_r( Str::wrapWith($test, '"') );  			// Expected: "Merhaba Dünya!"
print_r( Str::startsWith($test, 'M') );  		// Expected: (bool) TRUE
print_r( Str::startsWith($test, 'm') );  		// Expected: (bool) TRUE
print_r( Str::endsWith($test, 'M') );  			// Expected: (bool) FALSE
print_r( Str::endsWith($test, 'm') );  			// Expected: (bool) FALSE
```
