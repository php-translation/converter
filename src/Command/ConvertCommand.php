<?php

namespace Translation\Converter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Loader;
use Translation\Converter\Reader\JmsReader;
use Translation\Converter\Service\Converter;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConvertCommand extends Command
{
    const SUPPORTED_FORMATS = ['jms', 'csv', 'ini', 'json', 'mo', 'php', 'yml', 'xlf'];

    protected function configure()
    {
        $this
            ->setName('translation:convert')
            ->setDescription('Convert your existing translation files to Xliff')
            ->addArgument('input_dir', InputArgument::REQUIRED, 'Where are your existing translations?')
            ->addArgument('output_dir', InputArgument::REQUIRED, 'Where should we put the new translations?')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The format to convert from. Supported formats are: '.implode(', ', self::SUPPORTED_FORMATS))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = scandir($input->getArgument('input_dir'));

        $formats = [];
        $locales = [];
        foreach ($files as $file) {
            if (preg_match('|.+\.(.+)\.(.+)|', $file, $matches)) {
                $locales[] = $matches[1];
                $formats[] = $matches[2];
            }
        }

        if (null === $format = $input->getOption('format')) {
            $formats = array_unique($formats);
            if (count($formats) !== 1) {
                throw new \InvalidArgumentException('More than one format found. Please specify a format with --format=xxx');
            }
            $format = reset($formats);
        }

        $reader = $this->getReader($format);
        $converter = new Converter($reader, $format === 'jms' ? 'xlf' : $format);
        $converter->convert($input->getArgument('input_dir'), $input->getArgument('output_dir'), array_unique($locales));
    }

    /**
     * @param string $format
     *
     * @return Loader\LoaderInterface
     */
    private function getReader($format)
    {
        switch ($format) {
            case 'jms':
                return new JmsReader();
            case 'csv':
                return new Loader\CsvFileLoader();
            case 'ini':
                return new Loader\IniFileLoader();
            case 'json':
                return new Loader\JsonFileLoader();
            case 'mo':
                return new Loader\MoFileLoader();
            case 'php':
                return new Loader\PhpFileLoader();
            case 'yml':
            case 'yaml':
                return new Loader\YamlFileLoader();
            case 'xlf':
            case 'xliff':
                return new Loader\XliffFileLoader();
            default:
                throw new \InvalidArgumentException(sprintf('Format "%s" is not a valid format', $format));
        }
    }
}
