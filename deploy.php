<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/npm.php';
require 'recipe/slack.php';

// Project name
set('application', 'indigo');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Load env file
task('load:dotenv', function () {
    (new \Symfony\Component\Dotenv\Dotenv())->load('.env');
    array_map(function ($var) {
        set($var, getenv($var));
    }, explode(',', $_SERVER['SYMFONY_DOTENV_VARS']));
});

// Hosts
inventory('hosts.yml');

/**
 * Main task
 */
desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:optimize',
    'npm:install',
    'artisan:migrate',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Dynamic run npm scripts
use Symfony\Component\Console\Input\InputOption;
option('npm-env', null, InputOption::VALUE_OPTIONAL, 'NPM run environment.', 'dev');
set('npm_env', function () {
    return input()->getOption('npm-env') ?: 'dev';
});

// NPM run scripts command.
desc('Command: npm run dev');
task('npm:run:dev', function () {
    run('cd {{release_path}} && {{bin/npm}} run dev');
});
desc('Command: npm run admin-dev');
task('npm:run:admin-dev', function () {
    run('cd {{release_path}} && {{bin/npm}} run admin-dev');
});
desc('Command: npm run prod');
task('npm:run:prod', function () {
    run('cd {{release_path}} && {{bin/npm}} run prod');
});
desc('Command: npm run admin-prod');
task('npm:run:admin-prod', function () {
    run('cd {{release_path}} && {{bin/npm}} run admin-prod');
});
desc('Build development resources in npm');
task('npm:build:dev', function () {
    invoke('npm:run:dev');
    invoke('npm:run:admin-dev');
});
desc('Build production resources in npm');
task('npm:build:prod', function () {
    invoke('npm:run:prod');
    invoke('npm:run:admin-prod');
});

desc('Build npm resources');
task('npm:build', function () {
    $npmEnv = get('npm_env');

    if ($npmEnv === 'dev') {
       invoke('npm:build:dev');
    } else if ($npmEnv === 'prod') {
       invoke('npm:build:prod');
    }
});
after('npm:install', 'npm:build');

// Webhook
before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');

// This should be placed at last, and will be called at first
before('deploy', 'load:dotenv');