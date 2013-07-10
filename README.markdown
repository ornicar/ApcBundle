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

  1. Add it to your composer.json:

      ```json
      {
          "require": {
              "ornicar/apc-bundle": "dev-master"
          }
      }
      ```

     or:

      ```sh
          composer require ornicar/apc-bundle
          composer update ornicar/apc-bundle
      ```

  2. Add this bundle to your application kernel:

          // app/AppKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Ornicar\ApcBundle\OrnicarApcBundle(),
                  // ...
              );
          }

  3. Configure `ornicar_apc` service:

          # app/config/config.yml
          ornicar_apc:
              host: http://example.com
              web_dir: %kernel.root_dir%/../web

  4. If you want to use curl rather than fopen set the following option:

          # app/config/config.yml
          ornicar_apc:
              ...
              mode: curl


Usage
=====

Clear all APC cache (opcode+user):

          $ php app/console apc:clear

Clear only opcode cache:

          $ php app/console apc:clear --opcode

Clear only user cache:

          $ php app/console apc:clear --user


Capifony usage
==============

To automatically clear apc cache after each capifony deploy you can define a custom task

```ruby
namespace :symfony do
  desc "Clear apc cache"
  task :clear_apc do
    capifony_pretty_print "--> Clear apc cache"
    run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} apc:clear --env=#{symfony_env_prod}'"
    capifony_puts_ok
  end
end
```

and add this hook

```ruby
# apc
after "deploy", "symfony:clear_apc"
```

Nginx configuration
===================

If you are using nginx and limiting PHP scripts that you are passing to fpm you need to allow 'apc' prefixed php files. Otherwise your web server will return the requested PHP file as text and the system won't be able to clear the apc cache.

Example configuration:
```
# Your virtual host
server {
  ...
  location ~ ^/(app|app_dev|apc-.*)\.php(/|$) { { # This will allow apc (apc-{MD5HASH}.php) files to be processed by fpm
    fastcgi_pass                127.0.0.1:9000;
    ...
``` 