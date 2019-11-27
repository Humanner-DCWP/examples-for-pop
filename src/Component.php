<?php
namespace Leoloso\ExamplesForPoP;

use PoP\Root\Component\AbstractComponent;
use PoP\Root\Component\YAMLServicesTrait;
use Leoloso\ExamplesForPoP\Config\ServiceBoot;
use PoP\ComponentModel\Container\ContainerBuilderUtils;

/**
 * Initialize component
 */
class Component extends AbstractComponent
{
    use YAMLServicesTrait;
    // const VERSION = '0.1.0';

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
    public static function boot()
    {
        parent::boot();

        // Initialize services
        ServiceBoot::boot();

        // Initialize classes
        ContainerBuilderUtils::attachFieldValueResolversFromNamespace(__NAMESPACE__.'\\FieldValueResolvers');
    }
}
