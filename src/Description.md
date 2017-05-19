Kitab is an API documentation generator program. Its role is to scan,
lex, parse, and compile any PHP programs into a static HTML
documentation.

## Usage

The best simplest way to use Kitab is with a command-line, like:

```sh
$ ./bin/kitab compile --with-composer --output-directory documentation src
```

where `src` is the directory containing the PHP code you would like to
document, `documentation` is the directory that will receive the
generated documentation, and the `--with-composer` option to ask Kitab
to use Composer for PSR-4 mapping definitions.

## Standards
