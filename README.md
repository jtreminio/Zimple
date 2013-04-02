jtreminio/Container
======

This is a wrapper around the simple Pimple container. It provides a few features that makes handling objects
and writing tests much easier.

Installation
=======

Add it to your `composer.json` file:

    "jtreminio/container": "1.0.*@dev"

Then run `./composer.phar update`. This will also install the [Pimple container](https://github.com/fabpot/Pimple)

Now just put it in your code:

    Container::setPimple(new Pimple());

Usage
========

Container accepts a fully qualified name:

    $date = Container::get('\DateTime');

You can also pass in an optional array of parameters for the object constructor:

    $date = Container::get('\DateTime', array('now', Container::get('\DateTimeZone'));

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

Testing
=========

Container makes the process of inserting mocks from your tests extremely easy. It makes use of `::set()` to prevent
the code from overwriting the PHPUnit mock. Check out the tests for several easy examples.
