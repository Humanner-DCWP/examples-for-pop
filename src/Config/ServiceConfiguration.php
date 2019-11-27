<?php
namespace Leoloso\ExamplesForPoP\Config;

use PoP\API\FragmentCatalogue\FragmentUtils;
use PoP\ComponentModel\Container\ContainerBuilderUtils;
use PoP\Root\Component\PHPServiceConfigurationTrait;

class ServiceConfiguration
{
    use PHPServiceConfigurationTrait;

    protected static function configure()
    {
        // 'contentMesh' fragment
        // Initialization of parameters
        $githubRepo = $_REQUEST['githubRepo'] ?? 'leoloso/PoP';
        $zone = $_REQUEST['zone'] ?? 'MOZ028';
        $page = $_REQUEST['page'] ?? 1;
        $contentMesh = <<<EOT
            getAsyncJSON([
                github: "https://api.github.com/repos/$githubRepo",
                weather: "https://api.weather.gov/zones/forecast/$zone/forecast",
                photos: "https://picsum.photos/v2/list?page=$page&limit=10"
            ])@contentMesh|
            extract(
                getSelfProp(%self%, contentMesh),
                weather.periods
            )@weather|
            extract(
                getSelfProp(%self%, contentMesh),
                photos.url
            )@photos|
            echo([
                name: extract(
                    getSelfProp(%self%, contentMesh),
                    github.full_name
                ),
                description: extract(
                    getSelfProp(%self%, contentMesh),
                    github.description
                ),
                starCount: extract(
                    getSelfProp(%self%, contentMesh),
                    github.stargazers_count
                ),
                forkCount: extract(
                    getSelfProp(%self%, contentMesh),
                    github.forks_count
                )
            ])@github
        EOT;
        // Format the fragment
        $contentMesh = FragmentUtils::removeWhitespaces($contentMesh);
        $contentMesh = FragmentUtils::addSpacingToExpressions($contentMesh);
        // Add RouteModuleProcessors to the Manager
        ContainerBuilderUtils::injectValuesIntoService(
            'fragment_catalogue_manager',
            'add',
            'contentMesh',
            $contentMesh
        );
    }
}
