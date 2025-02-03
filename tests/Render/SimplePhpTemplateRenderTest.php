<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Render;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ShopwarePluginSkeletonGenerator\Render\SimplePhpTemplateRender;

#[CoversClass(SimplePhpTemplateRender::class)]
class SimplePhpTemplateRenderTest extends TestCase
{
    private SimplePhpTemplateRender $templateRender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templateRender = new SimplePhpTemplateRender();
    }

    public function testRender(): void
    {
        $testParameter = ['name' => 'John Doe', 'age' => '21'];
        $templatePath = __DIR__ . '/../Fixtures/templates/template.php';  // path to a template file for testing
        $expectedRenderedTemplate = <<<EOF
            name: John Doe
            age: 21
            EOF;

        self::assertSame($expectedRenderedTemplate, $this->templateRender->render($templatePath, $testParameter));
    }

    public function testRenderEmptyOutput(): void
    {
        $testParameter = ['name' => 'John Doe', 'age' => 21];
        $templatePath = '/path/to/not-existent-template.php';

        self::assertSame('', $this->templateRender->render($templatePath, $testParameter));
    }
}
