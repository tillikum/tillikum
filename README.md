Installation
============

Get Tillikum!
-------------

The best way to do this, currently, is to pull down the latest code from
[svn.tillikum.org](https://svn.tillikum.org). My apologies, but there is no
installer at the moment.

<pre>
# You can check this out somewhere else
$ svn checkout https://svn.tillikum.org/branches/relational-db-migration tillikum
# If you checked the previous copy of code out somewhere else, change this too
$ cd tillikum
$ export TILLIKUM=`pwd`
</pre>

This will check out a copy under `tillikum` in your current working directory.
The `export TILLIKUM` command will set an environment variable so that the
instructions that follow will be easier to understand, but it is not necessary.

Dependencies
------------

Dependencies are managed by [Composer](http://getcomposer.org/).

1. [Download Composer](http://getcomposer.org/download/).
2. Resolve and install dependencies, as shown below. If you need
   [Phing](http://www.phing.info), use the `--dev` flag.

<pre>
# Install dependencies via Composer.
# If your composer binary is called something else, replace `composer' with
# that name.
#
# NOTE: You do not need --dev if you already have Phing installed.
#
# Example: composer.phar with phing installed:
# composer.phar install
#
$ composer install --dev
</pre>

Read the [Composer documentation](http://getcomposer.org/) for more information
on upgrading Tillikum. The `composer.json` file that Tillikum uses is described
in the Composer documentation.

Configure
---------

1. `$ cd ${TILLIKUM}/config`
2. `$ cp -i application.ini.dist application.ini`
3. Modify `application.ini` appropriately. If you just want to get something
   working, you may only need to modify the database connection parameters. Change
   options under `resources.doctrine.dbal.connections.default` to modify the
   Tillikum database connection. See Doctrine documentation for supported database
   drivers.
4. If you run Tillikum in production, it is *highly recommended* that you change
   the caching parameters from the defaults. By default, no caching is set up. See
   the `[production : default]` header for examples and pointers. At some point in
   the future, we may include more complete examples if it turns out to be
   difficult to configure.

**You should not configure a live database until you are comfortable with the
installation process.**

Build
-----

Next, you will build the project. This process is mostly copying files to a
target location, with some token replacement and asset optimization.

1. `$ cd ${TILLIKUM}`
2. `$ sh ./vendor/phing/phing/bin/phing` *or* `$ phing` (use the latter if Phing
   is already on your `PATH`.

You should now have a built Tillikum project.

Database setup
---------------------

**If this is your first time installing Tillikum**, you need to set up the
database schema:

1. `$ cd ${TILLIKUM}/build`
1. `$ ./vendor/bin/doctrine orm:schema-tool:create`

**If you are upgrading Tillikum**, you may need to update the database schema:

1. `$ ./vendor/bin/doctrine orm:schema-tool:update` and check if anything needs
   to be changed.
2. `$ ./vendor/bin/doctrine orm:schema-tool:update --force` once you are ready to
   make the changes from the previous command.

You should have functional software now, all that’s left is to point an
application server at it.

Application server setup
------------------------

**Apache configuration:**

1. Make sure you have `mod_rewrite` enabled.
2. Set up a rule to write all requests to the entry point of Tillikum, which is
   the `index.php` file in the `BUILD_ROOT/www/document_root` directory.

Here is a sample snippet:

<pre>
Alias /tillikum /path/to/tillikum/build/www/document_root
&lt;Directory /path/to/tillikum/build/www/document_root&gt;
    AllowOverride All
    RewriteEngine on
    RewriteBase /tillikum
    RewriteRule .* index.php
    SetEnv APPLICATION_ENV production
&lt;/Directory&gt;
</pre>

Restart Apache and browse to `/tillikum` in your configured virtual host!
Tillikum is completely flexible in terms of where it lives. You can tweak the
above rules any way you like, as long as the core rewrite rules for `index.php`
are kept intact.

**Other servers:**

Not tested. Try it and find out! The software should run in a variety of
environments with no major changes, and the project supports this goal. If you
would like to contribute to this, please
[contact us](https://github.com/tillikum/tillikum/wiki/Contact).

Next Steps
----------

Now that you have a running Tillikum instance, you will want to start writing
plugin code to create a customized and fully-functional system. Refer to
[the Tillikum wiki](https://github.com/tillikum/tillikum/wiki)
for more information!

Troubleshooting
---------------

We don’t get enough questions for a FAQ :)

[Contact us](https://github.com/tillikum/tillikum/wiki/Contact) with your
questions.
