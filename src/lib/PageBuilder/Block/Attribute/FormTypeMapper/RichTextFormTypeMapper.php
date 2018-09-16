<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformPageBuilderRichTextBlock\PageBuilder\Block\Attribute\FormTypeMapper;

use EzSystems\EzPlatformPageBuilderRichTextBlock\Form\Type\RichTextAttributeType;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Attribute\FormTypeMapper\AttributeFormTypeMapperInterface;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Definition\BlockAttributeDefinition;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Definition\BlockDefinition;
use Symfony\Component\Form\FormBuilderInterface;

class RichTextFormTypeMapper implements AttributeFormTypeMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function map(
        FormBuilderInterface $formBuilder,
        BlockDefinition $blockDefinition,
        BlockAttributeDefinition $blockAttributeDefinition,
        array $constraints = []): FormBuilderInterface
    {
        return $formBuilder->create('value', RichTextAttributeType::class);
    }
}
