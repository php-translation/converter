<?php

namespace Translation\Converter\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\Bundle\Model\Metadata;
use Translation\Converter\Reader\JmsReader;
use Translation\Converter\Service\Converter;
use Translation\SymfonyStorage\Loader\XliffLoader;

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
        @unlink(self::$outputDir);
        @mkdir(self::$outputDir);
    }

    public function testConvertJMS()
    {
        $reader = new JmsReader();
        $converter = new Converter($reader, 'xlf');
        $converter->convert(self::$fixturesDir, self::$outputDir, ['en']);

        $catalogue = (new XliffLoader())->load(self::$outputDir.'/messages.en.xlf', 'en');
        $this->assertEquals('This is a bar.', $catalogue->get('bar'));
        $meta = new Metadata($catalogue->getMetadata('bar'));
        $this->assertTrue($meta->isApproved());
        $this->assertEquals('new', $meta->getState());
        $this->assertEquals('Tests/Translation/XliffMessageUpdaterTest.php', $meta->getSourceLocations()[0]['path']);
    }

    public function testConvertYaml()
    {
        $reader = new JmsReader();
        $converter = new Converter($reader, 'yml');
        $converter->convert(self::$fixturesDir, self::$outputDir, ['en']);

        $catalogue = (new XliffLoader())->load(self::$outputDir.'/admin.en.xlf', 'en');
        $this->assertEquals('Business', $catalogue->get('en.symbol.anonymous'));
    }
}
