<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Util;

use PHPUnit\Framework\TestCase;
use ShopwarePluginSkeletonGenerator\Util\CodeManipulator;

class CodeManipulatorTest extends TestCase
{
    private CodeManipulator $instance;

    protected function setUp(): void
    {
        $initialSourceCode = "<?php \n\nnamespace Some\Namespace;\n\n class MyClass {}";
        $this->instance = new CodeManipulator($initialSourceCode);
    }

    /**
     * Test getSourceCode method of CodeManipulator
     * The method is expected to return the source code provided at instantiation
     */
    public function testGetSourceCode(): void
    {
        $expected = "<?php \n\nnamespace Some\Namespace;\n\n class MyClass {}";
        $actual = $this->instance->getSourceCode();

        self::assertSame($expected, $actual, 'should return the original source code');
    }

    /**
     * Test addAdditionalBundle method of CodeManipulator
     * The method is expected to add an additional bundle to the source code
     */
    public function testAddAdditionalBundle(): void
    {
        // Define a bundle name for the test
        $bundleName = 'TestBundle';

        // Keep a copy of the source code before modification
        $sourceCodeBefore = $this->instance->getSourceCode();

        // Call the method to be tested
        $this->instance->addAdditionalBundle($bundleName);

        // Get the source code after modification
        $sourceCodeAfter = $this->instance->getSourceCode();

        // Ensure that the source code is not the same after calling addAdditionalBundle
        self::assertNotSame($sourceCodeBefore, $sourceCodeAfter, 'source code should change after calling addAdditionalBundle');

        // Ensure that the source code contains the new bundle name after calling addAdditionalBundle
        self::assertStringContainsString($bundleName, $sourceCodeAfter, 'source code should contain the new bundle name after calling addAdditionalBundle');
    }

    /**
     * Test addUseStatementIfNecessary method of CodeManipulator when class is in the same namespace.
     * The method is expected to return short class name.
     */
    public function testAddUseStatementIfNecessarySameNamespace(): void
    {
        $className = "SameNamespace\SomeClass";
        $expected = 'SomeClass';

        $actual = $this->instance->addUseStatementIfNecessary($className);

        self::assertSame($expected, $actual, 'should return short class name when the class is in the same namespace');
    }

    /**
     * Test addUseStatementIfNecessary method of CodeManipulator when class is not in the same namespace and
     * use statement for this class does not exist. The method is expected to return short class name and add
     * use statement to the source code.
     */
    public function testAddUseStatementIfNecessaryNewUse(): void
    {
        $className = "NewNamespace\SomeClass";
        $expected = 'SomeClass';

        $actual = $this->instance->addUseStatementIfNecessary($className);

        self::assertSame($expected, $actual, 'should return short class name when the class is in a new namespace');
        self::assertStringContainsString("use $className;", $this->instance->getSourceCode(), 'source code should contain use statement for the new class from a different namespace');
    }

    /**
     * Test addUseStatementIfNecessary method of CodeManipulator when class is not in the same namespace and use statement
     * for this class already exists. The method is expected to return short class name and does not add
     * another use statement to the source code.
     */
    public function testAddUseStatementIfNecessaryExistingUse(): void
    {
        $className = "NewNamespace\SomeClass";
        $expected = 'SomeClass';

        // Make sure use statement for the class already exists in the source code.
        $this->instance->addUseStatementIfNecessary($className);
        $sourceCodeBefore = $this->instance->getSourceCode();

        $actual = $this->instance->addUseStatementIfNecessary($className);

        self::assertSame($expected, $actual, 'should return short class name when the class is in a new namespace');
        self::assertSame($sourceCodeBefore, $this->instance->getSourceCode(), 'source code should not change when the class from a different namespace already has a use statement');
    }
}
