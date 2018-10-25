# REST API Functions

Bring functions into WordPress REST API.

## Example

Create `hello.php` in `wp-content/themes/NAME/functions/hello.php'`

```php
<?php

function handler($request) {
	return 'Hello, world';
}
```

Call it 

```shell
GET /wp-json/functions/v1/hello

"Hello, world"
```

Supported HTTP methods are for each function file are:

`GET, POST, PUT, PATCH, DELETE`

## Install

```
composer require wpup/functions
```

## Filters

Short documenation about filters, read the source code to find out more about each filter.

- `functions_file` - Modify file path
- `functions_handle` - Modify function/method string name (that are used for `call_user_func`)
- `functions_directories` - Modify which directories the plugin should look for functions file. Default is `wp-content/themes/NAME/functions`

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
