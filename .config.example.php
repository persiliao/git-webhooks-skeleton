<?php
declare(strict_types=1);

/**
 * @author Persi.Liao
 * @email xiangchu.liao@gmail.com
 * @link https://www.github.com/persiliao
 */

use PersiLiao\GitWebhooks\Provider\GiteaProvider;

return [
    'name' => [
        //'driver' => GiteaProvider::class,
        'secert' => '',
        'workdir' => '',
        'command' => [
            [
                'branch' => 'master',
                'event' => 'push',
                'exec' => [
                    '// rm -rf %s/data'
                ]
            ]
        ]
    ]
];