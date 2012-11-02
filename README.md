Installation
============

Get Tillikum!
-------------

The best way to do this, currently, is to pull down the latest code from
[svn.tillikum.org](https://svn.tillikum.org). My apologies, but there is no
installer at the moment.

<pre>
svn checkout https://svn.tillikum.org/branches/relational-db-migration tillikum
</pre>

This will check out a copy under `tillikum` in your current working directory.

Dependencies
------------

Dependencies are managed by [Composer](http://getcomposer.org/download/). It
doesn’t matter how you install it, but it is a very useful package in the modern
PHP ecosystem, so you might want to put it somewhere you can reuse it.

<pre>
# Install dependencies via Composer.
# Depending on your approach, you might need to use `composer.phar' instead
# of `composer'.
$ composer install
</pre>

Read the [Composer documentation](http://getcomposer.org/) for more information
on updating, and what else you can do with Composer.

If you're interested in the details, read the `composer.json` file to see what
is required and what will be installed - it is quite readable.

Configure
---------

I will now use `TILLIKUM_ROOT` to denote the root of the project, which is the
directory you checked out earlier.

1. Change directory to `TILLIKUM_ROOT/config`.
2. Copy `application.ini.dist` to `application.ini` if you have not already done so.
3. Modify `application.ini` appropriately. If you "just want to get something
   working," you should only need to modify the database connection parameters.
   Change options under `resources.doctrine.dbal.connections.default` to modify the
   Tillikum database connection. See Doctrine documentation for supported database
   drivers.

**You should not configure a live database until you are comfortable with the
installation process.**

Build
-----

Next, you will build the project. This process is mostly copying files to a
target location, with some token replacement and asset optimization.

1. Change your directory to `TILLIKUM_ROOT`.
2. Run `phing`. If you are following these instructions and do not already have
   Phing installed, it is probably at
   `TILLIKUM_ROOT/vendor/phing/phing/bin/phing.php` and you can run that with
   `php TILLIKUM_ROOT/vendor/phing/phing/bin/phing.php`.
3. If everything is successful, you should now have a `TILLIKUM_ROOT/build`
   directory.
4. `cd TILLIKUM_ROOT/build`

From here on out, `TILLIKUM_ROOT/build` will be referred to as `BUILD_ROOT`.

Database setup
---------------------

**If this is your first time installing Tillikum**, you need to set up the
database schema:

1. `doctrine orm:schema-tool:create`

**If you are upgrading Tillikum**, you may need to update the database schema:

1. `doctrine orm:schema-tool:update` to see if anything needs to be change, and
2. `doctrine orm:schema-tool:update --force` to push the changes to your
   configured database.

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
