<?php

declare(strict_types=1);

namespace Leoloso\ExamplesForPoP\FieldResolvers\Legacy\Version_0_1_0;

use PoP\ComponentModel\Misc\GeneralUtils;
use PoP\Engine\TypeResolvers\RootTypeResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;

class RootFieldResolver extends AbstractDBDataFieldResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(RootTypeResolver::class);
    }

    public static function getPriorityToAttachClasses(): ?int
    {
        // Higher priority => Process before the latest version fieldResolver
        return 20;
    }

    public function getSchemaFieldVersion(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        return '0.1.0';
    }

    public function decideCanProcessBasedOnVersionConstraint(TypeResolverInterface $typeResolver): bool
    {
        return true;
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
                    ]
                );
        }

        return $schemaFieldArgs;
    }

    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
            'userServiceURLs' => $translationAPI->__('Services used in the application: GitHub data for a specific repository', 'examples-for-pop'),
            'userServiceData' => $translationAPI->__('Retrieve data from the services', 'examples-for-pop'),
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
                ];
            case 'userServiceData':
                $userServiceURLs = $typeResolver->resolveValue(
                    $resultItem,
                    $fieldQueryInterpreter->getField(
                        'userServiceURLs',
                        $fieldArgs
                    ),
                    $variables,
                    $expressions,
                    $options
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
                    ),
                    $variables,
                    $expressions,
                    $options
                );
        }

        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
}
