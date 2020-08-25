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
        'secret' => '', // Git webhook secret
        'workdir' => '' // Project worker directory
    ]
];
