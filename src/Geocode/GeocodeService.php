<?php
namespace GoogleMapsApi\Geocode;

use GoogleMapsApi\Geocode\Result\GeocodeResult;
use GoogleMapsApi\ServiceApiInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PlacesService
 * @package GoogleMapsApi\Service
 */
class GeocodeService implements ServiceApiInterface
{
    const GEOCODE_BASE_URL  = 'https://maps.googleapis.com/maps/api/geocode';

    /** @var ClientInterface $httpClient */
    private $client;

    /**
     * @param array ServiceApiInterface
     * @param ClientInterface $httpClient
     */
    public function __construct(array $parameters, ClientInterface $httpClient)
    {
        // Required options for this API
        $options = new OptionsResolver();
        $options->setRequired(['key']);
        $this->options = $options->resolve($parameters);

        // Set HTTP Client
        $this->setHttpClient($httpClient);
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $httpClient
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->client = $httpClient;
    }

    /**
     * The Place Autocomplete service is a web service that returns place predictions in response to an HTTP request.
     * The request specifies a textual search string and optional geographic bounds. The service can be used to provide
     * autocomplete functionality for text-based geographic searches, by returning places such as businesses, addresses
     * and points of interest as a user types.
     *
     * @param array $parameters
     * @see https://developers.google.com/places/web-service/autocomplete
     * @return GeocodeResult
     */
    public function getGeocode(array $parameters)
    {
        // Required and optional parameters for this function call
        $options = new OptionsResolver();
        $options->setDefault('output', 'json');
        $options->setDefault('language', 'en');

        $options->setDefined(['latlng', 'place_id']);
        $options = $options->resolve($parameters);

        // Place the call
        $uri = $this->buildUri(
            sprintf('%s/%s', static::GEOCODE_BASE_URL, $options['output']),
            $this->options['key'],
            $parameters
        );

        $request = $this->client->request('GET', $uri);

        return new GeocodeResult($request, $options['language']);
    }

    /**
     * @param $uri
     * @param $key
     * @param $parameters
     * @return string
     */
    private function buildUri($uri, $key, $parameters)
    {
        $query = http_build_query($parameters);

        return sprintf('%s?%s&key=%s', $uri, $query, $key);
    }
}