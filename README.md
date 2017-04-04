# Site [![Build Status](https://travis-ci.org/Icybee/module-sites.svg?branch=2.0)](https://travis-ci.org/Icybee/module-sites)

The Site module (`sites`) manages the websites served by [Icybee][].





## Event hooks





### `ICanBoogie\Application::run`

Initializes the `site`, `locale` and `timezone` properties of the core object. If the website has
a base path, the `contextualize()` and `decontextualize()` helpers of the [Routing](https://github.com/ICanBoogie/Routing)
package are patched.





### `ICanBoogie\HTTP\RequestDispatcher::dispatch:before`

Redirects the request to the first available website to the user if the request matches none.





## Prototype methods





### `Icybee\Modules\Nodes\Node::get_site`

Returns the website a node belongs to.

```php
<?php

$app->models['nodes']->one->site;
```





### `ICanBoogie\Application::get_site`

Returns the website matching the current request.

```php
<?php

$app->site;
# or
$app->request->context->site;
```





### `ICanBoogie\Application::get_site_id`

Returns the identifier of the website matching the current request.

```php
<?php

$app->site_id;
# or
$app->request->context->site_id;
```





### `ICanBoogie\HTTP\Request\Context::get_site`

Returns the website matching the request context.

```php
<?php

$app->request->context->site;
```





### `ICanBoogie\HTTP\Request\Context::get_site_id`

Returns the identifier of the website matching the request context.

```php
<?php

$app->request->context->site_id;
```





----------





## Requirement

The package requires PHP 5.6 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/):

```
$ composer require icybee/module-sites
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-sites), its repository can be
cloned with the following command line:

	$ git clone https://github.com/Icybee/module-sites.git sites





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later be
cleaned with the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/module-sites.svg?branch=2.0)](https://travis-ci.org/Icybee/module-sites)





## License

The module is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.

[Icybee]: http://icybee.org/
