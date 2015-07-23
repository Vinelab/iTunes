<?php

namespace Vinelab\ITunes;

use Illuminate\Config\Repository;
use Vinelab\Http\Client as HttpClient;
use Vinelab\ITunes\Exceptions\ConfigurationException;
use Vinelab\ITunes\Exceptions\InvalidSearchException;

/**
 * The iTunes agent.
 *
 * Responsible for performing the request to the iTunes API.
 *
 * @author Abed Halawi <abed.halawi@vinelab.com>
 *
 * @since 1.0.0
 */
class Agent
{
    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Vinelab\HttpClient
     */
    protected $http;

    /**
     * The API Request Configuration.
     *
     * @var array
     */
    protected $iTunesConfig = [];

    public function __construct(
        Repository $config = null,
        HttpClient $http = null
    ) {
        $this->config = $config ?: new Repository();
        $this->http = $http ?: new HttpClient();
        // load library configuration
        $this->loadConfig();
    }

    /**
     * Search the API for a term.
     *
     * @param string $term
     * @param array  $params
     *
     * @return object
     */
    public function search($term, $params = array())
    {
        $params = $this->getSearchParams($term, $params);
        $results = $this->http->get($this->request('search', $params))->json();

        return $results ?: $this->emptyResults();
    }

    /**
     * Search withing a defined region.
     *
     * @param string $region
     * @param string $term
     * @param array  $params
     *
     * @return obejct
     */
    public function searchRegion($region, $term, $params = array())
    {
        return $this->search($term, array_merge(array('country' => strtoupper($region)), $params));
    }

    /**
     * Lookup an item in the API.
     *
     * @param string $item
     * @param array  $params
     *
     * @return object
     */
    public function lookup($id, $value = null, $params = array())
    {
        $results = $this->http->get($this->request('lookup', $this->getLookupParams($id, $value, $params)))->json();

        return $results ?: $this->emptyResults();
    }

    /**
     * This method is automatically called through
     * the __call method. A convenience in order to be able
     * to perform searches like:.
     *
     * music($term); musicInRegion($region, $term);
     * tvShow($show); tvShowInregion($region, $show);
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return object
     */
    public function specificMediaSearch($method, $arguments)
    {
        if (isset($arguments[0])) {
            $params = array();

            if (strpos($method, 'InRegion') !== false && isset($arguments[1])) {
                // performing regional search
                list($region, $term) = $arguments;
                $params['country'] = strtoupper($region);

                // remove InRegion to get the media type out of the method name
                $media = str_replace('InRegion', '', $method);
                $term = $arguments[1];
            } else {
                $media = $method;
                $term = $arguments[0];
            }

            $params['media'] = $media;

            if (count($arguments) > 1 && is_array($arguments[count($arguments) - 1])) {
                $params = array_merge($arguments[count($arguments) - 1], $params);
            }

            return $this->search($term, $params);
        }

        throw new InvalidSearchException('Missing arguments for search');
    }

    /**
     * Builds up the request array.
     *
     * @param string $type   supported: search | lookup
     * @param string $term
     * @param array  $params
     *
     * @return array
     */
    public function request($type, $params = array())
    {
        if (isset($this->iTunesConfig['api'])) {
            if (
                isset($this->iTunesConfig['api']['url']) &&
                isset($this->iTunesConfig['api']['search_uri']) &&
                isset($this->iTunesConfig['api']['lookup_uri'])
            ) {
                $host = $this->iTunesConfig['api']['url'];

                switch ($type) {
                    case 'search':
                    default:
                        $uri = $this->iTunesConfig['api']['search_uri'];
                        $params = array_merge(
                            array(
                                'limit' => isset($this->iTunesConfig['limit']) ? $this->iTunesConfig['limit'] : 50,
                            ),
                            $params
                        );
                    break;

                    case 'lookup':
                        $uri = $this->iTunesConfig['api']['lookup_uri'];
                    break;
                }

                return array(
                    'url' => $host.$uri,
                    'params' => $params,
                );
            }
        }

        throw new ConfigurationException('Incomplete Configuration');
    }

    /**
     * Get the query parameters for a search request.
     *
     * @param string $term
     * @param array  $params
     *
     * @return array
     */
    protected function getSearchParams($term, array $params)
    {
        return array_merge(compact('term'), $params);
    }

    /**
     * Get the query params for a lookup request.
     *
     * @param string $id
     * @param string $value
     * @param array  $params
     *
     * @return array
     */
    protected function getLookupParams($id, $value, array $params)
    {
        // Make the id default
        if (!$value) {
            $value = $id;
            $id = 'id';
        }

        return array_merge(array($id => $value), $params);
    }

    protected function emptyResults()
    {
        return array('resultsCount' => 0, 'results' => array());
    }

    public function __call($method, $arguments)
    {
        // Any unmatched method is turned into a media search
        // i.e. $agent->music(...)
        // i.e. $agent->tvShow(...)
        // i.e. $agent->musicInRegion(...)
        return $this->specificMediaSearch($method, $arguments);
    }

    /**
     * Load the configuration parameters.
     *
     * @return
     */
    protected function loadConfig()
    {
        // load the detaulf configuration.
        $this->iTunesConfig = (array) require_once __DIR__.'/../../config/itunes.php';
    }
}
