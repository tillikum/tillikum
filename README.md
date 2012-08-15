Installation
============

Dependencies
------------

* PHP 5.3+
* Zend Framework 1.11+
* Doctrine ORM
* Tillikum code
* A web server

PHP extensions
--------------

We require the following PHP extensions:

* bcmath (for accurate financial calculations)
* ctype
* fileinfo
* iconv
* intl (l10n and i18n)
* json
* mbstring
* Reflection
* session
* SPL

The above extensions are checked at runtime, and you will be notified if they are not installed.

PHP requirements
----------------

You will need to make sure PHP’s `magic_quotes_gpc` is off as well.

Dependency installation
-----------------------

The easiest way to get Zend Framework and Doctrine is from PEAR, but you can install it any number of ways as long as it is on PHP's `include_path`.

<pre>
# Zend Framework
pear channel-discover pear.zfcampus.org

pear install pear.zfcampus.org/ZF

# Doctrine
pear channel-discover pear.symfony.com
pear channel-discover pear.doctrine-project.org

pear install pear.doctrine-project.org/DoctrineORM
</pre>

Tillikum installation
---------------------

Next, you’ll need to get the code from tillikum.org. There is no installer at the moment, so you'll need to check out a copy from Subversion.

<pre>
svn checkout https://svn.tillikum.org tillikum
</pre>

Configuration
-------------

I will use `TILLIKUM_ROOT` to denote the root of the project, wherever you have it checked out.

Change directory to `TILLIKUM_ROOT/www/application/config`, and copy `application.ini.dist` to `application.ini`, and modify appropriately. At first, you should only need to modify the database connection parameters. Change `resources.doctrine.dbal.connections.default` options to modify the Tillikum database connection. See Doctrine documentation for supported database drivers.

**Make sure you point your configuration at an empty database. You should not use a live database until you are comfortable with the installation process.**

Database installation
---------------------

If you installed Doctrine from PEAR, you should be able to change directory to `TILLIKUM_ROOT/bin` and type `doctrine` and get help text for the command line interface to the Doctrine ORM. Do the following:

1. `doctrine orm:generate-proxies` which will create proxy entities (this is a Doctrine implementation detail, if you don't understand it, don't worry)
2. `doctrine orm:schema-tool:create` which will generate the schema for your relational database and add it to the database you configured above

If that was successful, you should be almost ready…

Frontend configuration
----------------------

Point a webserver at `document_root` of the checked out code.

**Apache configuration**

Make sure you have `mod_rewrite` enabled.

Here is a sample snippet:

<pre>
Alias /tillikum /path/to/tillikum/www/document_root
&lt;Directory /path/to/tillikum/www/document_root&gt;
    AllowOverride All
    RewriteEngine on
    RewriteBase /tillikum
    RewriteRule .* index.php
    SetEnv APPLICATION_ENV development
&lt;/Directory&gt;
</pre>

Restart Apache and browse to `/tillikum` in your configured virtual host!

**Note:** Apache httpd is by no means required to run Tillikum. If you have
another webserver, please submit working snippets and I'll include them here.

Further configuration
---------------------

That was just the beginning. Now that you have a running Tillikum instance, you will want to start writing plugin code to create a fully-functional system. Refer to [the Tillikum wiki](http://tillikum.org/wiki) for more information!
