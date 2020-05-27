# Server side rendering JavaScript in your PHP application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/server-side-rendering.svg?style=flat-square)](https://packagist.org/packages/spatie/server-side-rendering)
[![Build Status](https://img.shields.io/travis/spatie/server-side-rendering/master.svg?style=flat-square)](https://travis-ci.org/spatie/server-side-rendering)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/server-side-rendering.svg?style=flat-square)](https://packagist.org/packages/spatie/server-side-rendering)

```php
use Spatie\Ssr\Renderer;
use Spatie\Ssr\Engines\V8;

$engine = new V8();

$renderer = new Renderer($engine);

echo $renderer
    ->entry(__DIR__.'/../../public/js/app-server.js')
    ->render();

// <div>My server rendered app!</div>
```

- Works with any JavaScript framework that allows for server side rendering
- Runs with or without the V8Js PHP extension
- Requires minimal configuration

If you're building a Laravel app, check out the [laravel-server-side-rendering](https://github.com/spatie/laravel-server-side-rendering) package instead.

This readme assumes you already have some know-how about building server rendered JavaScript apps.

## Support us

Learn how to create a package like this one, by watching our premium video course:

[![Laravel Package training](https://spatie.be/github/package-training.jpg)](https://laravelpackage.training)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Who's this package for?

Server side rendering (SSR) can be hard, and non-trivial to enable in your JavaScript application. Before using this library, make sure you know what you're getting in to. Alex Grigoryan has a [pretty concise article](https://medium.com/walmartlabs/the-benefits-of-server-side-rendering-over-client-side-rendering-5d07ff2cefe8) on the benefits and caveats of SSR. Anthony Gore also has a great article on [server side rendering a Vue application in Laravel](https://vuejsdevelopers.com/2017/11/06/vue-js-laravel-server-side-rendering/), which inspired this library.

In case you're in need of a refresher...

- SSR reduces the time until the [first meaningful paint](https://www.quora.com/What-does-First-Meaningful-Paint-mean-in-Web-Performance), providing a better experience for your users
- SSR is recommended if you need to tailor your app for crawlers that can't execute JavaScript (SEO)
- SSR adds a meaningful amount of complexity to your application
- SSR can increase response times and the overall load on your server

When you've got an answer to the "Do I need SSR?" question, ask yourself if you need SSR in a PHP application. Benefits of rendering your app in a PHP runtime are:

- Access to your application's session & state, which you normally don't if your SPA is consuming a stateless api
- Reduced infrastructure complexity because you don't need to maintain a node server

If you're building a SPA that connects to an external API, and the PHP runtime doesn't provide any extra value, you're probably better off using a battle tested solution like [Next.js](https://github.com/zeit/next.js/) or [Nuxt.js](https://nuxtjs.org).

As a final disclaimer, judging by the amount—well, lack—of people blogging about rendering JavaScript applications in PHP, this whole setup is uncharted territory. There may be more unknown caveats lurking around the corner.

If you're still sure you want to keep going, please continue!

## Installation

You can install the package via composer:

```bash
composer require spatie/server-side-rendering
```

## Usage

### Your JavaScript app's architecture

This guide assumes you already know how to build a server-rendered application. If you're looking for reading material on the subject, Vue.js has a [very comprehensive guide](https://ssr.vuejs.org/en/) on SSR. It's Vue-specific, but the concepts also translate to other frameworks like React.

### Engines

An engine executes a JS script on the server. This library ships with two engines: a `V8` engine which wraps some `V8Js` calls, so you'll need to install a PHP extension for this one, and a `Node` engine which builds a node script at runtime and executes it in a new process. An engine can run a script, or an array of multiple scripts.

The `V8` engine is a lightweight wrapper around the `V8Js` class. You'll need to install the [v8js extension](https://github.com/phpv8/v8js) to use this engine.

The `Node` engine writes a temporary file with the necessary scripts to render your app, and executes it in a node.js process. You'll need to have [node.js](https://nodejs.org) installed to use this engine.

### Rendering options

You can chain any amount of options before rendering the app to control how everything's going to be displayed.

```php
echo $renderer
    ->disabled($disabled)
    ->context('user', $user)
    ->entry(__DIR__.'/../../public/js/app-server.js')
    ->render();
```

#### `enabled(bool $enabled = true): $this`

Enables or disables server side rendering. When disabled, the client script and the fallback html will be rendered instead.

#### `debug(bool $debug = true): $this`

When debug is enabled, JavaScript errors will cause a php exception to throw. Without debug mode, the client script and the fallback html will be rendered instead so the app can be rendered from a clean slate.

#### `entry(string $entry): $this`

The path to your server script. The contents of this script will be run in the engine.

#### `context($context, $value = null): $this`

Context is passed to the server script in the `context` variable. This is useful for hydrating your application's state. Context can contain anything that json-serializable.

```php
echo $renderer
    ->entry(__DIR__.'/../../public/js/app-server.js')
    ->context('user', ['name' => 'Sebastian'])
    ->render();
```

```js
// app-server.js

store.user = context.user // { name: 'Sebastian' }

// Render the app...
```

Context can be passed as key & value parameters, or as an array.

```php
$renderer->context('user', ['name' => 'Sebastian']);
```

```php
$renderer->context(['user' => ['name' => 'Sebastian']]);
```

#### `env($env, $value = null): $this`

Env variables are placed in `process.env` when the server script is executed. Env variables must be primitive values like numbers, strings or booleans.

```php
$renderer->env('NODE_ENV', 'production');
```

```php
$renderer->env(['NODE_ENV' => 'production']);
```

#### `fallback(string $fallback): $this`

Sets the fallback html for when server side rendering fails or is disabled. You can use this to render a container for the client script to render the fresh app in.

```php
$renderer->fallback('<div id="app"></div>');
```

#### `resolveEntryWith(callable $resolver): $this`

Add a callback to transform the entry when it gets resolved. It's useful to do this when creating the renderer so you don't have to deal with complex paths in your views.

```php
echo $renderer
    ->resolveEntryWith(function (string $entry): string {
        return __DIR__."/../../public/js/{$entry}-server.js";
    })
    ->entry('app')
    ->render();
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Sebastian De Deyne](https://github.com/sebastiandedeyne)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
