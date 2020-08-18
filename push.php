<?php
declare(strict_types=1);
/**
 * @author Persi.Liao
 * @email xiangchu.liao@gmail.com
 * @link https://www.github.com/persiliao
 */

require __DIR__ . '/vendor/autoload.php';

use PersiLiao\GitWebhooks\Provider\GiteaProvider;
use PersiLiao\GitWebhooks\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

try{
    if(file_exists(__DIR__ . '/config.php') === false){
        throw new RuntimeException('config.php file is missing');
    }
    $config = require __DIR__ . '/config.php';
    if(!isset($config) || !is_array($config) || empty($config)){
        throw new RuntimeException('Please check the config.php file configuration');
    }
    $secrets = [];
    foreach($config as $name => $conf){
        if(empty($name) || !is_string($name)){
            throw new RuntimeException('Please check the config.php file name configuration');
        }
        if(!isset($conf['secret'], $conf['workdir'])){
            throw new RuntimeException('Please check the config.php file secret, workdir configuration');
        }
        if(!is_dir($conf['workdir']) || !is_writable($conf['workdir'])){
            throw new RuntimeException('Please check the config.php file WorkerDirectory configuration');
        }
        if(isset($conf['command']) && !empty($conf['command']) && is_array($conf['command'])){
            foreach($conf['command'] as $command){
                if(!isset($command['event'], $command['exec'])){
                    throw new RuntimeException('Please check the config.php file command configuration');
                }
            }
        }
    }
    $response = new Response();
    $request = Request::createFromGlobals();

    $repository = new Repository([
        new GiteaProvider($request)
    ], $secrets);
    $event = $repository->createEvent();
    $repository->onPush(static function() use ($event, $response, $config){
        $defaultEvent = 'push';
        $defaultPullCommand = 'cd %s && git pull origin %s 2>/dev/null';
        $repositoryName = $event->getRepository()->getName();
        $repositoryConfig = $config[$repositoryName] ?? [];
        $workdir = $repositoryConfig['workdir'];
        $branchName = $event->getBranchName();
        exec(sprintf($defaultPullCommand, $workdir, $branchName), $outputArr, $returnArr);
        if(isset($outputArr, $returnArr)){
            unset($outputArr, $returnArr);
        }
        if(isset($repositoryConfig['command']) && !empty($repositoryConfig['command']) && is_array($repositoryConfig['command'])){
            foreach($repositoryConfig['command'] as $command){
                if((!isset($command['branch']) || empty($command['branch'])) || (isset($command['branch']) &&
                        $command['branch'] === $branchName)){
                    if((isset($command['event']) && !empty($command['event']) && $command['event'] === $defaultEvent) && (isset($command['exec']) && !empty
                            ($command['exec']))){
                        $exec = $command['exec'];
                        if(strpos($exec, '{workdir}')){
                            $exec = str_replace('{workdir}', $workdir, $exec);
                        }
                        if(strpos($exec, '{branch}')){
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
        $response->setContent('git pull success');
    });
}catch(Exception|Error $e){
    $response->setStatusCode($e->getCode())->setContent($e->getMessage());
}finally{
    $response->send();
}