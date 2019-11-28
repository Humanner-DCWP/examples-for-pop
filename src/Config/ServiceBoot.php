<?php
namespace Leoloso\ExamplesForPoP\Config;

use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\API\Facades\PersistedFragmentManagerFacade;
use PoP\API\PersistedFragments\PersistedFragmentUtils;

class ServiceBoot
{
    public static function boot()
    {
        // 'contentMesh' fragment
        // Initialization of parameters
        $githubRepo = $_REQUEST['githubRepo'] ?? 'leoloso/PoP';
        $weatherZone = $_REQUEST['weatherZone'] ?? 'MOZ028';
        $photoPage = $_REQUEST['photoPage'] ?? 1;
        // Fragment resolution
        $meshServices = <<<EOT
        echo([
            github: "https://api.github.com/repos/$githubRepo",
            weather: "https://api.weather.gov/zones/forecast/$weatherZone/forecast",
            photos: "https://picsum.photos/v2/list?page=$photoPage&limit=10"
        ])@meshServices
EOT;
        $meshServiceData = <<<EOT
        --meshServices|
        getAsyncJSON(getSelfProp(%self%, meshServices))@meshServiceData
EOT;
        $contentMesh = <<<EOT
        --meshServiceData|
        echo([
            weatherForecast: extract(
                getSelfProp(%self%, meshServiceData),
                weather.periods
            ),
            photoGalleryURLs: extract(
                getSelfProp(%self%, meshServiceData),
                photos.url
            ),
            githubMeta: echo([
                description: extract(
                    getSelfProp(%self%, meshServiceData),
                    github.description
                ),
                starCount: extract(
                    getSelfProp(%self%, meshServiceData),
                    github.stargazers_count
                )
            ])
        ])@contentMesh
EOT;
        // Inject the values into the service
        $translationAPI = TranslationAPIFacade::getInstance();
        $persistedFragmentManager = PersistedFragmentManagerFacade::getInstance();
        $persistedFragmentManager->add(
            'meshServices',
            PersistedFragmentUtils::removeWhitespaces($meshServices),
            $translationAPI->__('Services required to create a \'content mesh\' for the application: GitHub data for a specific repository, weather data from the National Weather Service for a specific zone, and random photo data from Unsplash', 'examples-for-pop')
        );
        $persistedFragmentManager->add(
            'meshServiceData',
            PersistedFragmentUtils::removeWhitespaces($meshServiceData),
            $translationAPI->__('Retrieve data from the mesh services. This fragment includes calling fragment --meshServices', 'examples-for-pop')
        );
        $persistedFragmentManager->add(
            'contentMesh',
            PersistedFragmentUtils::removeWhitespaces($contentMesh),
            $translationAPI->__('Retrieve data from the mesh services and create a \'content mesh\'. This fragment includes calling fragment --meshServiceData', 'examples-for-pop')
        );
    }
}
