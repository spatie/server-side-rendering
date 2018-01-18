# Server side rendering JavaScript in your PHP application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/server-side-rendering.svg?style=flat-square)](https://packagist.org/packages/spatie/server-side-rendering)
[![Build Status](https://img.shields.io/travis/spatie/server-side-rendering/master.svg?style=flat-square)](https://travis-ci.org/spatie/server-side-rendering)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/xxxxxxxxx.svg?style=flat-square)](https://insight.sensiolabs.com/projects/xxxxxxxxx)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/server-side-rendering.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/server-side-rendering)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/server-side-rendering.svg?style=flat-square)](https://packagist.org/packages/spatie/server-side-rendering)

```php
use Spatie\Ssr\Renderer;
use Spatie\Ssr\Engines\V8;
use Spatie\Ssr\Resolvers\DefaultResolver;

$engine = new V8();
$resolver = new DefaultResolver(__DIR__.'../../public/js');

$renderer = new Renderer($engine, $resolver);

echo $renderer->entry('app')->render();

// <div>My server rendered app!</div><script src="/app-client.js"></script>
```

- Works with any JavaScript framework that allows for server side rendering
- Runs with or without the V8Js PHP extension
- Requires minimal configuration

If you're building a Laravel app, check out the [laravel-ssr](https://github.com/spatie/laravel-ssr) package instead.

This readme assumes you already have some know-how about building server rendered JavaScript apps.

## Who's this package for?

Server side rendering (SSR) can be hard, and non-trivial to enable in your JavaScript application. Before using this library, make sure you know what you're getting in to. Alex Grigoryan has a [pretty concise article](https://medium.com/walmartlabs/the-benefits-of-server-side-rendering-over-client-side-rendering-5d07ff2cefe8) on the benefits and caveats of SSR. Anthony Gore also has a great article on [server side rendering a Vue application in Laravel](https://vuejsdevelopers.com/2017/11/06/vue-js-laravel-server-side-rendering/), which inspired this library.

In case you're in need of a refresher...

- SSR is useful if you need to tailor your app for crawlers that can't execute JavaScript (SEO)
- SSR reduces the time until the [first meaningful paint](https://www.quora.com/What-does-First-Meaningful-Paint-mean-in-Web-Performance)
- SSR increases response times, and the overall load on your server
- SSR adds a meaningful amount of complexity to your application

When you've got an answer to the "Do I need SSR?" question, ask yourself if you need SSR in a PHP application. Benefits of rendering your app in a PHP runtime are:

- You can make use of your applications session & state
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

### Core concepts

Before getting started, let's review this library's core concepts.

If you want to render your JavaScript app, you'll need to call the `render` method on the `Renderer` class. `Renderer` has two dependencies: a `Resolver` and an `Engine`, which will respectively fetch the necessary server & client scripts, and execute the server script.

#### Resolvers

When server side rendering a JavaScript app, your app will have two entry points: one for the server, which generates static html for the first paint, and one for the client, which bootstraps the application in the browser.

The `Resolver` interface needs to be able to do two things, both based on the same `$entry` string.

- Return the server script contents
- Return a client script url

A directory structure for a project that allows SSR commonly often looks similar to this:

```
app/
  ...
public/
  js/
    app-client.js
    app-server.js
```

The `DefaultResolver` has a root path, and looks for files in that directory.

```php
use Spatie\Ssr\Resolvers\DefaultResolver;

// Set the root path to an absolute path containing your scripts
$resolver = new DefaultResolver(__DIR__.'/../../public/js');

$resolver->getClientScriptUrl('app'); // 'js/app-client.js'
$resolver->getServerScript('app'); // <app-server.js contents>
```

The client script url is needed on the webpage, and the server script will be executed by an `Engine` instance.

#### Engines

An engine executes a JS script on the server. This library ships with two engines: a `V8` engine which wraps some `V8Js` calls, so you'll need to install a PHP extension for this one, and a `Node` engine which builds a node script at runtime and executes it in a new process. An engine can run a script, or an array of multiple scripts.

The `V8` engine is a lightweight wrapper around the `V8Js` class. You'll need to install the [v8js extension](https://github.com/phpv8/v8js) to use this engine.

The `Node` engine writes a temporary file with the necessary scripts to render your app, and executes it in a node.js process. You'll need to have [node.js](https://nodejs.org) installed to use this engine.

### Rendering options

You can chain any amount of options before rendering the app to control how everything's going to be displayed.

```php
echo $renderer
    ->disabled($disabled)
    ->withContext('user', $user)
    ->entry('app')
    ->render();
```

#### `enabled(bool $enabled = true)`

Enables or disables server side rendering. When disabled, the client script and the fallback html will be rendered instead.

#### `debug(bool $debug = true)`

When debug is enabled, JavaScript errors will cause a php exception to throw. Without debug mode, the client script and the fallback html will be rendered instead so the app can be rendered from a clean slate.

#### `entry(string $entry)`

The entry identifier of your app. This will be used by the resolver to retrieve the client script url and server script contents.

#### `withContext($context, $value = null)`

Context is passed to the server script in the `context` variable. This is useful for hydrating your application's state. Context can contain anything that json-serializable.

```php
echo $renderer
    ->entry('app')
    ->withContext('user', ['name' => 'Sebastian'])
    ->render();
```

```js
// app-server.js

store.user = context.user // { name: 'Sebastian' }

// Render the app...
```

Context can be passed as key & value parameters, or as an array.

```php
$renderer->withContext('user', ['name' => 'Sebastian']);
```

```php
$renderer->withContext(['user' => ['name' => 'Sebastian']]);
```

#### `withEnv($env, $value = null)`

Env variables are placed in `process.env` when the server script is executed. Env variables must be primitive values like numbers, strings or booleans.

```php
$renderer->withEnv('NODE_ENV', 'production');
```

```php
$renderer->withEnv(['NODE_ENV' => 'production']);
```

#### `withFallback(string $fallback)`

Sets the fallback html for when server side rendering fails or is disabled. You can use this to render a container for the client script to render the fresh app in.

```php
$renderer->withFallback('<div id="app"></div>');
```

#### `withScript()` & `withoutScript()`

Determines whether or not a script tag should be rendered in the client view. By default, script tags are rendered.

```php
echo $renderer->withScript();
// <div>My server rendered app!</div><script src="/app-client.js"></script>
```

```php
echo $renderer->withoutScript();
// <div>My server rendered app!</div>
```

#### `loadScriptAfter()` & `loadScriptBefore()`

Determines whether a script tag should be rendered before or after the server rendered html. By default, script tags are rendered after the html.

```php
echo $renderer->loadScriptAfter();
// <div>My server rendered app!</div><script src="/app-client.js"></script>
```

```php
echo $renderer->loadScriptBefore();
// <script src="/app-client.js"></script><div>My server rendered app!</div>
```

#### `loadScriptSync()`, `loadScriptAsync()` & `loadScriptDeferred()`

Determines how the script should be loaded on the page. By default, scripts have no special loading attributes (sync).

```php
echo $renderer->loadScriptSync();
// <div>My server rendered app!</div><script src="/app-client.js"></script>
```

```php
echo $renderer->loadScriptAsync();
// <div>My server rendered app!</div><script async src="/app-client.js"></script>
```

```php
echo $renderer->loadScriptDeferred();
// <div>My server rendered app!</div><script defer src="/app-client.js"></script>
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

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie).
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
