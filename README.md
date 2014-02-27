prototypr
====
PHP 5.5+ object prototyping system using traits

[![Build Status](https://travis-ci.org/jgswift/prototypr.png?branch=master)](https://travis-ci.org/jgswift/prototypr)

## Installation

Install via [composer](https://getcomposer.org/):
```sh
php composer.phar require jgswift/prototypr:dev-master
```

## Usage

Prototypr is a lightweight php trait that enables easy object prototyping magic methods.
Prototypr aims to add simple prototypal behavior to php without intruding on your domain model

The following is a minimal example
```php
<?php
class Foo
{
    use prototypr\Prototype;
}

Foo::bar(function() {
    return "baz";
});

$foo = new Foo;
var_dump($foo->bar()); // returns "baz"
```

Alternatively methods can be set in a local scope and apply only to an individual object

```php
class Foo
{
    use prototypr\Prototype;
}

$foo = new Foo();
$foo->bar(function() {
    return "baz";
});
var_dump($foo->bar()); // returns "baz"
```

prototypr supports late-binding of multiple closures and will always execute all closures regardless of return conditions

```php
class Foo
{
    use prototypr\Prototype;
}

$count = 0;
Foo::bar(function()use(&$count) {
    $count+=1;
});

Foo::bar(function()use(&$count) {
    $count+=2;
});

$foo = new Foo();
$foo->bar();

var_dump($count); // returns 3
```

prototypr will automatically traverse the class tree to find methods, but you may also specify individual extensions

```php
class Foo
{
    use prototypr\Prototype;
}

Foo::bar(function() {
    return "somethingImportant";
});

class Baz
{
    use prototypr\Prototype;
}

prototypr\Manager::extend('Baz','Foo');

var_dump(count(prototypr\Registry::prototypes('Baz'))); // returns 1

$baz = new Baz;
var_dump($baz->bar()); // returns "somethingImportant"
```