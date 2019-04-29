<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Converter\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use Translation\Bundle\Model\Metadata;
use Translation\Converter\Reader\JmsReader;
use Translation\Converter\Service\Converter;
use Translation\SymfonyStorage\Loader\XliffLoader;
use Symfony\Component\Translation\Loader;

class ConverterTest extends TestCase
{
    protected static $fixturesDir;

    protected static $outputDir;

    public static function setUpBeforeClass()
    {
        self::$outputDir = sys_get_temp_dir().'/translation_converter_output';
        self::$fixturesDir = __DIR__.'/../../Fixtures';
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        @mkdir(self::$outputDir, 0777, true);
        $files = glob(self::$outputDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testConvertJMS()
    {
        $reader = new JmsReader();
        $converter = new Converter($reader, ['xlf', 'xliff']);
        $converter->convert(self::$fixturesDir, self::$outputDir, ['en']);

        $catalogue = (new XliffLoader())->load(self::$outputDir.'/messages.en.xlf', 'en');
        $this->assertEquals('This is a bar.', $catalogue->get('bar'));
        $meta = new Metadata($catalogue->getMetadata('bar'));
        $this->assertTrue($meta->isApproved());
        $this->assertEquals('new', $meta->getState());
        $this->assertEquals('Tests/Translation/XliffMessageUpdaterTest.php', $meta->getSourceLocations()[0]['path']);

        $catalogue = (new XliffLoader())->load(self::$outputDir.'/xliff.en.xlf', 'en');
        $this->assertEquals('This is a bar.', $catalogue->get('bar'));
        $meta = new Metadata($catalogue->getMetadata('bar'));
        $this->assertTrue($meta->isApproved());
        $this->assertEquals('new', $meta->getState());
        $this->assertEquals('Tests/Translation/XliffMessageUpdaterTest.php', $meta->getSourceLocations()[0]['path']);
    }

    public function testConvertYaml()
    {
        $reader = new Loader\YamlFileLoader();
        $converter = new Converter($reader, 'yml');
        $converter->convert(self::$fixturesDir, self::$outputDir, ['en']);

        $catalogue = (new XliffLoader())->load(self::$outputDir.'/admin.en.xlf', 'en');
        $this->assertEquals('Business', $catalogue->get('en.symbol.anonymous'));
    }
}
