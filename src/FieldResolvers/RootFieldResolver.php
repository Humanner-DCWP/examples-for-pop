<?php
namespace Leoloso\ExamplesForPoP\FieldResolvers;

use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;

class RootFieldResolver extends Root_Version_0_1_0_FieldResolver
{
    public function getSchemaFieldVersion(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        return null;
    }
    public static function getPriorityToAttachClasses(): ?int
    {
        return null;
    }

    public function getSchemaFieldDeprecationDescription(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []): ?string
    {
        // If the query doesn't specify what version of the field to use, add a deprecation message
        if (!$fieldArgs['versionConstraint']) {
            $translationAPI = TranslationAPIFacade::getInstance();
            $deprecationMessage = sprintf(
                $translationAPI->__('Field \'%1$s\' has a new version: \'%2$s\'. This version will become the default one on January 1st. We advise you to use this new version already, test that it works fine, and report to us if you find any problem. To do the switch, please add the \'versionConstraint\' field argument to your query: %1$s(versionConstraint:\'%2$s\'). If you are unable to switch to the new version, please make sure to explicitly point to the current version \'%3$s\' before January 1st: %1$s(versionConstraint:\'%3$s\'). In case of doubt, please contact us at name@company.com.', 'examples-for-pop'),
                $fieldName,
                '0.2.0',
                '0.1.0'
            );
            $descriptions = [
                'meshServices' => $deprecationMessage,
                'meshServiceData' => $deprecationMessage,
                'contentMesh' => $deprecationMessage,
            ];
            return $descriptions[$fieldName] ?? parent::getSchemaFieldDeprecationDescription($typeResolver, $fieldName, $fieldArgs);
        }
        return parent::getSchemaFieldDeprecationDescription($typeResolver, $fieldName, $fieldArgs);
    }
}
