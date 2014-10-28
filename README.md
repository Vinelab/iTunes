[![Build Status](https://travis-ci.org/Vinelab/iTunes.svg?branch=0.1.2)](https://travis-ci.org/Vinelab/iTunes)

[![Dependency Status](https://www.versioneye.com/user/projects/53efc98013bb065c3300049a/badge.svg?style=flat)](https://www.versioneye.com/user/projects/53efc98013bb065c3300049a)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/336d059a-e325-4d38-bf41-2dfe7be45fed/big.png)](https://insight.sensiolabs.com/projects/336d059a-e325-4d38-bf41-2dfe7be45fed)

# Vinelab/iTunes
A simple yet full-fledged iTunes API client with caching support.

## Installation

### Composer

- `"vinelab/itunes": "dev-master"` or refer to [vinelab/itunes on packagist.org](https://packagist.org/packages/vinelab/itunes)

```php
// change this to point correctly according
// to your folder structure.
require './vendor/autoload.php';

use Vinelab\ITunes\Agent as iTunes;

$iTunes = new iTunes;

$response = $iTunes->search('Porcupine Tree')); // The original iTunes response
```

### Laravel

Edit **app.php** and add `'Vinelab\ITunes\ITunesServiceProvider'` to the `'providers'` array.

It will automatically alias itself as `ITunes` which can be used as a Facade class.

## Usage

### Search

```php
<?php

    // Search the iTunes Store
    ITunes::search('Metallica');

    // Search the iTunes Store with custom parameters
    ITunes::search('Cannibal Corpse', array('limit'=>25, 'entity'=>'...'));

    /**
     * Search for media
     *
     * check (http://www.apple.com/itunes/affiliates/resources/documentation/itunes-store-web-service-search-api.html#searching)
     * for all supported media types (the media parameter)
     */
    ITunes::music('Rolling Stones');

    ITunes::musicVideo('Immolation');

    ITunes::tvShow('Sex and The City');

    /**
     * Search a specific region
     *
     * Add InRegion for any kind of media search
     */
    ITunes::musicInRegion('LB', 'Myriam Fares');


```

### Lookup

```php
<?php

    // Lookup defaults to id=...
    ITunes::lookup(490326927);

    // You can also specify the lookup request
    ITunes::lookup('amgVideoId', 17120);

    // Multiple items lookup
    ITunes::lookup('amgAlbumId', '15175,15176,15177,15178,15183,15184,15187,1519,15191,15195,15197,15198');

```

### Caching
> you can specify the duration (in minutes) of caching per request as follows

> NOTE: The last cache duration value set will remain for the rest of the requests so make sure you reset afterwards.

```php
<?php

    ITunes::cacheFor(10);
    ITunes::search('Gangnam Style'); // will be cached for 10 min.

    ITunes::cacheFor(1);
    ITunes::search('Yesterday'); // will be cached for 1 min.

    // To bypass caching pass 0
    ITunes::cacheFor(0);
    ITunes::search('Hallelujah'); // won't be cached
```
