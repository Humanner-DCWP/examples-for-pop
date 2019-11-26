<?php
namespace Leoloso\ExamplesForPoP\FieldValueResolvers;

use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\FieldResolvers\FieldResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\FieldValueResolvers\AbstractOperatorOrHelperFieldValueResolver;

class HelperFieldValueResolver extends AbstractOperatorOrHelperFieldValueResolver
{
    public static function getFieldNamesToResolve(): array
    {
        return [
            'contentMesh',
        ];
    }

    public function getSchemaFieldType(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $types = [
            'contentMesh' => SchemaDefinition::TYPE_OBJECT,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($fieldResolver, $fieldName);
    }

    public function getSchemaFieldArgs(FieldResolverInterface $fieldResolver, string $fieldName): array
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'contentMesh':
                return [
                    [
                        SchemaDefinition::ARGNAME_NAME => 'githubRepo',
                        SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                        SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('GitHub repository for which to fetch data, in format \'account/repo\' (eg: \'leoloso/PoP\')', 'examples-for-pop'),
                    ],
                    [
                        SchemaDefinition::ARGNAME_NAME => 'weatherZone',
                        SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                        SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('Zone from which to retrieve weather data, as listed in https://api.weather.gov/zones/forecast', 'examples-for-pop'),
                    ],
                    [
                        SchemaDefinition::ARGNAME_NAME => 'photoPage',
                        SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_INT,
                        SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('Page from which to fetch photo data', 'examples-for-pop'),
                    ],
                ];
        }

        return parent::getSchemaFieldArgs($fieldResolver, $fieldName);
    }

    public function getSchemaFieldDescription(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
            'contentMesh' => $translationAPI->__('Fetch \'content mesh\' data (i.e. data from different services required to power the application), including repository data from GitHub, weather data from the National Weather Service, and random photo data from Unsplash', 'examples-for-pop'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($fieldResolver, $fieldName);
    }

    public function resolveValue(FieldResolverInterface $fieldResolver, $resultItem, string $fieldName, array $fieldArgs = [])
    {
        $fieldQueryInterpreter = FieldQueryInterpreterFacade::getInstance();
        switch ($fieldName) {
            case 'contentMesh':
                return $fieldResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'getAsyncJSON',
                        [
                            'urls' => [
                                'github' => sprintf(
                                    'https://api.github.com/repos/%s',
                                    $fieldArgs['githubRepo'] ?? 'leoloso/PoP'
                                ),
                                'weather' => sprintf(
                                    'https://api.weather.gov/zones/forecast/%s/forecast',
                                    $fieldArgs['weatherZone'] ?? 'MOZ028'
                                ),
                                'photos' => sprintf(
                                    'https://picsum.photos/v2/list?page=%s&limit=10',
                                    $fieldArgs['photoPage'] ?? rand(1, 20)
                                )
                            ],
                        ]
                    )
                );
        }

        return parent::resolveValue($fieldResolver, $resultItem, $fieldName, $fieldArgs);
    }
}
