<?php
namespace Leoloso\ExamplesForPoP\TypeResolverDecorators;

use PoP\ComponentModel\TypeResolvers\AbstractTypeResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\Engine\DirectiveResolvers\Cache\SaveCacheDirectiveResolver;
use PoP\AccessControl\TypeResolverDecorators\AbstractPublicSchemaTypeResolverDecorator;

/**
 * Add directive @cache to fields expensive to calculate
 */
class CacheTypeResolverDecorator extends AbstractPublicSchemaTypeResolverDecorator
{
    public static function getClassesToAttachTo(): array
    {
        return [
            AbstractTypeResolver::class,
        ];
    }

    /**
     * Always @cache fields `getJSON` and `getAsyncJSON` for 1 hour
     *
     * @param TypeResolverInterface $typeResolver
     * @return array
     */
    public function getMandatoryDirectivesForFields(TypeResolverInterface $typeResolver): array
    {
        $mandatoryDirectivesForFields = [];
        $fieldQueryInterpreter = FieldQueryInterpreterFacade::getInstance();
        $cacheDirective = $fieldQueryInterpreter->getDirective(
            SaveCacheDirectiveResolver::getDirectiveName(),
            [
                'time' => 3600, // Cache it for 1 hour
            ]
        );
        $mandatoryDirectivesForFields['getJSON'] = [
            $cacheDirective,
        ];
        $mandatoryDirectivesForFields['getAsyncJSON'] = [
            $cacheDirective,
        ];
        return $mandatoryDirectivesForFields;
    }
}
