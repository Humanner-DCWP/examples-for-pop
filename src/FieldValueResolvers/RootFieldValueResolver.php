<?php
namespace Leoloso\ExamplesForPoP\FieldValueResolvers;

use PoP\Engine\Misc\Extract;
use PoP\ComponentModel\GeneralUtils;
use PoP\API\FieldResolvers\RootFieldResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\FieldResolvers\FieldResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\FieldValueResolvers\AbstractDBDataFieldValueResolver;

class RootFieldValueResolver extends AbstractDBDataFieldValueResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(RootFieldResolver::class);
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
            'meshServices',
            'meshServiceData',
            'contentMesh',
        ];
    }

    public function getSchemaFieldType(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $types = [
            'meshServices' => TypeCastingHelpers::combineTypes(SchemaDefinition::TYPE_ARRAY, SchemaDefinition::TYPE_URL),
            'meshServiceData' => SchemaDefinition::TYPE_OBJECT,
            'contentMesh' => SchemaDefinition::TYPE_OBJECT,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($fieldResolver, $fieldName);
    }

    public function getSchemaFieldArgs(FieldResolverInterface $fieldResolver, string $fieldName): array
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'meshServices':
            case 'meshServiceData':
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
            'meshServices' => $translationAPI->__('Services required to create a \'content mesh\' for the application: GitHub data for a specific repository, weather data from the National Weather Service for a specific zone, and random photo data from Unsplash', 'examples-for-pop'),
            'meshServiceData' => $translationAPI->__('Retrieve data from the mesh services', 'examples-for-pop'),
            'contentMesh' => $translationAPI->__('Retrieve data from the mesh services and create a \'content mesh\'', 'examples-for-pop'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($fieldResolver, $fieldName);
    }

    public function resolveValue(FieldResolverInterface $fieldResolver, $resultItem, string $fieldName, array $fieldArgs = [])
    {
        $fieldQueryInterpreter = FieldQueryInterpreterFacade::getInstance();
        switch ($fieldName) {
            case 'meshServices':
                return [
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
                ];
            case 'meshServiceData':
                return $fieldResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'getAsyncJSON',
                        [
                            'urls' => $fieldResolver->resolveValue(
                                $resultItem,
                                $fieldQueryInterpreter->getField(
                                    'meshServices',
                                    $fieldArgs
                                )
                            ),
                        ]
                    )
                );
            case 'contentMesh':
                $meshServiceData = $fieldResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'meshServiceData',
                        $fieldArgs
                    )
                );
                if (GeneralUtils::isError($meshServiceData)) {
                    return $meshServiceData;
                }
                $meshServiceData = (array)$meshServiceData;
                return [
                    'weatherForecast' => Extract::getDataFromPath($fieldName, $meshServiceData, 'weather.periods'),
                    'photoGalleryURLs' => Extract::getDataFromPath($fieldName, $meshServiceData, 'photos.url'),
                    'githubMeta' => [
                        'description' => Extract::getDataFromPath($fieldName, $meshServiceData, 'github.description'),
                        'starCount' => Extract::getDataFromPath($fieldName, $meshServiceData, 'github.stargazers_count'),
                    ],
                ];
        }

        return parent::resolveValue($fieldResolver, $resultItem, $fieldName, $fieldArgs);
    }
}
