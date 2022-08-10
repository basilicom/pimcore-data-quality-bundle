<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Model\Provider;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;
use Pimcore\Model\DataObject\DataQualityConfig;
use Pimcore\Tool;
use Symfony\Contracts\Translation\TranslatorInterface;

class ObjectFieldsProvider implements SelectOptionsProviderInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOptions($context, $fieldDefinition): array
    {
        $fieldName = null;
        if (isset($context['fieldname'])) {
            $fieldName = $context['fieldname'];
        }
        if ($fieldName !== 'Field' && $fieldName !== 'DataQualityField') {
            return [];
        }

        /** @var DataQualityConfig $object */
        $object = null;
        if (isset($context['object'])) {
            $object = $context['object'];
        }
        if (empty($object)) {
            return [];
        }

        $dataQualityClassId = $object->getDataQualityClass();
        if (empty($dataQualityClassId)) {
            return [];
        }

        $class = ClassDefinition::getById($dataQualityClassId);
        if (empty($class)) {
            return [];
        }

        /** @var Select $fieldDefinition */
        $data           = $fieldDefinition->getOptionsProviderData();
        $onlyFieldNames = false;
        if (!empty($data) && $data === 'onlyFieldNames') {
            $onlyFieldNames = true;
        }

        $result           = [];
        $localizedField   = [];
        $fieldDefinitions = $class->getFieldDefinitions();
        foreach ($fieldDefinitions as $name => $field) {
            // bastodo: object Bricks
            // bastodo: field Collections
            // bastodo: blocks
            if ($name === 'localizedfields') {
                $languages = Tool::getValidLanguages();

                /** @var Localizedfields $field */
                $children = $field->getFieldDefinitions();

                foreach ($children as $child) {
                    $title = $this->translator->trans($child->getTitle(), [], 'admin');
                    $value = $child->getName();
                    if (!$onlyFieldNames) {
                        $value .= '@@@' . $title;
                    }
                    $localizedField[] = [
                        'key'   => $title . ' (' . $child->getName() . ') #All',
                        'value' => $value,
                    ];

                    foreach ($languages as $language) {
                        $localizedField[] = [
                            'key'   => $title . ' (' . $child->getName() . ') #' . $language,
                            'value' => $value . '###' . $language,
                        ];
                    }
                }
            } else {
                $title = $this->translator->trans($field->getTitle(), [], 'admin');
                $value = $name;
                if (!$onlyFieldNames) {
                    $value .= '@@@' . $title;
                }
                $result[] = [
                    'key'   => $title . ' (' . $name . ')',
                    'value' => $value,
                ];
            }
        }

        return array_merge($result, $localizedField);
    }

    public function getDefaultValue($context, $fieldDefinition): ?string
    {
        return $fieldDefinition->getDefaultValue();
    }

    public function hasStaticOptions($context, $fieldDefinition): bool
    {
        return false;
    }
}
