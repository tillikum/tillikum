Installation
============

Get Tillikum!
-------------

Get the latest code from [Github](https://github.com/tillikum/tillikum):

<pre>
$ git clone git://github.com/tillikum/tillikum.git
$ cd tillikum

# Not necessary, but makes subsequent instructions easier to follow.
$ export TILLIKUM=`pwd`
</pre>

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

The local configuration file overrides settings in the configuration files that
are under version control. The local configuration file is called
`local.config.php`, and we ship a sample `local.config.php.dist` to get you
started.

1. `$ cd ${TILLIKUM}/config`
2. `$ cp -i local.config.php.dist local.config.php`
3. Modify `local.config.php` appropriately. If you just want to get something
   working, you may only need to modify the database connection parameters.
   See Doctrine documentation for supported database drivers for more information.

**You should not configure a live database until you are comfortable with the
installation process.**

Build
-----

Next, you will build the project. This process is mostly copying files to a
target location, with some token replacement and asset optimization.

1. `$ cd ${TILLIKUM}`
2. `$ sh ./vendor/phing/phing/bin/phing` *or* `$ phing` (use the latter if Phing
   is already on your `PATH`).

You should now have the project build artifacts in `${TILLIKUM}/build`.

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
2. Set up a rule to rewrite all requests to the entry point of Tillikum, which
   is `${TILLIKUM}/build/www/document_root/index.php`.

Here is a sample snippet:

<pre>
Alias /tillikum /path/to/tillikum/build/www/document_root
&lt;Directory /path/to/tillikum/build/www/document_root&gt;
    AllowOverride All
    RewriteEngine on
    RewriteBase /tillikum
    RewriteRule .* index.php
&lt;/Directory&gt;
</pre>

Restart Apache and browse to `/tillikum` in your configured virtual host!
Tillikum is completely flexible in terms of where it lives. You can tweak the
above rules any way you like, as long as the core rewrite rules for `index.php`
are kept intact.

**nginx and PHP-FPM**

Just a snippet of the nginx part (this should probably be nested in a
`server` block):

<pre>
root /path/to/tillikum/build/www/document_root;

location /tillikum/ {
    rewrite ^/tillikum(.+) $1 break;

    try_files $uri @tillikum;
}

location @tillikum {
    include fastcgi_params;

    fastcgi_pass  127.0.0.1:9000;
    fastcgi_param SCRIPT_NAME     /tillikum/index.php;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
}
</pre>

**Other servers**

If it runs under `mod_php` and PHP-FPM it should be reasonably straightforward
to configure other servers as well. If you get something working and it isn't
listed here, send a pull request to update the documentation!

Next Steps
----------

Now that you have a running Tillikum instance, you will want to start writing
plugin code to create a customized and fully-functional system. Refer to
[the Tillikum wiki](https://github.com/tillikum/tillikum/wiki)
for more information!

Troubleshooting
---------------

Feel free to
[contact us](https://github.com/tillikum/tillikum/wiki/Contact) with your
questions or
[submit an issue](https://github.com/tillikum/tillikum/issues).
