<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Model\Provider;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;
use Pimcore\Model\DataObject\DataQualityConfig;
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
        $fieldName = $context['fieldname'];
        if ($fieldName !== 'Field' && $fieldName !== 'DataQualityField') {
            return [];
        }

        /** @var DataQualityConfig $object */
        $object = $context['object'];
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
            if ($name === 'localizedfields') {
                /** @var Localizedfields $field */
                $children = $field->getFieldDefinitions();

                foreach ($children as $child) {
                    $title = $this->translator->trans($child->getTitle(), [], 'admin');
                    $value = $child->getName();
                    if (!$onlyFieldNames) {
                        $value .= '@@@' . $title;
                    }
                    $localizedField[] = [
                        'key'   => 'Localized: ' . $title . ' (' . $child->getName() . ')',
                        'value' => $value,
                    ];
                }
                // bastodo: maybe use this for sorting localized Fields
//              $bla = $class->getLayoutDefinitions();
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
