<p align="center">
    <img src="../resource/logo.svg" width="250px" />
</p>

---

<p align="center">
  <a href="https://travis-ci.org/hoaproject/Kitab"><img src="https://img.shields.io/travis/hoaproject/Kitab/master.svg" alt="Build status" /></a>
  <a href="https://packagist.org/packages/hoa/kitab"><img src="https://img.shields.io/packagist/dt/hoa/kitab.svg" alt="Packagist" /></a>
  <a href="https://hoa-project.net/LICENSE"><img src="https://img.shields.io/packagist/l/hoa/kitab.svg" alt="License" /></a>
</p>

<p align="center">
  <strong>Kitab</strong> is the ideal companion for
  <strong>Documentation-Driven Quality</strong> for PHP programs.
</p>
<p align="center">
  Made with ❤️ by <a href="https://hoa-project.net"><img src="https://static.hoa-project.net/Image/Hoa.svg" height="18" alt="Hoa" /></a>
</p>

# Kitab

The goal of Kitab is twofold, **render** and **test** the
documentation:

  1. Generate a quality and searchable documentation based on your
     code. The documentation inside your code is compiled into static
     HTML files with a powerful static search engine,

  2. Test the documentation itself. Indeed, a documentation contains
     examples, and these examples are compiled into test suites that
     are run directly to ensure the examples are still up-to-date and
     working.

## Static documentation

Kitab is able to compile the documentation inside your code into
static HTML files. A carefully crafted design is provided to ensure a
great look for your documentation. This is possible to customize the
logo, the project name, etc.

A static search engine is compiled specifically for your
documentation. It contains all the modern features we can expect from a
search engine, like tokenizing, stemming, stop word filtering, term
frequency-inverse document frequency (TF-ID), inverted index etc. The
search engine database is pre-computed and optimized to load as fast
as possible.

The more your documentation provides details and smart vocabulary, the
more the search engine will be able to provide relevant results.

The following command line compiles the documentation from your code
in `src` into HTML files stored in `doc`:

```sh
$ ./bin/kitab compile --open --with-composer --output-directory doc src
```

The `--with-composer` option asks Kitab to use Composer for PSR-4
mapping definitions. This is useful to map `README.md` files to
namespace directories, more below. The `--open` option opens the
documentation in your default browser as soon as it is generated
successfully.

## DocTest

Documentation test suites, aka DocTest, are generated based on the
examples present in your documentation. Examples are compiled into
test suites and executed on-the-fly. A cache is generated to avoid
to re-compile examples into test suites each time.

For instance, the following example will succeed:

```php
/**
 * Classical sum of two integers.
 *
 * # Examples
 *
 * ```php
 * $x = 1;
 * $y = 2;
 *
 * assert(3 === sum($x + $y));
 * ```
 */
function sum(int $x, int $y): int
{
    return $x + $y;
}
```

The following command line generates and executes the documentation
test suites from the `src` directory:

```sh
$ ./bin/kitab test src
```

Behind the scene, Kitab
uses [the atoum test framework](http://atoum.org).

# Dependencies

Kitab requires [PHP](http://php.net/)
and [NodeJS](https://nodejs.org/) to be installed: PHP because this is
a PHP program, and NodeJS to pre-compiled the static search engine
(which is written in [Elm](http://elm-lang.org/)).

# Standards and formats

Kitab expects documentation in your PHP code to be written
in [CommonMark](http://commonmark.org/) (a standard variant of
Markdown). It can be mixed with [HTML](https://w3.org/TR/html5/).

Each block of documentation can declare sections, and any kind of
CommonMark elements, like:

```php
/**
 * This is a block of documentation, attached to a PHP class.
 *
 * # Examples
 *
 * An example illustrates how to use the documented entity, here the
 * class `C`.
 *
 * ```php
 * $c = new C();
 * ```
 */
class C { }
```

There are only 2 special section names: _Examples_, and _Exceptions_. Use
them to introduce one or more examples, and exceptions
explanations. This is a common standard used by other tools.

Any kind of entities can be documented: Classes, interfaces, traits,
class attributes, constants, methods, and functions.

Namespaces cannot be documented directly from the code, because of the
way they are declared (entities are declared inside a namespace; the
namespace is not declared as is). However, they can be documented
through special files, named `README.md`. If your code
follows [the PSR-4 specification](http://www.php-fig.org/), then run
Kitab with the `--with-composer` option to specify the location of the
`composer.json` file of your project in order to allow Kitab to
automatically find PSR-4 mappings. These mappings are necessary to
transform a namespace into a path to a directory. For each directory
representing a namespace, if a `README.md` file exists, then it will
be used as the documentation of this particular namespace. For
instance, `Kitab\` maps to `src/`, so the documentation for
the [`Kitab`\\`Compiler`](kitab/compiler/index.html) namespace is
expected to be find in the `src/Compiler/README.md` file, that
simple. This is pretty straightforward at usage.

Entity and namespace documentations are inserted at the top of their
respective documentation page. This is the introduction. The rest of
the page contains information about the entity or the namespace.

## Block of codes

Documentation can contain block of codes. This is possible to specify
the type of the block with this standard notation:

<pre>
```type
code
```
</pre>

where `type` can be `php`, `sh`, `html`, `css` etc. It impacts the
syntax highlighting.

The `php` type is special since it indicates a potential test
case. Indeed, all code blocks inside an Examples Section can be
compiled into test suites with the `./bin/kitab test` command. We can
control the expectation of the test case:

  * `php` indicates the test case must be a success,
  * `php,ignore` indicates the test case must be skiped,
  * `php,must_throw` indicates the test case must throw an exception,
  * `php,must_throw(E)` indicates the test case must throw an
    exception of kind `E`.

Consequently, the following example will be a success:

``` php
/**
 * Generate a runtime exception.
 *
 * # Examples
 *
 * ```php,must_throw(RuntimeException)
 * panic('Hello World');
 * ```
 */
function panic(string $message): RuntimeException
{
    throw new RuntimeException($message);
}
```
