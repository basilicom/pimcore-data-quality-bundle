<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Provider;

use Basilicom\DataQualityBundle\Definition\DefinitionException;
use Basilicom\DataQualityBundle\DefinitionsCollection\Factory\FieldDefinitionFactory;
use Basilicom\DataQualityBundle\DefinitionsCollection\FieldDefinition;
use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\DataQualityConfig;
use Pimcore\Model\DataObject\Fieldcollection\Data\DataQualityFieldDefinition;
use Pimcore\Model\Version as DataObjectVersion;

final class DataQualityProvider
{
    private FieldDefinitionFactory $fieldDefinitionFactory;

    public function __construct(FieldDefinitionFactory $fieldDefinitionFactory)
    {
        $this->fieldDefinitionFactory = $fieldDefinitionFactory;
    }

    public function setDataQualityPercent(AbstractObject $dataObject, array $items, string $fieldName): int
    {
        $value         = 0;
        $countTotal    = 0;
        $countComplete = 0;
        $setter        = 'set' . \ucfirst($fieldName);

        if (\method_exists(
            $dataObject,
            $setter
        )) {
            foreach ($items as $group) {
                foreach ($group['fields'] as $field) {
                    $countTotal = $countTotal + (1 * $field['weight']);
                    if ($field['valid']) {
                        $countComplete = $countComplete + (1 * $field['weight']);
                    }
                }
            }

            $value = (int) \round(($countComplete / $countTotal) * 100);

            DataObjectVersion::disable();

            $dataObject->$setter($value);
            $dataObject->save();

            DataObjectVersion::enable();
        }

        return $value;
    }

    /**
     * @return DataQualityConfig[]
     */
    public function getDataQualityConfigs(?AbstractObject $dataObject): array
    {
        $dataQualityConfigList = new DataQualityConfig\Listing();

        $dataQualityConfigs = [];
        foreach ($dataQualityConfigList as $dataQualityConfig) {
            $dataQualityClass = $dataQualityConfig->getDataQualityClass();
            if ($dataObject && $dataObject->getClassId() === $dataQualityClass) {
                if ($dataQualityConfig->isPublished()) {
                    $dataQualityConfigs[$dataQualityConfig->getId()] = $dataQualityConfig;
                }
            }
        }

        return $dataQualityConfigs;
    }

    /**
     * @throws DataQualityException|DefinitionException
     */
    public function calculateDataQuality(AbstractObject $dataObject, DataQualityConfig $dataQualityConfig): array
    {
        $dataQualityRules = $this->getDataQualityRules($dataQualityConfig);

        $data = ['items' => []];

        foreach ($dataQualityRules as $dataQualityRuleGroupName => $dataQualityRuleGroup) {
            $fields = [];
            foreach ($dataQualityRuleGroup as $fieldDefinition) {
                /** @var FieldDefinition $fieldDefinition */
                $getter = 'get' . $fieldDefinition->getFieldName();
                $isLocalizedField = false;
                if (method_exists($dataObject, $getter)) {
                    $classFieldDefinition = $dataObject->getClass()->getFieldDefinition($fieldDefinition->getFieldName());
                    if (empty($classFieldDefinition)) {

                        // try to find the field in localized fields:

                        $lf = $dataObject->getClass()->getFieldDefinition("localizedfields");
                        if ($lf) {
                            $classFieldDefinition = $lf->getFieldDefinition($fieldDefinition->getFieldName());
                            $isLocalizedField = true;
                        } else {

                            throw new DataQualityException('fieldtype for field ' . $fieldDefinition->getFieldName() . ' is not supported, yet.');
                        }

                    }

                    if ($isLocalizedField) {

                        # we ARE using the fallback definitions!
                        #\Pimcore\Model\DataObject\Localizedfield::setGetFallbackValues(false);

                        # the validation condition should be applied to ALL languages
                        # @bastodo make this behaviour configurable via conditionclass parameters!
                        $allLanguagesValid = true;

                        $languages = \Pimcore\Tool::getValidLanguages();
                        foreach ($languages as $language) {

                            $value                = $dataObject->$getter($language);
                            $valid = $fieldDefinition->getConditionClass()->validate(
                                $value,
                                $classFieldDefinition,
                                $fieldDefinition->getParameters()
                            );

                            $allLanguagesValid = $allLanguagesValid && $valid;
                        }

                    } else {

                        $value                = $dataObject->$getter();
                        $valid = $fieldDefinition->getConditionClass()->validate(
                            $value,
                            $classFieldDefinition,
                            $fieldDefinition->getParameters()
                        );

                    }


                    $fields[] = [
                        'valid'  => $valid,
                        'name'   => $fieldDefinition->getTitle(),
                        'weight' => $fieldDefinition->getWeight(),
                    ];
                }
            }
            $data['items'][] = [
                'name'   => $dataQualityRuleGroupName,
                'fields' => $fields
            ];
        }

        $data['percent'] = $this->setDataQualityPercent($dataObject, $data['items'], $dataQualityConfig->getDataQualityField());
        $data['title']   = $dataQualityConfig->getDataQualityName();

        return $data;
    }

    public function getDataQualityRules(DataQualityConfig $dataQualityConfig): array
    {
        $fieldcollection = $dataQualityConfig->getDataQualityRules();
        $items           = $fieldcollection->getItems();

        $rules = [];

        /** @var DataQualityFieldDefinition $item */
        foreach ($items as $item) {
            $group           = empty($item->getGroup()) ? FieldDefinitionFactory::DEFAULT_GROUP : $item->getGroup();
            $rules[$group][] = $this->fieldDefinitionFactory->get($item);
        }

        return $rules;
    }
}
