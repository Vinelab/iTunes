<?php

namespace Vinelab\ITunes;

use Illuminate\Config\Repository;
use Illuminate\Cache\CacheManager;
use Vinelab\Http\Client as HttpClient;

/**
 * An Extension of the Agent class that adds caching capabilities.
 *
 * @author Abed Halawi <abed.halawi@vinelab.com>
 *
 * @since 1.2.0
 */
class LaravelAgent extends Agent
{
    /**
     * What to prefix the cache key with.
     *
     * @var string
     */
    protected $cachePrefix = 'v:itunes';

    /**
     * The duration to cache the received results.
     *
     * @var int
     */
    protected $defaultCacheDuration = 60;

    /**
     * @var Illuminate\Cache\CacheManager
     */
    protected $cache;

    public function __construct(
        Repository $config = null,
        CacheManager $cacheManager = null,
        HttpClient $http = null
    ) {
        parent::__construct($config, $http);

        $this->cache = $cacheManager;
        // Set the default cache duration (overridden by config if found)
        if (!isset($this->iTunesConfig['cache'])) {
            $this->iTunesConfig['cache'] = $this->defaultCacheDuration;
        }
    }

    public function cacheFor($minutes)
    {
        return $this->iTunesConfig['cache'] = $minutes;
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
        $cacheKey = $this->cacheKey('search', $params);
        $cacheDuration = $this->iTunesConfig['cache'];

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        } else {
            return $this->cache->remember($cacheKey, $cacheDuration, function () {
                return json_encode(parent::search($term, $params));
            });
        }
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
        $cacheKey = $this->cacheKey('lookup', $this->getLookupParams($id, $value, $params));
        $cacheDuration = $this->iTunesConfig['cache'];

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        } else {
            return $this->cache->remember($cacheKey, $cacheDuration, function () {
                return json_encode(parent::lookup($id, $value, $params));
            });
        }
    }

    /**
     * get a cache key for the given type and params.
     *
     * @param string $term
     * @param array  $params
     *
     * @return string
     */
    protected function cacheKey($type, $params)
    {
        return sprintf('%s:%s:%s', $this->cachePrefix, $type, md5(http_build_query($params)));
    }

    /**
     * Load the configuration parameters.
     */
    protected function loadConfig()
    {
        parent::loadConfig();

        // merge overriding with user-specified configuration.
        $this->iTunesConfig = array_merge($this->iTunesConfig, $this->config->get('itunes'));
    }
}
