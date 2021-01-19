<?php
declare(strict_types=1);
/**
 * @author Persi.Liao
 * @email xiangchu.liao@gmail.com
 * @link https://www.github.com/persiliao
 */

require __DIR__ . '/vendor/autoload.php';

use PersiLiao\GitWebhooks\Exception\InvalidArgumentException as GitWebhooksInvalidArgumentException;
use PersiLiao\GitWebhooks\Provider\GiteaProvider;
use PersiLiao\GitWebhooks\Provider\GiteeProvider;
use PersiLiao\GitWebhooks\Provider\GithubProvider;
use PersiLiao\GitWebhooks\Provider\GitlabProvider;
use PersiLiao\GitWebhooks\Provider\GogsProvider;
use PersiLiao\GitWebhooks\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

try{
    $response = new Response();
    if(file_exists(__DIR__ . '/config.php') === false){
        throw new GitWebhooksInvalidArgumentException('config.php file is missing');
    }
    $config = require __DIR__ . '/config.php';
    if(!isset($config) || !is_array($config) || empty($config)){
        throw new GitWebhooksInvalidArgumentException('Please check the config.php file configuration');
    }
    $secrets = [];

    foreach($config as $name => $conf){
        if(empty($name) || !is_string($name)){
            throw new GitWebhooksInvalidArgumentException('Please check the config.php file name configuration');
        }
        if(!isset($conf['secret'], $conf['workdir'])){
            throw new GitWebhooksInvalidArgumentException('Please check the config.php file secret, workdir configuration');
        }
        if(!is_dir($conf['workdir'])){
            throw new GitWebhooksInvalidArgumentException('Please check the config.php file WorkerDirectory configuration');
        }
        if(isset($conf['command']) && !empty($conf['command']) && is_array($conf['command'])){
            foreach($conf['command'] as $command){
                if(!isset($command['event'], $command['exec'])){
                    throw new GitWebhooksInvalidArgumentException('Please check the config.php file command configuration');
                }
            }
        }
        $secrets[$name] = $conf['secret'];
    }
    $request = Request::createFromGlobals();
    $repository = new Repository([
        new GiteaProvider($request),
        new GithubProvider($request),
        new GitlabProvider($request),
        new GiteeProvider($request),
        new GogsProvider($request)
    ], $secrets);
    $event = $repository->createEvent();
    $repository->onPush(static function() use ($event, $response, $config){
        $repositoryName = $event->getRepository()->getName();
        if(!isset($config[$repositoryName])){
            return null;
        }
        $repositoryConfig = $config[$repositoryName];
        $workdir = $repositoryConfig['workdir'];
        $branchName = $event->getBranchName();
        if(isset($repositoryConfig['command']) && !empty($repositoryConfig['command']) && is_array($repositoryConfig['command'])){
            foreach($repositoryConfig['command'] as $command){
                if((!isset($command['branch']) || empty($command['branch'])) || (isset($command['branch']) &&
                        $command['branch'] === $branchName)){
                    if((isset($command['event']) && !empty($command['event'])) && (isset($command['exec']) && !empty
                            ($command['exec']))){
                        if(is_array($command['exec'])){
                            foreach($command['exec'] as $exec){
                                if(strpos($exec, '{workdir}') !== false){
                                    $exec = str_replace('{workdir}', $workdir, $exec);
                                }
                                if(strpos($exec, '{branch}') !== false){
                                    $exec = str_replace('{branch}', $branchName, $exec);
                                }
                                exec($exec, $outputArr, $returnArr);
                                if(isset($outputArr, $returnArr)){
                                    unset($outputArr, $returnArr);
                                }
                            }
                        }
                    }
                }
            }
        }
        $response->setContent('success');
    });
}catch(Exception|Error $e){
    $response->setStatusCode($e->getCode())->setContent($e->getMessage());
}finally{
    $response->send();
}
