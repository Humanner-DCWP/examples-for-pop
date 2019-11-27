<?php
namespace Leoloso\ExamplesForPoP\Config;

// use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\Root\Component\PHPServiceConfigurationTrait;
use PoP\API\Container\ContainerBuilderUtils as APIContainerBuilderUtils;

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
        // Fragment resolution
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
        // $translationAPI = TranslationAPIFacade::getInstance();
        APIContainerBuilderUtils::addFragmentToCatalogueService(
            'contentMesh',
            $contentMesh,
            // $translationAPI->__('Fetch \'content mesh\' data (i.e. data from different services required to power the application), including repository data from GitHub, weather data from the National Weather Service, and random photo data from Unsplash', 'examples-for-pop')
            'Fetch \'content mesh\' data (i.e. data from different services required to power the application), including repository data from GitHub, weather data from the National Weather Service, and random photo data from Unsplash'
        );
    }
}
