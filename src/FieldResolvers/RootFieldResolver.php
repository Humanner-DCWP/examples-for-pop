<?php
namespace Leoloso\ExamplesForPoP\FieldResolvers;

use PoP\ComponentModel\Environment;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;

class RootFieldResolver extends Root_Version_0_1_0_FieldResolver
{
    public static function getPriorityToAttachClasses(): ?int
    {
        return null;
    }

    public function decideCanProcessBasedOnVersionConstraint(TypeResolverInterface $typeResolver): bool
    {
        return false;
    }

    public function resolveSchemaValidationWarningDescription(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []): ?string
    {
        if (Environment::enableSemanticVersionConstraints()) {
            // If the query doesn't specify what version of the field to use, add a deprecation message
            if (!$fieldArgs[SchemaDefinition::ARGNAME_VERSION_CONSTRAINT]) {
                $translationAPI = TranslationAPIFacade::getInstance();
                $descriptions = [
                    'userServiceURLs' => sprintf(
                        $translationAPI->__('Field \'%1$s\' has a new version: \'%2$s\'. This version will become the default one on January 1st. We advise you to use this new version already and test that it works fine; if you find any problem, please report the issue in %3$s. To do the switch, please add the \'versionConstraint\' field argument to your query, using Composer\'s semver constraint rules (%4$s): %1$s(versionConstraint:"%5$s"). If you are unable to switch to the new version, please make sure to explicitly point to the current version \'%6$s\' before January 1st: %1$s(versionConstraint:"%6$s"). In case of doubt, please contact us at name@company.com.', 'examples-for-pop'),
                        $fieldName,
                        '0.2.0',
                        'https://github.com/mycompany/myproject/issues',
                        'https://getcomposer.org/doc/articles/versions.md',
                        '^0.2',
                        '0.1.0'
                    ),
                    'userServiceData' => sprintf(
                        $translationAPI->__('Field \'%1$s\' has more than 1 version. Please add the \'versionConstraint\' field argument to your query to indicate which version to use (using Composer\'s semver constraint rules: %2$s). To use the latest version, use: %1$s(versionConstraint:"%3$s"). Available versions: \'%4$s\'.', 'examples-for-pop'),
                        $fieldName,
                        'https://getcomposer.org/doc/articles/versions.md',
                        '^0.2',
                        implode(
                            $translationAPI->__('\', \'', 'examples-for-pop'),
                            ['0.2.0', '0.1.0']
                        )
                    ),
                ];
                return $descriptions[$fieldName] ?? parent::resolveSchemaValidationWarningDescription($typeResolver, $fieldName, $fieldArgs);
            }
        }
        return parent::resolveSchemaValidationWarningDescription($typeResolver, $fieldName, $fieldArgs);
    }
}
