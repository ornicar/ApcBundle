Provide a command line to clear APC cache from the console.

The problem with APC is that it's impossible to clear it from command line.
Because even if you enable APC for PHP CLI, it's a different instance than,
say, your Apache PHP or PHP-CGI APC instance.

The trick here is to create a file in the web dir, execute it through HTTP,
then remove it.

Prerequisite
============

If you want to clear Apache part of APC, you will need to enable `allow_url_fopen` in `php.ini` to allow opening of URL object-like files.



Installation
============

  1. Add this bundle to your project as a Git submodule:

        $ git submodule add git://github.com/ornicar/ApcBundle.git vendor/Bundles/Ornicar/ApcBundle

  2. Add `Ornicar` namespace to your autoloader:

        // app/autoload.php
        $loader->registerNamespaces(array(
           'Ornicar' => __DIR__.'/../vendor/bundles',
           // your other namespaces
        );

  3. Add this bundle to your application kernel:

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new Ornicar\ApcBundle\OrnicarApcBundle(),
                // ...
            );
        }

  4. Configure `ornicar_apc` service:

        # app/config/config.yml
        ornicar_apc:
            host: http://example.com
            web_dir: %kernel.root_dir%/../web

Usage
=====

Clear all APC cache (opcode+user):

    $ php app/console apc:clear

Clear only opcode cache:

    $ php app/console apc:clear --opcode

Clear only user cache:

    $ php app/console apc:clear --user
