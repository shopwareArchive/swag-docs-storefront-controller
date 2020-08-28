<?php declare(strict_types=1);

namespace Swag\ExtendJsPlugin\tests;

use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class UsedClassesAvailableTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testClassesAreInstantiable(): void
    {
        $namespace = str_replace('Tests', '', __NAMESPACE__);

        $files = $this->getPluginClasses();
        foreach ($files as $file) {
            if (!preg_match('/.*.php$/', $file->getRelativePathname())) {
                continue;
            }

            $classRelativePath = str_replace(['.php', '/'], ['', '\\'], $file->getRelativePathname());

            $this->getMockBuilder($namespace . '\\' . $classRelativePath)
                ->disableOriginalConstructor()
                ->getMock();
        }

        static::assertCount(5, $files);
    }

    private function getPluginClasses(): Finder
    {
        $finder = new Finder();
        $finder->in(realpath(__DIR__ . '/../src'));
        $finder->exclude(['tests', 'bin', 'node_modules']);

        return $finder->files()->name('/.*\.(xml|twig|php)$/');
    }
}
