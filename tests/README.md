Testing Tillikum
================

Note that you need to install PHPUnit 3.5. This is currently required by Zend
Framework 1 and there are no plans to change it. There is a script which will
download and unpack PHPUnit 3.5 locally which is where the `phpunit` script will
expect it.

If, for whatever reason, you are already running PHPUnit 3.5, you do not need to
do this.

PHPUnit installation
--------------------

1. **If you do not have PHPUnit 3.5**: Run `./install-phpunit-3.5.sh` and when
   it finishes you should have `phpunit-3.5` containing a self-contained PHPUnit
   3.5 installation.

PHPUnit configuration
---------------------

1. Copy `phpunit.xml.dist` to `phpunit.xml` and make modifications indicated in
   the comments if you wish to run extension tests.

Running tests
-------------

1. `./phpunit` will run the entire test suite from top to bottom. See
   [PHPUnit 3.5 Documentation](http://www.phpunit.de/manual/3.5/en/) for more
   information on what PHPUnit can do, including running subsets of tests.
