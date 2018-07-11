<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Converter\Reader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class JmsReader implements LoaderInterface
{
    /**
     * @param mixed  $resource
     * @param string $locale
     * @param string $domain
     *
     * @return MessageCatalogue
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $previous = libxml_use_internal_errors(true);
        if (false === $doc = simplexml_load_file($resource)) {
            libxml_use_internal_errors($previous);
            $libxmlError = libxml_get_last_error();

            throw new \RuntimeException(sprintf('Could not load XML-file "%s": %s', $resource, $libxmlError->message));
        }

        libxml_use_internal_errors($previous);

        $doc->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        $doc->registerXPathNamespace('jms', 'urn:jms:translation');

        $hasReferenceFiles = in_array('urn:jms:translation', $doc->getNamespaces(true));

        $catalogue = new MessageCatalogue($locale);
        $catalogue->addResource(new FileResource($resource));

        /** @var \SimpleXMLElement $trans */
        foreach ($doc->xpath('//xliff:trans-unit') as $trans) {
            $id = ($resName = (string) $trans->attributes()->resname)
                ? $resName : (string) $trans->source;

            if (empty($id)) {
                continue;
            }

            $meta = [];
            if ($hasReferenceFiles) {
                foreach ($trans->xpath('./jms:reference-file') as $file) {
                    $line = (string) $file->attributes()->line;

                    $meta['notes'][] = ['category' => 'file-source', 'content' => sprintf('%s:%s', (string) $file, $line ? (int) $line : 0)];
                }
            }

            if ($meaning = (string) $trans->attributes()->extradata) {
                if (0 === strpos($meaning, 'Meaning: ')) {
                    $meaning = substr($meaning, 9);
                }

                $meta['notes'][] = ['category' => 'meaning', 'content' => $meaning];
            }
            if ($approved = (string) $trans->attributes()->approved) {
                $text = (string) $approved;
                $meta['notes'][] = ['category' => 'approved', 'content' => 'yes' == $text || 'true' == $text ? 'true' : 'false'];
            }

            foreach ($trans->target->attributes() as $name => $value) {
                if ('state' === $name) {
                    $meta['notes'][] = ['category' => 'state', 'content' => (string) $value];
                }
            }

            $catalogue->set($id, $trans->target, $domain);
            $catalogue->setMetadata($id, $meta, $domain);
        }

        return $catalogue;
    }
}
