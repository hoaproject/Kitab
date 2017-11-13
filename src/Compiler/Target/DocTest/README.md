Kitab's goal is twofold: Render and Test the
documentation. Documentation tests are often called DocTest.

A documentation should contain code blocks. Only code blocks within
the Examples and Exceptions Sections are compiled into tests.

Code blocks are compiled by
[code block handlers](./kitab/compiler/target/doctest/codeblockhandler/index.html),
but they are all assembled by the
`Kitab\Compiler\Target\DocTest\DocTest` target implementation.
