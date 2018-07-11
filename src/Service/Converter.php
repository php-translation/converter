<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Converter\Service;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Translation\Converter\Loader\TranslationLoader;
use Translation\SymfonyStorage\Dumper\XliffDumper;
use Translation\SymfonyStorage\FileStorage;

/**
 * Convert any translation format to XLF.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Converter
{
    /**
     * @var \Translation\SymfonyStorage\TranslationLoader
     */
    private $reader;

    /**
     * @var TranslationWriter
     */
    private $writer;

    /**
     * @param LoaderInterface $reader
     * @param string          $format
     */
    public function __construct(LoaderInterface $reader, $format)
    {
        $this->reader = new TranslationLoader($reader, $format);
        $this->writer = new TranslationWriter();
        $this->writer->disableBackup();
        $this->writer->addDumper('xlf', new XliffDumper());
    }

    /**
     * @param string $inputDir
     * @param string $outputDir
     * @param array  $locales
     */
    public function convert($inputDir, $outputDir, array $locales)
    {
        $inputDir = realpath($inputDir);
        $outputDir = realpath($outputDir);
        $inputStorage = new FileStorage($this->writer, $this->reader, [$inputDir]);
        $outputStorage = new FileStorage($this->writer, $this->reader, [$outputDir], ['xliff_version' => '2.0']);
        foreach ($locales as $locale) {
            $inputCatalogue = new MessageCatalogue($locale);
            $outputCatalogue = new MessageCatalogue($locale);

            $inputStorage->export($inputCatalogue);
            foreach ($inputCatalogue->all() as $domain => $messages) {
                $outputCatalogue->add($messages, $domain);
                foreach ($messages as $id => $message) {
                    $outputCatalogue->setMetadata($id, $inputCatalogue->getMetadata($id, $domain), $domain);
                }
            }

            // rewrite the resources to new path.
            /** @var FileResource $resource */
            foreach ($inputCatalogue->getResources() as $resource) {
                $path = str_replace($inputDir, $outputDir, $resource->getResource());

                // rewrite $path extension to be xlf
                $path = substr($path, 0, strrpos($path, '.')).'.xlf';

                // Make sure file exists
                file_put_contents($path, '');

                $outputCatalogue->addResource(new FileResource($path));
            }

            $outputStorage->import($outputCatalogue);
        }
    }
}
