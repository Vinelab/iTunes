<?php namespace Vinelab\ITunes;

use Illuminate\Config\Repository;
use Illuminate\Cache\CacheManager;
use Vinelab\Http\Client as HttpClient;

use Vinelab\ITunes\Exceptions\ConfigurationException;
use Vinelab\ITunes\Exceptions\InvalidSearchException;

class Agent {

    /**
     * What to prefix the cache key with
     * @var string
     */
    protected $cachePrefix = 'v:itunes';

    /**
     * @var Illuminate\Config\Repository
     */
    protected $_Config;

    /**
     * @var Illuminate\Cache\CacheManager
     */
    protected $_Cache;

    /**
     * @var Vinelab\HttpClient
     */
    protected $_HttpClient;

    /**
     *
     * The duration to cache the received results
     * @var float
     */
    protected $defaultCacheDuration = 60;

    /**
     * The API Request Configuration
     * @var array
     */
    protected $iTunesConfig;

    public function __construct(
        Repository $config = null,
        CacheManager $cacheManager = null,
        HttpClient $http = null )
    {

        $this->_Config = $config ?: new Repository;
        $this->_Cache = $cacheManager ?: new Cache;
        $this->_Http = $http;

        // load library configuration
        $this->iTunesConfig = $this->_Config->get('itunes::itunes');

        // Set the default cache duration (overridden by config if found)
        if (!isset($this->iTunesConfig['cache'])) $this->iTunesConfig['cache'] = $this->defaultCacheDuration;
    }

    /**
     * Search the API for a term
     * @param  string $term
     * @param  array  $params
     * @return object
     */
    public function search($term, $params = array())
    {
        $params = array_merge(compact('term'), $params);

        $cacheKey = $this->cacheKey('search', $params);
        $cacheDuration = $this->iTunesConfig['cache'];

        if ($this->_Cache->has($cacheKey))
        {
            return $this->_Cache->get($cacheKey);
        } else {

            $results = $this->_Http->get($this->request('search', $params))->json();

            if ($results)
            {
                return $this->_Cache->remember($cacheKey, $cacheDuration, function() use($results) {
                    return json_encode($results);
                });

            } else {
                return json_encode($this->emptyResults());
            }
        }
    }

    /**
     * Search withing a defined region
     * @param  string $region
     * @param  string $term
     * @param  array  $params
     * @return obejct
     */
    public function searchRegion($region, $term, $params = array())
    {
        return $this->search($term, array_merge(array('country'=>strtoupper($region)), $params));
    }

    /**
     * Lookup an item in the API
     * @param  string $item
     * @param  array  $params
     * @return object
     */
    public function lookup($id, $value = null, $params = array())
    {
        // Make the id default
        if (!$value)
        {
            $value = $id;
            $id = 'id';
        }

        $params = array_merge(array($id=>$value), $params);

        $cacheKey = $this->cacheKey('lookup', $params);
        $cacheDuration = $this->iTunesConfig['cache'];

        if ($this->_Cache->has($cacheKey))
        {
            return $this->_Cache->get($cacheKey);
        } else {

            $results = $this->_Http->get($this->request('lookup', $params))->json();

            if ($results)
            {
                return $this->_Cache->remember($cacheKey, $cacheDuration, function() use($results) {
                    return json_encode($results);
                });

            } else {
                return json_encode($this->emptyResults());
            }
        }
    }

    /**
     * This method is automatically called through
     * the __call method. A convenience in order to be able
     * to perform searches like:
     *
     * music($term); musicInRegion($region, $term);
     * tvShow($show); tvShowInregion($region, $show);
     *
     * @param  string $method
     * @param  array $arguments
     * @return object
     */
    public function specificMediaSearch($method, $arguments)
    {
        if (isset($arguments[0]))
        {
            $params = array();

            if (strpos($method, 'InRegion') !== false and isset($arguments[1]))
            {
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

            if (count($arguments) > 1 and is_array($arguments[count($arguments)-1]))
            {
                $params = array_merge($arguments[count($arguments)-1], $params);
            }

            return $this->search($term, $params);
        }

        throw new InvalidSearchException("Missing arguments for search");
    }

    /**
     * Builds up the request array
     *
     * @param  string $type supported: search | lookup
     * @param  string $term
     * @param  array  $params
     * @return array
     */
    public function request($type, $params = array())
    {
        if (isset($this->iTunesConfig['api']))
        {
            if (
                isset($this->iTunesConfig['api']['url']) and
                isset($this->iTunesConfig['api']['search_uri']) and
                isset($this->iTunesConfig['api']['lookup_uri'])
            ) {

                $host = $this->iTunesConfig['api']['url'];

                switch($type)
                {
                    case 'search':
                    default:
                        $uri = $this->iTunesConfig['api']['search_uri'];
                        $params = array_merge(
                            array(
                                'limit' => isset($this->iTunesConfig['limit']) ? $this->iTunesConfig['limit'] : 50
                            ),
                            $params
                        );
                    break;

                    case 'lookup':
                        $uri = $this->iTunesConfig['api']['lookup_uri'];
                    break;
                }

                return array(
                    'url'    => $host.$uri,
                    'params' => $params
                );
            }
        }

        throw new ConfigurationException('Incomplete Configuration');
    }

    public function cacheFor($minutes)
    {
        return $this->iTunesConfig['cache'] = $minutes;
    }

    protected function emptyResults()
    {
        return array('resultsCount'=>0, 'results'=>array());
    }

    /**
     * [cacheKey description]
     * @param  string $term
     * @param  array $params
     * @return string
     */
    protected function cacheKey($type, $params)
    {
        return sprintf('%s:%s:%s', $this->cachePrefix, $type, md5(http_build_query($params)));
    }

    public function __call($method, $arguments)
    {
        // Any unmatched method is turned into a media search
        // i.e. $agent->music(...)
        // i.e. $agent->tvShow(...)
        // i.e. $agent->musicInRegion(...)
        return $this->specificMediaSearch($method, $arguments);
    }
}