\<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as M;
use Vinelab\ITunes\Agent;

class AgentTest extends TestCase {

    public function setUp()
    {
        $this->mConfig = M::mock('Illuminate\Config\Repository');

        $this->defaultConfig = array(
            'api' => array(

                'version' => 2,

                'url' => 'https://itunes.apple.com',

                'search_uri' => '/search',

                'lookup_uri' => '/lookup'
            ),

            'limit' => '50',

            'language' => 'en_us', // or ja_jp

            'explicit' => 'Yes', // or No
        );

        $this->mCache = M::mock('Illuminate\Cache\CacheManager');
        $this->mCache->shouldReceive('has')->andReturn(false);
        $this->mCache->shouldReceive('remember')->andReturn(true);
        $this->mHttpClient = M::mock('Vinelab\Http\Client');
        $this->mHttpClient->shouldReceive('json')->andReturn(json_encode(array('results'=>array())));
        $this->mHttpClient->shouldReceive('get')->andReturn($this->mHttpClient);
    }

    public function test_request_generator()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $agent = new Agent($this->mConfig, $this->mCache);

        $searchRequest = $agent->request('search', array('term'=>'abou ali'));

        $this->assertEquals($searchRequest['url'], $this->defaultConfig['api']['url'].$this->defaultConfig['api']['search_uri']);
        $this->assertEquals($searchRequest['params'], array('term'=>'abou ali', 'limit'=>50));

        $params = array('country'=>'Lebanon', 'term'=>'colorado');
        $searchRequestParams = $agent->request('search', $params);
        $this->assertEquals(array_merge(array('term'=>'colorado', 'limit'=>50), $params), $searchRequestParams['params']);

        $lookupRequest = $agent->request('lookup', 'artistId');
        $this->assertEquals($lookupRequest['url'], $this->defaultConfig['api']['url'].$this->defaultConfig['api']['lookup_uri']);
    }

    public function test_request_custom_limit_override_default()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $agent = new Agent($this->mConfig, $this->mCache);

        $req = $agent->request('search', array('limit'=>10));
        $this->assertEquals($req['params']['limit'], 10);
    }

    /**
     * @expectedException Vinelab\ITunes\Exceptions\ConfigurationException
     */
    public function test_request_fails_with_no_search_uri()
    {
        unset($this->defaultConfig['api']['search_uri']);

        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $agent = new Agent($this->mConfig, $this->mCache);
        $agent->request('search', array('term'=>'whatever'));
    }

    /**
     * @expectedException Vinelab\ITunes\Exceptions\ConfigurationException
     */
    public function test_request_fails_with_no_lookup_uri()
    {
        unset($this->defaultConfig['api']['lookup_uri']);

        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $agent = new Agent($this->mConfig, $this->mCache);
        $agent->request('search', array('term'=>'whatever'));
    }

    /**
     * @expectedException Vinelab\ITunes\Exceptions\ConfigurationException
     */
    public function test_request_fails_with_no_api_url()
    {
        unset($this->defaultConfig['api']['url']);

        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $agent = new Agent($this->mConfig, $this->mCache);
        $agent->request('search', array('ter'=>'whatever'));
    }

    public function test_search()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);

        $fakeAgent = new Agent($this->mConfig, $this->mCache);
        $request = $fakeAgent->request('search', array('term'=>'bananas'));

        $this->mHttpClient->shouldReceive('get')->once()
            ->with($request);

        $agent = new Agent($this->mConfig, $this->mCache, $this->mHttpClient);
        $s = $agent->search('bananas');
        $this->assertNotNull($s);
    }

    public function test_lookup()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);

        $fakeAgent = new Agent($this->mConfig, $this->mCache);
        $request = $fakeAgent->request('lookup', array('term'=>'fastouke'));

        $this->mHttpClient->shouldReceive('get')->once()
            ->with($request);

        $agent = new Agent($this->mConfig, $this->mCache, $this->mHttpClient);
        $l = $agent->lookup('fastouke');
        $this->assertNotNull($l);
    }

    public function test_search_region()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $this->mHttpClient->shouldReceive('get')->once();
        $agent = new Agent($this->mConfig, $this->mCache, $this->mHttpClient);

        $s = $agent->searchRegion('LB', 'hanna montana');
        $this->assertNotNull($s);
    }

    public function test_search_media()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $fakeAgent = new Agent($this->mConfig, $this->mCache);
        $request = $fakeAgent->request('search', array('term'=>'my itsy bitsy song', 'media'=>'music'));

        $this->mHttpClient->shouldReceive('get')->once()->with($request);

        $agent = new Agent($this->mConfig, $this->mCache, $this->mHttpClient);
        $this->assertNotNull($agent->music('my itsy bitsy song'));
    }

    public function test_search_media_in_region()
    {
        $this->mConfig->shouldReceive('get')->andReturn($this->defaultConfig);
        $fakeAgent = new Agent($this->mConfig, $this->mCache);
        $request = $fakeAgent->request('search', array('term'=>'my itsy bitsy song', 'media'=>'music', 'country'=>'AE'));

        $this->mHttpClient->shouldReceive('get')->once()->with($request);

        $agent = new Agent($this->mConfig, $this->mCache, $this->mHttpClient);
        $s = $agent->musicInRegion('ae', 'my itsy bitsy song');
        $this->assertNotNull($s);
    }
}