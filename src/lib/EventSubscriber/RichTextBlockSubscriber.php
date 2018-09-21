<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformPageBuilderRichTextBlock\EventSubscriber;

use DOMDocument;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Definition\BlockAttributeDefinition;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Definition\BlockDefinitionEvents;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Definition\Event\BlockDefinitionEvent;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Renderer\BlockRenderEvents;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Renderer\Event\PreRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RichTextBlockSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BlockRenderEvents::getBlockPreRenderEventName('richtext') => 'onBlockPreRender',
            BlockDefinitionEvents::getBlockDefinitionEventName('richtext') => 'onBlockDefinition',
        ];
    }

    /**
     * @param \EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Definition\Event\BlockDefinitionEvent $event
     */
    public function onBlockDefinition(BlockDefinitionEvent $event): void
    {
        $definition = $event->getDefinition();

        $contentAttributeDefinition = new BlockAttributeDefinition();
        $contentAttributeDefinition->setIdentifier('content');
        $contentAttributeDefinition->setName('Content');
        $contentAttributeDefinition->setType('richtext');
        $contentAttributeDefinition->setCategory('default');
        $contentAttributeDefinition->setConstraints([]);

        $attributes = $definition->getAttributes();
        $attributes['content'] = $contentAttributeDefinition;

        $definition->setAttributes($attributes);
    }

    /**
     * @param \EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Renderer\Event\PreRenderEvent $event
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function onBlockPreRender(PreRenderEvent $event): void
    {
        $renderRequest = $event->getRenderRequest();

        $parameters = $renderRequest->getParameters();
        $parameters['document'] = null;

        $xml = $event->getBlockValue()->getAttribute('content')->getValue();
        if (!empty($xml)) {
            $parameters['document'] = $this->loadXMLString($xml);
        }

        $renderRequest->setParameters($parameters);
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
