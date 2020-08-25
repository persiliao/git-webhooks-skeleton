# Introduction

This is a skeleton application using the [git-webhooks](https://github.com/persiliao/git-webhooks)

# Requirements

    - PHP >= 7.2
    - JSON PHP extension 

# Installation

```shell script
$ git clone https://github.com/persiliao/git-webhooks-skeleton /to/path
$ cd /to/path
$ composer install
```

# Config

```php
<?php
return [
    // name => Repository name
    'name' => [
        //'driver' => GiteaProvider::class,
        'secret' => '', // Git webhook secret
        'workdir' => '', // Project worker directory
        'command' => [ // Event command
            [
                'branch' => 'master', // Branch
                'event' => 'push', // Event name
                'exec' => [ // Exec command
                    '// rm -rf %s/data'
                ]
            ]
        ]
    ]
];
```

