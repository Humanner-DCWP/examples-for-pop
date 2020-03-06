<?php
namespace Leoloso\ExamplesForPoP\Config;

use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\API\Facades\PersistedFragmentManagerFacade;
use PoP\API\Facades\PersistedQueryManagerFacade;
use PoP\API\PersistedFragments\PersistedFragmentUtils;

class ServiceBoot
{
    public static function boot()
    {
        // 'contentMesh' persisted fragments
        // Initialization of parameters
        $githubRepo = $_REQUEST['githubRepo'] ?? 'leoloso/PoP';
        $weatherZone = $_REQUEST['weatherZone'] ?? 'MOZ028';
        $photoPage = $_REQUEST['photoPage'] ?? 1;
        // Fragment resolution
        $meshServicesPersistedFragment = <<<EOT
        echo([
            github: "https://api.github.com/repos/$githubRepo",
            weather: "https://api.weather.gov/zones/forecast/$weatherZone/forecast",
            photos: "https://picsum.photos/v2/list?page=$photoPage&limit=10"
        ])@meshServices
EOT;
        $meshServiceDataPersistedFragment = <<<EOT
        --meshServices|
        getAsyncJSON(getSelfProp(%self%, meshServices))@meshServiceData
EOT;
        $contentMeshPersistedFragment = <<<EOT
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
            PersistedFragmentUtils::removeWhitespaces($meshServicesPersistedFragment),
            $translationAPI->__('Services required to create a \'content mesh\' for the application: GitHub data for a specific repository, weather data from the National Weather Service for a specific zone, and random photo data from Unsplash', 'examples-for-pop')
        );
        $persistedFragmentManager->add(
            'meshServiceData',
            PersistedFragmentUtils::removeWhitespaces($meshServiceDataPersistedFragment),
            $translationAPI->__('Retrieve data from the mesh services. This fragment includes calling fragment --meshServices', 'examples-for-pop')
        );
        $persistedFragmentManager->add(
            'contentMesh',
            PersistedFragmentUtils::removeWhitespaces($contentMeshPersistedFragment),
            $translationAPI->__('Retrieve data from the mesh services and create a \'content mesh\'. This fragment includes calling fragment --meshServiceData', 'examples-for-pop')
        );

        // Persisted queries
        $contentMeshPersistedQuery = <<<EOT
        --contentMesh
EOT;
        // Inject the values into the service
        $persistedQueryManager = PersistedQueryManagerFacade::getInstance();
        $persistedQueryManager->add(
            'contentMesh',
            PersistedFragmentUtils::removeWhitespaces($contentMeshPersistedQuery),
            $translationAPI->__('Retrieve data from the mesh services and create a \'content mesh\'', 'examples-for-pop')
        );
    }
}
