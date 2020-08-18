<?php
declare(strict_types=1);

/**
 * @author Persi.Liao
 * @email xiangchu.liao@gmail.com
 * @link https://www.github.com/persiliao
 */

use PersiLiao\GitWebhooks\Provider\GiteaProvider;

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
