<?php

namespace Deployer;

require 'recipe/zend_framework.php';
require 'contrib/php-fpm.php';

// Config

set('repository', 'https://github.com/jbelien/MapFile-Generator');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('mapfile.akoo.be')
    ->set('remote_user', 'root')
    ->set('deploy_path', '/var/www/mapfile');

// Tasks

task('npm:build', function () {
    runLocally('npm install');
    runLocally('npm run build');
});
task('npm:rsync', function () {
    runLocally('rsync -e ssh -az public/css/ {{remote_user}}@{{hostname}}:{{release_path}}/public/css/');
    runLocally('rsync -e ssh -az public/js/ {{remote_user}}@{{hostname}}:{{release_path}}/public/js/');
});
task('npm', ['npm:build', 'npm:rsync']);

// Hooks

after('deploy:update_code', 'npm');
after('deploy:failed', 'deploy:unlock');
after('deploy:success', 'php-fpm:reload');
