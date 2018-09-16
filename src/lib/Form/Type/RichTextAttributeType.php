<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformPageBuilderRichTextBlock\Form\Type;

use EzSystems\EzPlatformPageBuilderRichTextBlock\Form\DataTransformer\RichTextDataTransformerFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class RichTextAttributeType extends AbstractType
{
    /** @var \EzSystems\EzPlatformPageBuilderRichTextBlock\Form\DataTransformer\RichTextDataTransformerFactory */
    private $richTextDataTransformerFactory;

    /**
     * @param \EzSystems\EzPlatformPageBuilderRichTextBlock\Form\DataTransformer\RichTextDataTransformerFactory $richTextDataTransformerFactory
     */
    public function __construct(RichTextDataTransformerFactory $richTextDataTransformerFactory)
    {
        $this->richTextDataTransformerFactory = $richTextDataTransformerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->richTextDataTransformerFactory->createDataTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
