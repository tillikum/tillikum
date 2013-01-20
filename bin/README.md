Binaries and Scripts
====================

These are not part of the web application, but are instead scripts and daemons
that support the system.

tillikum-jobd
-------------

The Tillikum job daemon watches for new jobs and runs the executable path
specified in the job as a child process.

You may run the job daemon anywhere you have computing power, it does not need
to run on the webserver. It may be preferable to run them on another server if
you have a lot of jobs or the jobs require a lot of resources.
