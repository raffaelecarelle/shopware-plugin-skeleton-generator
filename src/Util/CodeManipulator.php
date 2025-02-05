<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Util;

use Exception;
use PhpParser\Builder;
use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\FirstFindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Token;
use RuntimeException;

/**
 * @internal
 */
final class CodeManipulator
{
    private const string CONTEXT_OUTSIDE_CLASS = 'outside_class';

    private const string CONTEXT_ARRAY = 'array';

    private readonly Parser $parser;

    private readonly Standard $printer;

    /**
     * @var null|array<Node>
     */
    private ?array $oldStmts = null;

    /**
     * @var array<Token>
     */
    private array $oldTokens = [];

    /**
     * @var array<int, Node>
     */
    private array $newStmts = [];

    public function __construct(
        private string $sourceCode,
    ) {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->printer = new Standard();

        $this->setSourceCode($sourceCode);
    }

    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    public function addAdditionalBundle(string $bundleName): void
    {
        $this->addUseStatementIfNecessary($bundleName);

        $node = new ArrayItem(
            new New_(new Name(Autoload::extractClassName($bundleName))),
        );

        $this->addNodeToAdditionalBundlesMethod($node);
    }

    public function addUseStatementIfNecessary(string $class): string
    {
        $shortClassName = Autoload::extractClassName($class);
        if ($this->isInSameNamespace($class)) {
            return $shortClassName;
        }

        $namespaceNode = $this->getNamespaceNode();

        $targetIndex = null;
        $addLineBreak = false;
        $lastUseStmtIndex = null;
        foreach ($namespaceNode->stmts as $index => $stmt) {
            $index = (int) $index;

            if ($stmt instanceof Use_) {
                // I believe this is an array to account for use statements with {}
                foreach ($stmt->uses as $use) {
                    $alias = $use->alias->name ?? $use->name->getLast();

                    // the use statement already exists? Don't add it again
                    if ($class === (string) $use->name) {
                        return $alias;
                    }

                    if ($alias === $shortClassName) {
                        // we have a conflicting alias!
                        // to be safe, use the fully-qualified class name
                        // everywhere and do not add another use statement
                        return '\\' . $class;
                    }
                }

                $lastUseStmtIndex = $index;
            } elseif ($stmt instanceof Class_) {
                // we hit the class! If there were any use statements,
                // then put this at the bottom of the use statement list
                if (null !== $lastUseStmtIndex) {
                    $targetIndex = $lastUseStmtIndex + 1;
                } else {
                    $targetIndex = $index;
                    $addLineBreak = true;
                }

                break;
            }
        }

        if (null === $targetIndex) {
            throw new Exception('Could not find a class!');
        }

        $newUseNode = (new Builder\Use_($class, Use_::TYPE_NORMAL))->getNode();
        array_splice(
            $namespaceNode->stmts,
            $targetIndex,
            0,
            $addLineBreak ? [$newUseNode, $this->createBlankLineNode(self::CONTEXT_OUTSIDE_CLASS)] : [$newUseNode],
        );

        $this->updateSourceCodeFromNewStmts();

        return $shortClassName;
    }

    private function addNodeToAdditionalBundlesMethod(ArrayItem $newNode): void
    {
        $classNode = $this->getClassNode();

        $targetNode = $this->findLastNode(fn ($node): bool => $node instanceof ClassMethod && 'getAdditionalBundles' === $node->name->toString(), [$classNode]);

        if ( ! $targetNode instanceof Node) {
            $targetNode = (new Method('getAdditionalBundles'))
                ->makePublic()
                ->setReturnType('array')
                ->addStmt(new Return_(new Array_()))
                ->getNode()
            ;

            $classNode->stmts[] = $targetNode;
        }

        $collectionArrayNode = $this->findFirstNode(fn ($node): bool => $node instanceof Array_, [$targetNode]);

        $whiteSpace = new ArrayItem(
            // @phpstan-ignore-next-line
            $this->createBlankLineNode(self::CONTEXT_ARRAY),
        );

        if ($collectionArrayNode instanceof Array_) {
            // Aggiungo una linea vuota solo a 1 elemento perché poi il printer si ricorda della formattazione
            if (1 === \count($collectionArrayNode->items)) {
                $collectionArrayNode->items[] = $whiteSpace;
            }

            $collectionArrayNode->items[] = $newNode;
        }

        $this->updateSourceCodeFromNewStmts();
    }

