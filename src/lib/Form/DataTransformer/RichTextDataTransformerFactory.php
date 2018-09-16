<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformPageBuilderRichTextBlock\Form\DataTransformer;

use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\ConverterDispatcher;
use eZ\Publish\Core\FieldType\RichText\CustomTagsValidator;
use eZ\Publish\Core\FieldType\RichText\InternalLinkValidator;
use eZ\Publish\Core\FieldType\RichText\Normalizer;
use eZ\Publish\Core\FieldType\RichText\Validator;
use eZ\Publish\Core\FieldType\RichText\ValidatorDispatcher;
use Symfony\Component\Form\DataTransformerInterface;

final class RichTextDataTransformerFactory
{
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

    public function createDataTransformer(): DataTransformerInterface
    {
        return new RichTextDataTransformer(
            $this->docbookToXhtml5EditConverter,
            $this->internalFormatValidator,
            $this->inputConverterDispatcher,
            $this->inputNormalizer,
            $this->inputValidatorDispatcher,
            $this->internalLinkValidator,
            $this->customTagsValidator
        );
    }
}
