Seo title and description
=========================

description: block title and description are internal save to database

Installation
------------

```sh
$ composer require geniv/nette-seo
```
or
```json
"geniv/nette-seo": ">=1.0.0"
```

require:
```json
"php": ">=5.6.0",
"nette/nette": ">=2.4.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0.0"
```

Include in application
----------------------

neon configure:
```neon
# seo
seo:
    tablePrefix: %tablePrefix%
#   autowired: false    # default null, false => disable autowiring (in case multiple linked extension) | self    
    prefixSeparator:
        default-latte: ' - '
#    suffixSeparator:
#        default-latte: ' - '
```

neon configure extension:
```neon
extensions:
    seo: Seo\Bridges\Nette\Extension
```

usage:
```php
use Seo\Seo;

protected function createComponentSeo(Seo $seo)
{
    return $seo;
}
```

usage @layout.latte:
```latte
<title>{ifset title}{include title} - {else}{control seo:title}{/ifset}{if $presenter['seo']->isTitle()} - {/if}{control seo:title 'default-latte'}</title>
<meta name="description" content="{ifset description}{include description}{else}{control seo:description}{/ifset}{if $presenter['seo']->isDescription()} - {/if}{control seo:description 'default-latte'}">
```
or without separator (separator configure in neon, via: `prefixSeparator`, `suffixSeparator`):
```latte
<title>{ifset title}{include title}{else}{control seo:title}{/ifset}{control seo:title 'default-latte'}</title>
<meta name="description" content="{ifset description}{include description}{else}{control seo:description}{/ifset}{control seo:description 'default-latte'}">
```

### Warning:
text ident in title and description is automatic translate!!!
```latte
{block title}homepage-title{/block}
{block description}homepage-description{/block}
```
in case usage block: `{block title}` or `{block description}` content this block does not save to database!!!
