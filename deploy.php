<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/npm.php';

// Project name
set('application', 'indigo');

set('default_stage', 'staging');
set('branch', 'develop');

// Project repository
// set('repository', 'git@github.com:MilesPong/indigo.git');
set('repository', '/home/miles/docker-vm-share/git-repo/indigo.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

localhost()
    ->stage('staging')
    ->set('deploy_path', '~/docker-vm-share/www/{{application}}');    
    
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

// After npm install.
after('npm:install', 'npm:run:dev');
after('npm:install', 'npm:run:admin-dev');
// after('npm:install', 'npm:run:prod');
// after('npm:install', 'npm:run:admin-prod');

// NPM tasks.
desc('NPM run scripts');
task('npm:run:prod', function () {
    run('cd {{release_path}} && {{bin/npm}} run prod');
});
task('npm:run:admin-prod', function () {
    run('cd {{release_path}} && {{bin/npm}} run admin-prod');
});
task('npm:run:dev', function () {
    run('cd {{release_path}} && {{bin/npm}} run dev');
});
task('npm:run:admin-dev', function () {
    run('cd {{release_path}} && {{bin/npm}} run admin-dev');
});