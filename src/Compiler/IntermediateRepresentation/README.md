This namespace contains all the intermediate representations the
parser will compile into. They represent an abstraction of the code
holding only the data required by the target.

The workflow is the following:

  1. Compiler parses code with the parser,
  2. The parser generates intermediate representations,
  3. Which are used by the target and the linker.
