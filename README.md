# nette-seo
======

Seo component




extensions:
	seo: Seo\Bridges\Nette\Extension


<title>{include title|seoTitle} | {control config:text 'web-title'}</title>

<meta name="description" content="{ifset description}{include description|seoDescription}{/ifset} | {control config:text 'web-description'}">
