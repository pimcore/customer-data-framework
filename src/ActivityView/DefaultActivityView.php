<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\ActivityView;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;

class DefaultActivityView implements ActivityViewInterface
{
    /**
     * @var ViewFormatterInterface
     */
    protected $viewFormatter;

    /**
     * @param ViewFormatterInterface $viewFormatter
     */
    public function __construct(ViewFormatterInterface $viewFormatter)
    {
        $this->viewFormatter = $viewFormatter;
    }

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return array|false
     */
    public function getOverviewAdditionalData(ActivityStoreEntryInterface $activityEntry)
    {
        $implementationClass = $activityEntry->getImplementationClass();
        if (class_exists($implementationClass)) {
            if (method_exists($implementationClass, 'cmfGetOverviewData')) {
                return $implementationClass::cmfgetOverviewData($activityEntry);
            }
        }

        return false;
    }

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return array
     */
    public function getDetailviewData(ActivityStoreEntryInterface $activityEntry)
    {
        $implementationClass = $activityEntry->getImplementationClass();
        if (class_exists($implementationClass)) {
            if (method_exists($implementationClass, 'cmfGetDetailviewData')) {
                $data = $implementationClass::cmfGetDetailviewData($activityEntry);

                return $data ?: [];
            }
        }

        return [];
    }

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return string|false
     */
    public function getDetailviewTemplate(ActivityStoreEntryInterface $activityEntry)
    {
        $implementationClass = $activityEntry->getImplementationClass();
        if (class_exists($implementationClass)) {
            if (method_exists($implementationClass, 'cmfGetDetailviewTemplate')) {
                return $implementationClass::cmfGetDetailviewTemplate($activityEntry);
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function translate($messageId, $parameters = [])
    {
        return $this->viewFormatter->translate($messageId, $parameters);
    }

    /**
     * @param       $implementationClass
     * @param array $attributes
     * @param array $visibleKeys
     *
     * @return array
     */
    public function formatAttributes($implementationClass, array $attributes, array $visibleKeys = [])
    {
        $class = false;
        if ($implementationClass) {
            if (method_exists($implementationClass, 'classId')) {
                $class = ClassDefinition::getById($implementationClass::classId());
            }
        }

        $attributes = $this->extractVisibleAttributes($attributes, $visibleKeys);
        if (method_exists($implementationClass, 'cmfGetAttributeDataTypes')) {
            $dataTypes = (array)$implementationClass::cmfGetAttributeDataTypes();
        }
        $dataTypes = is_array($dataTypes) ? $dataTypes : [];

        $result = [];
        $vf = $this->viewFormatter;

        foreach ($attributes as $key => $value) {
            if (!is_scalar($value)) {
                unset($attributes[$key]);
                continue;
            }

            if ($class && $fd = $class->getFieldDefinition($key)) {
                $result[$vf->getLabelByFieldDefinition($fd)] = $vf->formatValueByFieldDefinition($fd, $value);
                continue;
            } elseif (isset($dataTypes[$key])) {
                if ($dataTypes[$key] == ActivityInterface::DATATYPE_BOOL) {
                    $value = $this->viewFormatter->formatBooleanValue($value);
                }
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Creates a link/button to an object, asset or document.
     *
     * @param $id
     * @param $elementType
     * @param string $buttonCssClass
     * @param string $buttonTranslationKey
     * @return string
     */
    public function createPimcoreElementLink($id, $elementType, $buttonCssClass = 'btn btn-sm btn-default', $buttonTranslationKey = 'cmf_open')
    {
        $elementType = mb_strtolower($elementType);

        if(!in_array($elementType, ['object', 'asset', 'document'])) {
            throw new \RuntimeException(sprintf('"%s" is not valid element type (object, asset + document is allowed)', $elementType));
        }

        if($elementType == 'object' && ($object = AbstractObject::getById($id))) {
            $link = sprintf("window.top.pimcore.helpers.openObject(%s, '%s')", $id, $object->getType());

        } elseif($elementType == 'document' && ($document = Document::getById($id))) {
            $link = sprintf("window.top.pimcore.helpers.openDocument(%s, '%s')", $id, $document->getType());
        } elseif($elementType == 'asset' && ($asset = Asset::getById($id))) {
            $link = sprintf("window.top.pimcore.helpers.openAsset(%s, '%s')", $id, $asset->getType());
        }

        if(!$link) {
            return '';
        }

        return sprintf('<a href="javascript:%s" class="%s">%s</a>', $link, $buttonCssClass, $this->translate($buttonTranslationKey));
    }
    
    /**
     * @param array $attributes
     * @param array $visibleKeys
     *
     * @return array
     */
    private function extractVisibleAttributes(array $attributes, array $visibleKeys)
    {
        if (sizeof($visibleKeys)) {
            $visibleAttributes = [];
            foreach ($visibleKeys as $column) {
                $visibleAttributes[$column] = $attributes[$column];
            }

            return $visibleAttributes;
        }

        return $attributes;
    }
}
