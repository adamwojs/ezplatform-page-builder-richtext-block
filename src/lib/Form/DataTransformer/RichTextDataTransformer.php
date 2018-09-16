<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformPageBuilderRichTextBlock\Form\DataTransformer;

use DOMDocument;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\ConverterDispatcher;
use eZ\Publish\Core\FieldType\RichText\CustomTagsValidator;
use eZ\Publish\Core\FieldType\RichText\InternalLinkValidator;
use eZ\Publish\Core\FieldType\RichText\Normalizer;
use eZ\Publish\Core\FieldType\RichText\Validator;
use eZ\Publish\Core\FieldType\RichText\ValidatorDispatcher;
use Symfony\Component\Form\DataTransformerInterface;

class RichTextDataTransformer implements DataTransformerInterface
{
    const EMPTY_VALUE = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0"/>
EOT;

    /** @var \eZ\Publish\Core\FieldType\RichText\Converter */
    private $docbookToXhtml5EditConverter;

    /** @var \eZ\Publish\Core\FieldType\RichText\Validator */
    private $internalFormatValidator;

    /** @var \eZ\Publish\Core\FieldType\RichText\ConverterDispatcher */
    private $inputConverterDispatcher;

    /** @var \eZ\Publish\Core\FieldType\RichText\Normalizer */
    private $inputNormalizer;

    /** @var null|\eZ\Publish\Core\FieldType\RichText\ValidatorDispatcher */
    private $inputValidatorDispatcher;

    /** @var null|\eZ\Publish\Core\FieldType\RichText\InternalLinkValidator */
    private $internalLinkValidator;

    /** @var null|\eZ\Publish\Core\FieldType\RichText\CustomTagsValidator */
    private $customTagsValidator;

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\Converter $docbookToXhtml5EditConverter
     * @param \eZ\Publish\Core\FieldType\RichText\Validator $internalFormatValidator
     * @param \eZ\Publish\Core\FieldType\RichText\ConverterDispatcher $inputConverterDispatcher
     * @param \eZ\Publish\Core\FieldType\RichText\Normalizer $inputNormalizer
     * @param \eZ\Publish\Core\FieldType\RichText\ValidatorDispatcher|null $inputValidatorDispatcher
     * @param \eZ\Publish\Core\FieldType\RichText\InternalLinkValidator|null $internalLinkValidator
     * @param \eZ\Publish\Core\FieldType\RichText\CustomTagsValidator|null $customTagsValidator
     */
    public function __construct(
        Converter $docbookToXhtml5EditConverter,
        Validator $internalFormatValidator,
        ConverterDispatcher $inputConverterDispatcher,
        Normalizer $inputNormalizer,
        ?ValidatorDispatcher $inputValidatorDispatcher,
        ?InternalLinkValidator $internalLinkValidator,
        ?CustomTagsValidator $customTagsValidator)
    {
        $this->docbookToXhtml5EditConverter = $docbookToXhtml5EditConverter;
        $this->internalFormatValidator = $internalFormatValidator;
        $this->inputConverterDispatcher = $inputConverterDispatcher;
        $this->inputNormalizer = $inputNormalizer;
        $this->inputValidatorDispatcher = $inputValidatorDispatcher;
        $this->internalLinkValidator = $internalLinkValidator;
        $this->customTagsValidator = $customTagsValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): string
    {
        if (!$value) {
            return '';
        }

        $document = $this->loadXMLString($value);
        $document = $this->docbookToXhtml5EditConverter->convert($document);

        return $document->saveXML();
    }

    /**
     * {@inheritdoc}
     *
     * @see \eZ\Publish\Core\FieldType\RichText\Type::createValueFromInput
     */
    public function reverseTransform($value)
    {
        if ($value === null || empty($value)) {
            $value = self::EMPTY_VALUE;
        }

        if ($this->inputNormalizer !== null && $this->inputNormalizer->accept($value)) {
            $value = $this->inputNormalizer->normalize($value);
        }

        $document = $this->loadXMLString($value);

        if ($this->inputValidatorDispatcher !== null) {
            $errors = $this->inputValidatorDispatcher->dispatch($document);
            if (!empty($errors)) {
                throw new InvalidArgumentException(
                    '$inputValue',
                    'Validation of XML content failed: ' . implode("\n", $errors)
                );
            }
        }

        return $this->inputConverterDispatcher->dispatch($document)->saveXML();
    }

    /**
     * Creates \DOMDocument from given $xmlString.
     *
     * @param string $xml
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return \DOMDocument
     */
    private function loadXMLString(string $xml): DOMDocument
    {
        $document = new DOMDocument();

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // Options:
        // - substitute entities
        // - disable network access
        // - relax parser limits for document size/complexity
        $success = $document->loadXML($xml, LIBXML_NOENT | LIBXML_NONET | LIBXML_PARSEHUGE);
        if (!$success) {
            $messages = [];

            foreach (libxml_get_errors() as $error) {
                $messages[] = trim($error->message);
            }

            throw new InvalidArgumentException(
                '$inputValue',
                'Could not create XML document: ' . implode("\n", $messages)
            );
        }

        return $document;
    }
}
