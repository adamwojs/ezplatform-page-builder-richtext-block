# ezplatform-page-builder-richtext-block

POC of RichText Block for Page Builder 

## Instalation 

1. Add the following line to the `repositories` section in the `composer.json`

```json
{ "type": "vcs", "url": "https://github.com/adamwojs/ezplatform-page-builder-richtext-block.git"}
```

2. Enable the bundle in `AppKernel`: 

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // eZ Platform EE
        new EzSystems\EzPlatformPageFieldTypeBundle\EzPlatformPageFieldTypeBundle(),
        new EzSystems\EzPlatformPageBuilderBundle\EzPlatformPageBuilderBundle(),
        // ...      
        new EzSystems\EzPlatformPageBuilderRichTextBlockBundle\EzPlatformPageBuilderRichTextBlockBundle(),
        // ...
        new AppBundle\AppBundle(),
    );
}
```

3. Install bundle using composer:

```bash
composer require ezsystems/ezplatform-page-builder-richtext-block dev-master
```