    private function createBlankLineNode(string $context): Use_ | Variable
    {
        return match ($context) {
            self::CONTEXT_OUTSIDE_CLASS => (new Builder\Use_(
                '__EXTRA__LINE',
                Use_::TYPE_NORMAL,
            ))
                ->getNode(),
            self::CONTEXT_ARRAY => new Variable(
                '__NEW__LINE',
            ),
            default => throw new Exception('Unknown context: ' . $context),
        };
    }

    private function updateSourceCodeFromNewStmts(): void
    {
        if (null === $this->oldStmts || [] === $this->oldTokens) {
            throw new RuntimeException('No Stmts are defined.');
        }

        $newCode = $this->printer->printFormatPreserving(
            $this->newStmts,
            $this->oldStmts,
            $this->oldTokens,
        );

        // replace the 3 "fake" items that may be in the code (allowing for different indentation)
        $newCode = preg_replace('/(\ |\t)*private\ \$__EXTRA__LINE;/', '', $newCode);
        $newCode = preg_replace('/use __EXTRA__LINE;/', '', (string) $newCode);
        $newCode = preg_replace('/(\ |\t)*\$__EXTRA__LINE;/', '', (string) $newCode);
        $newCode = preg_replace('/(\ |\t)*\$__NEW__LINE,/', "\n", (string) $newCode);

        $this->setSourceCode((string) $newCode);
    }

    private function setSourceCode(string $sourceCode): void
    {
        $this->sourceCode = $sourceCode;
        $this->oldStmts = $this->parser->parse($sourceCode);
        $this->oldTokens = $this->parser->getTokens();

        if (null === $this->oldStmts || [] === $this->oldTokens) {
            throw new RuntimeException('No Stmts are defined.');
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());
        $traverser->addVisitor(new NameResolver(null, [
            'replaceNodes' => false,
        ]));
        $this->newStmts = $traverser->traverse($this->oldStmts);
    }

    private function getClassNode(): Class_
    {
        $node = $this->findFirstNode(fn ($node): bool => $node instanceof Class_);

        if ( ! $node instanceof Class_) {
            throw new Exception('Could not find class node');
        }

        return $node;
    }

    /**
     * @param null|array<Node> $ast
     */
    private function findFirstNode(callable $filterCallback, ?array $ast = null): ?Node
    {
        $traverser = new NodeTraverser();
        $visitor = new FirstFindingVisitor($filterCallback);
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast ?? $this->newStmts);

        return $visitor->getFoundNode();
    }

    /**
     * @param array<Node> $ast
     */
    private function findLastNode(callable $filterCallback, array $ast): ?Node
    {
        $traverser = new NodeTraverser();
        $visitor = new FindingVisitor($filterCallback);
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        $nodes = $visitor->getFoundNodes();
        $node = end($nodes);

        return false === $node ? null : $node;
    }

    private function isInSameNamespace(string $class): bool
    {
        $pos = strrpos($class, '\\');

        if (false === $pos) {
            return $pos;
        }

        $namespace = substr($class, 0, $pos);

        return $this->getNamespaceNode()->name?->toCodeString() === $namespace;
    }

    private function getNamespaceNode(): Namespace_
    {
        $node = $this->findFirstNode(fn ($node): bool => $node instanceof Namespace_);

        if ( ! $node instanceof Namespace_) {
            throw new Exception('Could not find namespace node');
        }

        return $node;
    }
}
