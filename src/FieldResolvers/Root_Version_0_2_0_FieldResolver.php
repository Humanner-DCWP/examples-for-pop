<?php
namespace Leoloso\ExamplesForPoP\FieldResolvers;

use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;

class Root_Version_0_2_0_FieldResolver extends Root_Version_0_1_0_FieldResolver
{
    public function getSchemaFieldVersion(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        return '0.2.0';;
    }
    public static function getPriorityToAttachClasses(): ?int
    {
        return 30;
    }

    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        switch ($fieldName) {
            case 'meshServices':
            case 'meshServiceData':
            case 'contentMesh':
                // Find the "githubRepo" parameter, and change its default value
                return array_map(
                    function($arg) {
                        if ($arg[SchemaDefinition::ARGNAME_NAME] == 'githubRepo') {
                            $arg[SchemaDefinition::ARGNAME_DEFAULT_VALUE] = 'getpop/component-model';
                        }
                        return $arg;
                    },
                    $schemaFieldArgs
                );
        }

        return $schemaFieldArgs;
    }

    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $value = parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
        switch ($fieldName) {
            case 'meshServices':
                // Override the default value
                return array_merge(
                    $value,
                    [
                        'github' => sprintf(
                            'https://api.github.com/repos/%s',
                            $fieldArgs['githubRepo'] ?? 'getpop/component-model'
                        ),
                    ]
                );
        }

        return $value;
    }
}
