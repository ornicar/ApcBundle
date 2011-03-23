Provide a command line to clear APC cache from the console.

The problem with APC is that it's impossible to clear it from command line.
Because even if you enable APC for PHP CLI, it's a different instance than,
say, your Apache PHP or PHP-CGI APC instance.

The trick here is to create a file in the web dir, execute it through HTTP,
then remove it.

## Installation

### Add ApcBundle to your src/Bundle dir

    git submodule add git://github.com/ornicar/ApcBundle.git src/Bundle/ApcBundle

### Add ApcBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\ApcBundle\ApcBundle(),
            // ...
        );
    }

### Configure

    # app/config/config.yml
    apc:
        host: http://example.com
        web_dir: %kernel.root_dir%/../web

### Use

Clear All APC cache (opcode+user):

    php app/console apc:clear

Clear only opcode cache:

    php app/console apc:clear --opcode

Clear only user cache:

    php app/console apc:clear --user
