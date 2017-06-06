This namespace contains all the Intermediate Representations the
parser will compile into. They represent an abstraction of the code
holding only the data required by the target.

See [the global workflow](kitab/compiler/index.html) to get understand
where the Intermediate Representation (IR) happens.

# Into IR with a visitor

The parser analyses a file, and a resulting Abstract Syntax Tree (AST)
is generated on success. The AST is then transformed into the IR by
applying a visitor on it. The
`Kitab\Compiler\IntermediateRepresentation\Into` class acts as a
visitor on the AST to transform some nodes in the AST into IR. When
the transformation finishes, the IR can be retrived by collecting it.

## Examples

Let `$phpParser` be an instance of
the [`PHP-Parser`](https://github.com/nikic/PHP-Parser) parser, thus:

```php,ignore
// Parse PHP code.
$statements = $phpParser->parse('<?php function f(int $x): int { return $x << 2; }');

// Prepare the visitor to transform the AST into IR.
$intoIR = new Kitab\Compiler\IntermediateRepresentation\Into('example.php');

// Prepare the visitor logic.
$traverser = new PhpParser\NodeTraverser();
$traverser->addVisitor($intoIR);

// Visitor and transform.
$traverser->traverse($statements);

// Collect the result.
$intermediateRepresentation = $intoIR->collect();

assert($intermediateRepresentation instanceof Kitab\Compiler\IntermediateRepresentation\File);
```
