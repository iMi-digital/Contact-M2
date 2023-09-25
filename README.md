# Contact-M2

Magento 2 contact form with support for custom fields.

Based on (https://github.com/SlavaYurthev/Contact-M2/)

## Installation

`composer require imi/contact-m2`

## Usage 

### Configuration

The plugin is configured at `Stores -> Configuration -> Custom Contact -> Contact Us`

### Recipient

Email type fields will receive the email. If you want to ask for another email, use the text type.


### Mail Template

You can use the following snipped to include all the non-empty fields:

```
{{var _all_fields_html|raw}}
```
