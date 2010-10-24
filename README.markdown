Provide a command line to clear APC cache from the console.

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
    apc.config:
        url: http://example.com/
        web_dir: %kernel.root_dir%/../web

### Use

Clear All APC cache (opcode+user):

    php app/console apc:clear --all

Clear opcode cache:

    php app/console apc:clear --opcode

Clear user cache:

    php app/console apc:clear --user
