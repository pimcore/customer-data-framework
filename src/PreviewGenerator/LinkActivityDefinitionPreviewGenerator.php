<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\PreviewGenerator;

use Pimcore\Model\DataObject\ClassDefinition\PreviewGeneratorInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\Routing\RouterInterface;

class LinkActivityDefinitionPreviewGenerator implements PreviewGeneratorInterface
{
    public function __construct(protected RouterInterface $router)
    {
    }

    public function generatePreviewUrl(Concrete $object, array $params): string
    {
        return $this->router->generate(
            'cmf_link_activity_definition_preview',
            ['pimcore_object_preview' => $object->getId()]
        );
    }

    public function getPreviewConfig(Concrete $object): array
    {
        return [];
    }
}
