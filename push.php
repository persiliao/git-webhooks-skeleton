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
use PersiLiao\Utils\DotEnv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


try{
    if(file_exists(__DIR__ . '/.env') === false){
        throw new RuntimeException('.env file is missing');
    }
    DotEnv::load(__DIR__);
    $name = getenv('NAME');
    $secret = getenv('SECRET');
    $workdir = getenv('WorkerDirectory');
    if(empty($name) || empty($secret) || empty($workdir)){
        throw new RuntimeException('Please check the .env file configuration');
    }
    if(!is_dir($workdir) || !is_writeable($workdir)){
        throw new RuntimeException('Please check the .env WorkerDirectory configuration');
    }
    $response = new Response();
    $request = Request::createFromGlobals();
    $secrets = [
        $name => $secret
    ];
    $repository = new Repository([
        new GiteaProvider($request)
    ], $secrets);
    $event = $repository->createEvent();
    $repository->onPush(function() use ($event, $workdir, $response){
        if($event->getBranchName() === 'master'){
            exec(sprintf('cd %s && git pull', $workdir));
        }
        $response->setContent('git pull success');
    });
}catch(Exception|Error $e){
    error_log($e->getMessage());
    $response->setStatusCode($e->getCode())->setContent($e->getMessage());
}finally{
    $response->send();
}
 