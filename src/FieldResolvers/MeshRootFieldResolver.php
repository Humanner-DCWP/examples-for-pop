<?php

declare(strict_types=1);

namespace Leoloso\ExamplesForPoP\FieldResolvers;

use PoP\ComponentModel\ErrorUtils;
use PoP\ComponentModel\Misc\GeneralUtils;
use PoP\Engine\TypeResolvers\RootTypeResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;

class MeshRootFieldResolver extends AbstractDBDataFieldResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(RootTypeResolver::class);
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
            'meshServices',
            'meshServiceData',
            'contentMesh',
        ];
    }

    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $types = [
            'meshServices' => TypeCastingHelpers::makeArray(SchemaDefinition::TYPE_URL),
            'meshServiceData' => SchemaDefinition::TYPE_OBJECT,
            'contentMesh' => SchemaDefinition::TYPE_OBJECT,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }

    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'meshServices':
            case 'meshServiceData':
            case 'contentMesh':
                return array_merge(
                    $schemaFieldArgs,
                    [
                        [
                            SchemaDefinition::ARGNAME_NAME => 'githubRepo',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                            SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('GitHub repository for which to fetch data, in format \'account/repo\' (eg: \'leoloso/PoP\')', 'examples-for-pop'),
                            SchemaDefinition::ARGNAME_DEFAULT_VALUE => 'leoloso/PoP',
                        ],
                        [
                            SchemaDefinition::ARGNAME_NAME => 'weatherZone',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                            SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('Zone from which to retrieve weather data, as listed in https://api.weather.gov/zones/forecast', 'examples-for-pop'),
                            SchemaDefinition::ARGNAME_DEFAULT_VALUE => 'MOZ028',
                        ],
                        [
                            SchemaDefinition::ARGNAME_NAME => 'photoPage',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_INT,
                            SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('Page from which to fetch photo data. Default value: A random number between 1 and 20', 'examples-for-pop'),
                            // SchemaDefinition::ARGNAME_DEFAULT_VALUE => 'A random number between 1 and 20',
                        ],
                    ]
                );
        }

        return $schemaFieldArgs;
    }

    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
            'meshServices' => $translationAPI->__('Services required to create a \'content mesh\' for the application: GitHub data for a specific repository, weather data from the National Weather Service for a specific zone, and random photo data from Unsplash', 'examples-for-pop'),
            'meshServiceData' => $translationAPI->__('Retrieve data from the mesh services', 'examples-for-pop'),
            'contentMesh' => $translationAPI->__('Retrieve data from the mesh services and create a \'content mesh\'', 'examples-for-pop'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }

    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
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
                $meshServices = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'meshServices',
                        $fieldArgs
                    ),
                    $variables,
                    $expressions,
                    $options
                );
                if (GeneralUtils::isError($meshServices)) {
                    return $meshServices;
                }
                $meshServices = (array)$meshServices;
                return $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'getAsyncJSON',
                        [
                            'urls' => $meshServices,
                        ]
                    ),
                    $variables,
                    $expressions,
                    $options
                );
            case 'contentMesh':
                $meshServiceData = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'meshServiceData',
                        $fieldArgs
                    ),
                    $variables,
                    $expressions,
                    $options
                );
                if (GeneralUtils::isError($meshServiceData)) {
                    return $meshServiceData;
                }
                $meshServiceData = (array)$meshServiceData;
                $weatherForecast = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'extract',
                        [
                            'object' => $meshServiceData,
                            'path' => 'weather.periods',
                        ]
                    ),
                    $variables,
                    $expressions,
                    $options
                );
                $photoGalleryURLs = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'extract',
                        [
                            'object' => $meshServiceData,
                            'path' => 'photos.url',
                        ]
                    ),
                    $variables,
                    $expressions,
                    $options
                );
                $githubMetaDescription = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'extract',
                        [
                            'object' => $meshServiceData,
                            'path' => 'github.description',
                        ]
                    ),
                    $variables,
                    $expressions,
                    $options
                );
                $githubMetaStarCount = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'extract',
                        [
                            'object' => $meshServiceData,
                            'path' => 'github.stargazers_count',
                        ]
                    ),
                    $variables,
                    $expressions,
                    $options
                );
                $maybeErrors = array_filter(
                    [
                        $weatherForecast,
                        $photoGalleryURLs,
                        $githubMetaDescription,
                        $githubMetaStarCount,
                    ],
                    function ($fieldValue) {
                        return GeneralUtils::isError($fieldValue);
                    }
                );
                if (!empty($maybeErrors)) {
                    return ErrorUtils::getNestedErrorsFieldError($maybeErrors, $fieldName);
                }
                return [
                    'weatherForecast' => $weatherForecast,
                    'photoGalleryURLs' => $photoGalleryURLs,
                    'githubMeta' => [
                        'description' => $githubMetaDescription,
                        'starCount' => $githubMetaStarCount,
                    ],
                ];
        }

        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
}
