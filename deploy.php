<?php
namespace Deployer;

use Symfony\Component\Console\Input\InputOption;

require 'recipe/laravel.php';
require 'recipe/npm.php';
require 'recipe/slack.php';

// Project name
// https://github.com/MilesPong/indigo.git
set('application', 'indigo');

// [Optional] Allocate tty for git clone. Default value is false.
// set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', [
    'deployer/.env',
    'deployer/hosts.yml',
]);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Dynamic run npm scripts
option('npm-env', null, InputOption::VALUE_OPTIONAL, 'NPM run environment.', 'dev');
set('npm_env', function () {
    return input()->getOption('npm-env') ?: 'dev';
});

// Hosts
inventory('hosts.yml');

// Tasks
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

// Load env file
task('load:dotenv', function () {
    (new \Symfony\Component\Dotenv\Dotenv())->load('.env');
    array_map(function ($var) {
        set($var, getenv($var));
    }, explode(',', $_SERVER['SYMFONY_DOTENV_VARS']));
})->setPrivate();

desc('Installing submodule vendors');
task('deploy:submodule:vendors', function () {
    if (!commandExist('unzip')) {
        writeln('<comment>To speed up composer installation setup "unzip" command with PHP zip extension https://goo.gl/sxzFcD</comment>');
    }
    run('cd {{release_path}}/deployer && {{bin/composer}} {{composer_options}}');
});

desc('Reload PHP-FPM service');
task('php-fpm:reload', function () {
    // The user must have rights for reload service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl reload php-fpm.service
    run('sudo systemctl reload php7.2-fpm.service');
});

// Main task
desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:submodule:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:optimize',
    'npm:install',
    'npm:build',
    'artisan:migrate',
    'deploy:symlink',
    'php-fpm:reload',
    'deploy:unlock',
    'cleanup',
]);

// It seems that when overrode the "deploy" task, those defined "before" and "after" related tasks
// in laravel.php are gone, have to redefine it again.
after('deploy', 'success');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Webhook
before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');

// This should be placed at last, and will be called at first
before('deploy', 'load:dotenv');