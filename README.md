[![Build Status](https://travis-ci.org/Vinelab/iTunes.svg?branch=0.1.2)](https://travis-ci.org/Vinelab/iTunes)
# Vinelab/iTunes

> A helper class that communicates with the iTunes search+lookup API. Supports caching results, default caching duration is 60 min.

Installation
------------
Using [composer](http://getcomposer.org) require the package [vinelab/itunes](https://packagist.org/packages/vinelab/itunes).

Edit **app.php** and add ```'Vinelab\ITunes\ITunesServiceProvider'``` to the ```'providers'``` array.

It will automatically alias itself as ITunes which can be used as a Facade class.

Usage
-----

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

## MIT License
Copyright (c) 2013 Vinelab FZ LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
