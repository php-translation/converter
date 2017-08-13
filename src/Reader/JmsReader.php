<?php

declare(strict_types=1);

namespace Translation\Converter\Reader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class JmsReader implements LoaderInterface
{
    /**
     * @param mixed $resource
     * @param string $locale
     * @param string $domain
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

            $meta['notes'][] = ['id'=>'approved', 'content' => $trans['approved']];

            if (isset($trans->target['state'])) {
                // Redo this with our "status"
                $meta['notes'][] = ['id'=>'state', 'content' => $trans['state']];
            }

            if ($hasReferenceFiles) {
                foreach ($trans->xpath('./jms:reference-file') as $file) {
                    $line = (string) $file->attributes()->line;

                    $meta['notes'][] = ['content' => 'file-source', 'from'=>sprintf('%s:%s', (string) $file, $line ? (integer) $line : 0)];
                }
            }

            if ($meaning = (string) $trans->attributes()->extradata) {
                if (0 === strpos($meaning, 'Meaning: ')) {
                    $meaning = substr($meaning, 9);
                }

                $meta['notes'][] = ['id'=>'meaning', 'content' => $meaning];
            }

            $catalogue->set($id, $trans->target, $domain);
            $catalogue->setMetadata($id, $meta, $domain);
        }

        return $catalogue;
    }
}
