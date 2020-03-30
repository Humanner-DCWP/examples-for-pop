<?php
namespace Leoloso\ExamplesForPoP;

use Leoloso\ExamplesForPoP\Config\ServiceBoot;
use PoP\Root\Component\AbstractComponent;
use PoP\Root\Component\YAMLServicesTrait;
use PoP\ComponentModel\Container\ContainerBuilderUtils;

/**
 * Initialize component
 */
class Component extends AbstractComponent
{
    use YAMLServicesTrait;
    const VERSION = '0.2.0';

    /**
     * Initialize services
     */
    public static function init()
    {
        parent::init();
        self::initYAMLServices(dirname(__DIR__));
    }

    /**
     * Boot component
     *
     * @return void
     */
    public static function beforeBoot()
    {
        parent::beforeBoot();

        // Initialize services
        ServiceBoot::beforeBoot();

        // Initialize classes
        ContainerBuilderUtils::attachFieldResolversFromNamespace(__NAMESPACE__.'\\FieldResolvers');
        ContainerBuilderUtils::attachAndRegisterDirectiveResolversFromNamespace(__NAMESPACE__.'\\DirectiveResolvers');
    }

    /**
     * Boot component
     *
     * @return void
     */
    public static function afterBoot()
    {
        parent::afterBoot();

        // Initialize classes
        ContainerBuilderUtils::attachTypeResolverDecoratorsFromNamespace(__NAMESPACE__.'\\TypeResolverDecorators');
    }
}
