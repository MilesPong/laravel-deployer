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
task('npm:run', function () {
    cd('{{release_path}}');
    $npmEnv = get('npm_env');

    if ($npmEnv === 'dev') {
       run('{{bin/npm}} run dev');
       run('{{bin/npm}} run admin-dev');
    } else if ($npmEnv === 'prod') {
       run('{{bin/npm}} run prod');
       run('{{bin/npm}} run admin-prod');
    }
});
after('npm:install', 'npm:run');

// Webhook
before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');

// This should be placed at last, and will be called at first
before('deploy', 'load:dotenv');