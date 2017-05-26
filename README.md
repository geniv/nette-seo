# nette-seo
======

Seo component




extensions:
	seo: Seo\Bridges\Nette\Extension


<title>{ifset title}{include title|seoTitle} | {/ifset}default title</title>
<meta name="description" content="{ifset description}{include description|seoDescription} | {/ifset}default description">
