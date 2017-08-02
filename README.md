Seo title and description
=========================

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
"geniv/nette-translator": ">=1.0.0"
```

Include in application
----------------------

neon configure extension:
```neon
extensions:
    - Seo\Bridges\Nette\Extension
```

usage @layout.latte:
```latte
<title>{ifset title}{include title|seoTitle} | {/ifset}default title</title>
<meta name="description" content="{ifset description}{include description|seoDescription} | {/ifset}default description">
```
