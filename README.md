Zimple
======

This is a wrapper around the simple Pimple container. It provides a few features that makes handling objects
and writing tests much easier.

If you have a codebase that is untestable and you are unable to offer deep refactoring to make it untestable, having a
container such as this one is a good step toward gaining control over your codebase.

Installation
=======

Add it to your `composer.json` file:

    "jtreminio/zimple": "1.0.*@dev"

Then run `./composer.phar update`. This will also install the [Pimple container](https://github.com/fabpot/Pimple)

Now just put it in your code:

    Zimple::setPimple(new Pimple);

Usage
========

Zimple accepts a fully qualified name:

    $date = Zimple::get('\DateTime');

You can also pass in an optional array of parameters for the object constructor:

    $date = Zimple::get('\DateTime', array('now', Zimple::get('\DateTimeZone')));

By default Zimple returns a new instance of an object everytime you call `Zimple::get()`. You can override this
behavior by calling `Zimple::set()` and setting the third parameter to `true`:

    $today = Zimple::get('\DateTime');
    $tomorrow = Zimple::get('\DateTime', array('tomorrow'));

    // $today !== $tomorrow

    Zimple::set('\DateTime', $tomorrow, true);

    $tomorrowDup = Zimple::get('\DateTime');

    // $tomorrowDup == $tomorrow

    $twoDaysAgo = Zimple::get('\DateTime', array('2 days ago'));

    // $twoDaysAgo == $tomorrow

You can define objects as you normally would for Pimple before or after passing to Zimple:

    // From Pimple's homepage
    $pimple = new Pimple;

    // define some objects
    $pimple['session_storage'] = function ($c) {
        return new $c['session_storage_class']($c['cookie_name']);
    };

    $pimple['session'] = function ($c) {
        return new Session($c['session_storage']);
    };

    Zimple::setPimple($pimple);

Then access them as normal: `$session = Zimple::get('session_storage');`

Zimple is a Weird Name
=========

Zimple wraps around Pimple, which is a gross name. [SandyZoop](http://www.reddit.com/user/SandyZoop) recommended
I name it Cyst or Zit, which is equally gross. The goal of this container is to make dependency management simple
and easy to use, and make previously untestable code testable. Simple -> Zimple.

You can always alias it with

    use jtreminio\Zimple\Zimple as Container;
    Container::setPimple(new Pimple);
    $date = Container::get('\DateTime');

Testing
=========

Zimple makes the process of inserting mocks from your tests extremely easy. It makes use of `::set()` to prevent
the code from overwriting the PHPUnit mock. Check out the tests for several easy examples.
