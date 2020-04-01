<?php
namespace Leoloso\ExamplesForPoP\TypeResolverDecorators\Cache;

use PoP\ComponentModel\TypeResolvers\AbstractTypeResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\Engine\DirectiveResolvers\Cache\SaveCacheDirectiveResolver;
use PoP\TranslateDirective\DirectiveResolvers\AbstractTranslateDirectiveResolver;
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
     * Get the cache directive
     *
     * @return array
     */
    protected function getCacheDirective(): array
    {
        $fieldQueryInterpreter = FieldQueryInterpreterFacade::getInstance();
        return $fieldQueryInterpreter->getDirective(
            SaveCacheDirectiveResolver::getDirectiveName(),
            [
                'time' => 3600, // Cache it for 1 hour
            ]
        );
    }

    /**
     * Cache fields `getJSON` and `getAsyncJSON` for 1 hour by adding directive @cache
     *
     * @param TypeResolverInterface $typeResolver
     * @return array
     */
    public function getMandatoryDirectivesForFields(TypeResolverInterface $typeResolver): array
    {
        $cacheDirective = $this->getCacheDirective();
        return [
            'getJSON' => [
                $cacheDirective,
            ],
            'getAsyncJSON' => [
                $cacheDirective,
            ],
        ];
    }

    /**
     * Cache directive `@translate` for 1 hour by adding directive @cache
     *
     * @param TypeResolverInterface $typeResolver
     * @return array
     */
    public function getSucceedingMandatoryDirectivesForDirectives(TypeResolverInterface $typeResolver): array
    {
        $cacheDirective = $this->getCacheDirective();
        return [
            AbstractTranslateDirectiveResolver::getDirectiveName() => [
                $cacheDirective,
            ],
        ];
    }
}
