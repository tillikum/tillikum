Installation
============

Dependencies
------------

* PHP 5.3.3+
* Zend Framework 1.11.0+
* Doctrine ORM 2.3.0+
* Phing
* Tillikum code
* A web server

PHP requirements
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

The above extensions are checked by the Phing build system in the `check`
target.

You will need to make sure PHP’s `magic_quotes_gpc` is off.

Dependency installation
-----------------------

The easiest way to get the Zend Framework is via tarball or `svn export`, but
you can install it any number of ways as long as it is on PHP's `include_path`.

<pre>
# Zend Framework
cd /usr/share/php # or another place on PHP's path
svn export http://framework.zend.com/svn/framework/standard/tags/release-1.12.0/library/Zend
</pre>

The above command will export the ZF 1.12.0 release library subtree to the
current directory. **Careful!** Uninstall Zend Framework if you had it installed
another way before performing this step.

The easiest way to get Doctrine is from PEAR, but you can install it any number
of ways as long as it is on PHP's `include_path`.

<pre>
# Doctrine
pear channel-discover pear.symfony.com
pear channel-discover pear.doctrine-project.org

pear install pear.doctrine-project.org/DoctrineORM
</pre>

The easiest way to get Phing is from PEAR, but you can install it however you’d
like.

<pre>
# Phing
pear channel-discover pear.phing.info

pear install pear.phing.info/phing
</pre>

Tillikum installation
---------------------

Next, you’ll need to get the code from tillikum.org. There is no installer at
the moment, so you'll need to check out a copy from Subversion.

<pre>
svn checkout https://svn.tillikum.org tillikum
</pre>

From here on out, I will use `TILLIKUM_ROOT` to denote the root of the project.
This may be `trunk` or one of the branches depending on what you want to
install.

1. Change directory to `TILLIKUM_ROOT/config`.
2. Copy `application.ini.dist` to `application.ini` if you have not already done so.
3. Modify `application.ini` appropriately. If you "just want to get something working," you should only need to modify the database connection parameters. Change options under `resources.doctrine.dbal.connections.default` to modify the Tillikum database connection. See Doctrine documentation for supported database drivers.

**You should not use a live database until you are comfortable with the installation process.**

Next, you will build the project, which will perform environment checks and copy
necessary files.

1. Change your directory to `TILLIKUM_ROOT`.
2. Run `phing`.
3. If everything is successful, you should now have a `TILLIKUM_ROOT/build` directory.
4. `cd TILLIKUM_ROOT/build` and from here on out we will use `BUILD_ROOT` to refer to this directory.

Database setup
---------------------

If you installed Doctrine from PEAR, you should be able to change directory to
`BUILD_ROOT/bin` and type `doctrine` and get help text for the command line
interface to the Doctrine ORM. Do the following:

1. `doctrine orm:generate-proxies` which will create proxy entities (this is a Doctrine implementation detail, if you don't understand it, don't worry).
2. `doctrine orm:schema-tool:create` which will generate the schema for your relational database and add it to the database you configured above.

If that was successful, you’ve got some functional software.

Frontend configuration
----------------------

Point a webserver at `BUILD_ROOT/www/document_root`.

**Apache configuration**

Make sure you have `mod_rewrite` enabled.

Here is a sample snippet:

<pre>
Alias /tillikum /path/to/tillikum/build/www/document_root
&lt;Directory /path/to/tillikum/build/www/document_root&gt;
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

Next Steps
----------

That was just the beginning. Now that you have a running Tillikum instance, you
will want to start writing plugin code to create a customized and
fully-functional system.  Refer to
[the Tillikum wiki](https://github.com/tillikum/tillikum/wiki)
for more information!
