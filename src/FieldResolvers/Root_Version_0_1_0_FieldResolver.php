<?php
namespace Leoloso\ExamplesForPoP\FieldResolvers;

use PoP\ComponentModel\GeneralUtils;
use PoP\API\TypeResolvers\RootTypeResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;

class Root_Version_0_1_0_FieldResolver extends AbstractDBDataFieldResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(RootTypeResolver::class);
    }

    public static function getPriorityToAttachClasses(): ?int
    {
        // Higher priority => Process befor the current version fieldResolver
        return 20;
    }

    public function getSchemaFieldVersion(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        return '0.1.0';
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
            'userServiceURLs',
            'userServiceData',
        ];
    }

    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $types = [
            'userServiceURLs' => TypeCastingHelpers::makeArray(SchemaDefinition::TYPE_URL),
            'userServiceData' => SchemaDefinition::TYPE_OBJECT,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }

    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'userServiceURLs':
            case 'userServiceData':
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
            'userServiceURLs' => $translationAPI->__('Services required to create a \'content mesh\' for the application: GitHub data for a specific repository, weather data from the National Weather Service for a specific zone, and random photo data from Unsplash', 'examples-for-pop'),
            'userServiceData' => $translationAPI->__('Retrieve data from the mesh services', 'examples-for-pop'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }

    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $fieldQueryInterpreter = FieldQueryInterpreterFacade::getInstance();
        switch ($fieldName) {
            case 'userServiceURLs':
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
            case 'userServiceData':
                $userServiceURLs = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'userServiceURLs',
                        $fieldArgs
                    ), $variables, $expressions, $options
                );
                if (GeneralUtils::isError($userServiceURLs)) {
                    return $userServiceURLs;
                }
                $userServiceURLs = (array)$userServiceURLs;
                return $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'getAsyncJSON',
                        [
                            'urls' => $userServiceURLs,
                        ]
                    ), $variables, $expressions, $options
                );
        }

        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
}
