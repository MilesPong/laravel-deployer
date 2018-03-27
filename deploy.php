<?php
namespace Deployer;

require 'recipe/laravel.php';

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
    'artisan:migrate',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');