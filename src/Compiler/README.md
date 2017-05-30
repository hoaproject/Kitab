The compiler is composed of 3 parts:

  1. A parser,
  2. An intermediate representation,
  3. Targets.

# Stream compiler

In order to fast handle large projects to document, Kitab compiler has
been designed to work as a *stream*. Objects in memory are freed as
soon as possible, they are not accumulate. The same memory space can
be reused for each file being compiled. The algorithm works as
follows:

  1. The finder provides the inputs to the compiler: This is an
     iterator, where each item is a PHP file,
  2. The parser… parses each file, and are transformed into an
     intermediate representation,
  3. The intermediate representation is compiled into partial objects
     by the target,
  4. Some data (e.g. symbols) are copied in the linker,
  5. Once these is no more inputs, the target ends by assembling all
     the partial objects thanks to the linker.

The amount of memory used by this workflow is almost linear in
time. This design imposes some limitations, but we are not here yet.

# Parser

The parser is responsible to analyse each PHP
file. The [PHP-Parser](https://github.com/nikic/PHP-Parser) project
is used to achieve this goal. All the difficult job is handled by this
project. The result of a parsing is an Abstract Syntax Tree (AST),
that will be transformed into an Intermediate Representation (IR).

# Intermediate Representation

Because Kitab does not need to keep the whole AST in memory, it is
transformed into a lower Intermediate Representation (IR). The IR is a
set of simple structures. It ensures a light and simple API for the
targets, decoupled from the PHP-Parser AST API.

The root of IR is the `Kitab\Compiler\IntermediateRepresentation\Into`
class. It acts as a visitor to traverse the AST.

# Targets

Kitab can compile to many targets. So far, it only has the HTML target.

All targets must implement the `Kitab\Compiler\Target\Target`
interface. It requires few basic methods. It's up to the implementer
to ensure the correct behavior of the target.
