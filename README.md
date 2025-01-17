# Template
Build template from array data (so from converted Yaml or Json too)

## Get

```
composer require coercive/template
```

## Array structure

Please see `/sample/example.xml`

The field `wrapper` is use to place some html arround your content.

Set `{{content}}` inside the `wrapper` where your want to place your content.

The field `content` will be injected there.

You can also don't use it and place everything you need inside the `wrapper` directly.

The other fields `id`, `type`, `class` ... are setted to do checks, loops, inject specific content for a given id etc...

## Basic load

```php
use Coercive\Utility\Template\ArrayLoader;

# ArrayLoader returns multiple template, so if only one we take it by using [0] (the first row in array).
$template = ArrayLoader::load([$mapped])[0] ?? null;

# If you need to use some methods based on a map system, you have to load maps by using automap method.
if($template) {
    $template->automap();
}

# Render the template
echo $template->getHtml();
```

## Useful methods

Apply automaticaly the tag {{content}} to main template wrapper that don't have one.

```php
$template->setDefaultContentWrapperIfEmpty();
```

Apply automaticaly the tag {{content}} to positions wrapper that don't have one, and match the given types.

```php
$template->setDefaultContentWrapperForAllByTypes(['your_first_type_to_check', 'your_second_type_to_check']);
```

Work with types, ids, classes, namespaces, or all (no extra-check).

---

You have lot's of getters and setters to retrieve or set some data

```php
# See constant options on top of the class.
echo $template->getInternalId();
echo $template->getInternalClass();
echo $template->getInternalNamespace();
echo $template->getInternalType();
```

Access to data field. *(use Coercive/Container)*

```php
echo $template->data()->get('something');
```

---

Inject custom blocks that require internal preparation by your system.

```php
foreach ($template->getPositionByType('custom') as $position) {
    if($position->data()->get('item_type') === 'SLIDE' && $code = (int) $position->data()->get('item_code')) {
        $slide = $$$_Get_Your_Data_Here_For_Example($code);
        if($slide) {
            $html = $$$_Get_Render_System_Here_For_Example('template/example/slide', ['slide_data' => $slide]);
            $position->setWrapper($html); // or setContent() if you have a wrapper to use
        }
    }
}
```