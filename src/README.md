Kitab is an API documentation generator program. Its role is to scan,
lex, parse, and compile any PHP programs into a static HTML
documentation.

## Usage

The simplest way to use Kitab is with a command-line, like:

```sh
$ ./bin/kitab compile --with-composer --output-directory documentation src
```

where `src` is the directory containing the PHP code you would like to
document, `documentation` is the directory that will receive the
generated documentation, and the `--with-composer` option to ask Kitab
to use Composer for PSR-4 mapping definitions.

## Dependencies

Kitab requires PHP and NodeJS to be installed: PHP because this is a
PHP program, and NodeJS to pre-compiled the static search engine
(which is written in Elm).

## Standards and formats

Kitab expects documentation in your PHP code to be written
in [CommonMark](http://commonmark.org/) (a standard variant of
Markdown). It can be mixed with [HTML](w3.org/TR/html5/).

Each block of documentation can declare sections, and any kind of
CommonMark elements, like:

```
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
representing a namespace, if a `README.md` file exists, then it
will be used as the documentation of this particular namespace. For
instance, `Kitab\` maps to `src/`, so the documentation for the
`Kitab\Compiler` namespace is expected to be find in the
`src/Compiler/README.md` file, that simple. This is pretty
straightforward at usage.

Entity and namespace documentations are inserted at the top of their
respective documentation page. The rest of the page contains
information about the entity or the namespace.

Documentation can contain block of codes. This is possible to specify
the type of the block with this standard notation:

<pre>
```type
code
```
</pre>

where `type` can be `php`, `sh`, `html`, `css` etc. It has no
particular impact, except of the syntax highlighting. However, since
Kitab format is also the basis for other tools, it is better to not
forget to specify the type of a code block.
