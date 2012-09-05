Provide a command line to clear APC cache from the console.

The problem with APC is that it's impossible to clear it from command line.
Because even if you enable APC for PHP CLI, it's a different instance than,
say, your Apache PHP or PHP-CGI APC instance.

The trick here is to create a file in the web dir, execute it through HTTP,
then remove it.

Prerequisite
============

If you want to clear Apache part of APC, you will need to enable `allow_url_fopen` in `php.ini` to allow opening of URL
object-like files, or set the curl option.



Installation
============

  1. If you're using Composer, require this package and skip to step 4:

          $ composer.phar require ornicar/apc-bundle

  2. Otherwise, add this bundle to your project as a Git submodule:

          $ git submodule add git://github.com/ornicar/ApcBundle.git vendor/Bundles/Ornicar/ApcBundle

  3. Add `Ornicar` namespace to your autoloader:

          // app/autoload.php
          $loader->registerNamespaces(array(
             'Ornicar' => __DIR__.'/../vendor/bundles',
             // your other namespaces
          );

  4. Add this bundle to your application kernel:

          // app/AppKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Ornicar\ApcBundle\OrnicarApcBundle(),
                  // ...
              );
          }

  5. Configure `ornicar_apc` service:

          # app/config/config.yml
          ornicar_apc:
              host: http://example.com
              web_dir: %kernel.root_dir%/../web

  6. If you want to use curl rather than fopen set the following option:

          # app/config/config.yml
          ornicar_apc:
              ...
              mode: curl

  7. If you're using Composer and want to clear the APC cache automatically after installing or updating, then add `"Ornicar\\ApcBundle\\Composer\\ScriptHandler::clearApc"` twice to the `scripts` section of your composer.json:

          "scripts": {
              "post-install-cmd": [
                  "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
                  "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
                  "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
                  "Ornicar\\ApcBundle\\Composer\\ScriptHandler::clearApc"
              ],
              "post-update-cmd": [
                  "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
                  "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
                  "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
                  "Ornicar\\ApcBundle\\Composer\\ScriptHandler::clearApc"
              ]
          },


Usage
=====

Clear all APC cache (opcode+user):

          $ php app/console apc:clear

Clear only opcode cache:

          $ php app/console apc:clear --opcode

Clear only user cache:

          $ php app/console apc:clear --user
