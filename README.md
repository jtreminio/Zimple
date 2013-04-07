Zimple
======

This is a wrapper around the simple Pimple container. It provides a few features that makes handling objects
and writing tests much easier.

If you have a codebase that is untestable and you are unable to offer deep refactoring to make it untestable, having a
container such as this one is a good step toward gaining control over your codebase.

Installation
=======

Add it to your `composer.json` file:

    "jtreminio/container": "1.0.*@dev"

Then run `./composer.phar update`. This will also install the [Pimple container](https://github.com/fabpot/Pimple)

Now just put it in your code:

    Container::setPimple(new Pimple);

Usage
========

Container accepts a fully qualified name:

    $date = Container::get('\DateTime');

You can also pass in an optional array of parameters for the object constructor:

    $date = Container::get('\DateTime', array('now', Container::get('\DateTimeZone')));

By default Container returns a new instance of an object everytime you call `Container::get()`. You can override this
behavior by calling `Container::set()` and setting the third parameter to `true`:

    $today = Container::get('\DateTime');
    $tomorrow = Container::get('\DateTime', array('tomorrow'));

    // $today !== $tomorrow

    Container::set('\DateTime', $tomorrow, true);

    $tomorrowDup = Container::get('\DateTime');

    // $tomorrowDup == $tomorrow

    $twoDaysAgo = Container::get('\DateTime', array('2 days ago'));

    // $twoDaysAgo == $tomorrow

You can define objects as you normally would for Pimple before or after passing to Container:

    // From Pimple's homepage
    $pimple = new Pimple;

    // define some objects
    $pimple['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

    $pimple['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

    Container::setPimple($pimple);

Then access them as normal: `$session = Container::get('session_storage');`

Testing
=========

Container makes the process of inserting mocks from your tests extremely easy. It makes use of `::set()` to prevent
the code from overwriting the PHPUnit mock. Check out the tests for several easy examples.
