Kitab uses code blocks written in the documentation to generate tests
(written in [CommonMark](http://commonmark.org/), as a reminder). A
code block can have a type, such as `php`:

    ```php
    // here is some PHP code
    ```

or `http`:

    ```http
    // here is some HTTP messages.
    ```

A code block handler is a class that is able to compile code blocks of
a given type into test case bodies. Also as a reminder, Kitab relies
on [atoum](https://atoum.org) for the test framework. Consequently,
test cases must use the atoum API.

When compiling code blocks into test cases, the target will loop over
all attached code block handlers. If one of them is able to handle a
specific code block, then it is used. Many code block handlers can
handle the same code block.

A code block handler must extend the
`Kitab\Compiler\Target\DocTest\CodeBlockHandler\Definition` interface.

# About code block types

A code block type can have 3 forms:

  1. A single identifier, like `php`,
  2. A list of identifiers separated by a comma, like `php,ignore`,
  3. Or more sophisticated identifiers with arguments, like
     `php,must_throw(E)`.

The format is not strict, but it should match these previous examples
as most as possible for the sake of consistency. The main type (here
`php`) should also be the former of the list, the other can sometimes
be refered as options.
