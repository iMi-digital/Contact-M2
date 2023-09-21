# Contact-M2

Magento 2 contact form with support for custom fields.

Based on (https://github.com/SlavaYurthev/Contact-M2/)

## Installation

`composer require imi/contact-m2`

## Mail Template

You can use the following snipped to include all the non-empty fields:

```
{{var _all_fields_html|raw}}
```

