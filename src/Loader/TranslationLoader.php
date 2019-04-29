<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Converter\Loader;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
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
     * @param LoaderInterface $loader
     * @param string|string[] $format
     */
    public function __construct(LoaderInterface $loader, $format)
    {
        $this->loader = $loader;
        $this->format = $format;
    }

    /**
     * Loads translation messages from a directory to the catalogue.
     *
     * @param string           $directory the directory to look into
     * @param MessageCatalogue $catalogue the catalogue
     *
     * @deprecated Use read instead.
     */
    public function loadMessages($directory, MessageCatalogue $catalogue)
    {
        return $this->read($directory, $catalogue);
    }

    /**
     * Loads translation messages from a directory to the catalogue.
     *
     * @param string           $directory the directory to look into
     * @param MessageCatalogue $catalogue the catalogue
     */
    public function read($directory, MessageCatalogue $catalogue)
    {
        if (!is_dir($directory)) {
            return;
        }

        // load any existing translation files
        $patterns = $this->getTranslationFilePatterns($catalogue);
        $files = $this->getTranslationFiles($directory, $patterns);
        foreach ($files as $file) {
            $domain = $this->getDomainFromFilename($file->getFilename(), $patterns);
            $catalogue->addCatalogue($this->loader->load($file->getPathname(), $catalogue->getLocale(), $domain));
        }
    }

    protected function getTranslationFiles($directory, $patterns)
    {
        $finder = new Finder();
        foreach ($patterns as $pattern) {
            $finder->name($pattern);
        }

        return $finder
            ->files()
            ->in($directory);
    }

    protected function getDomainFromFilename($filename, $patterns)
    {
        foreach ($patterns as $pattern) {
            $extension = str_replace('*.', '', $pattern);
            $length = strlen($extension);
            if (substr($filename, -$length) === $extension) {
                return substr($filename, 0, -1 * $length - 1);
            }
        }

        return $filename;
    }

    protected function getTranslationFilePatterns(MessageCatalogue $catalogue)
    {
        if ($this->format && !is_array($this->format)) {
            $this->format = [$this->format];
        }

        $patterns = [];
        foreach ($this->format as $ext) {
            $patterns[] = sprintf('*.%s.%s', $catalogue->getLocale(), $ext);
        }

        return $patterns;
    }
}
