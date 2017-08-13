<?php

namespace Translation\Converter\Loader;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\SymfonyStorage\TranslationLoader as TranslationLoaderInterface;

class TranslationLoader implements TranslationLoaderInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var string
     */
    private $format;

    /**
     *
     * @param LoaderInterface $loader
     * @param string $format
     */
    public function __construct(LoaderInterface $loader, $format)
    {
        $this->loader = $loader;
        $this->format = $format;
    }

    /**
     * Loads translation messages from a directory to the catalogue.
     *
     * @param string                    $directory the directory to look into
     * @param MessageCatalogue $catalogue the catalogue
     */
    public function loadMessages($directory, MessageCatalogue $catalogue)
    {
        if (!is_dir($directory)) {
            return;
        }

        // load any existing translation files
        $finder = new Finder();
        $extension = $catalogue->getLocale().'.'.$this->format;
        $files = $finder->files()->name('*.'.$extension)->in($directory);
        foreach ($files as $file) {
            $domain = substr($file->getFilename(), 0, -1 * strlen($extension) - 1);
            $catalogue->addCatalogue($this->loader->load($file->getPathname(), $catalogue->getLocale(), $domain));
        }

    }
}
