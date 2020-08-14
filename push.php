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
    $response = new Response();
    $request = Request::createFromGlobals();
    $secrets = [
        getenv('NAME') => getenv('SECRET')
    ];
    $repository = new Repository([
        new GiteaProvider($request)
    ], $secrets);
    $event = $repository->createEvent();
    $repository->onPush(function() use ($event, $response){
        if($event->getBranchName() === 'master'){
            exec('cd /path/to/your/project && git pull');
        }
        $response->setContent('git pull success');
    });
}catch(Exception|Error $e){
    error_log($e->getMessage());
    $response->setStatusCode($e->getCode())->setContent($e->getMessage());
}finally{
    $response->send();
}
 