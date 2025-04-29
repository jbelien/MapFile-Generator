<?php

namespace Deployer;

require 'recipe/zend_framework.php';

// Config

set('repository', 'https://github.com/jbelien/MapFile-Generator');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('ob5cds.ftp.infomaniak.com')
    ->set('remote_user', 'ob5cds_system')
    ->set('deploy_path', '~/sites/mapfile.akoo.be');

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

after('deploy:update_code', 'npm:rsync');
after('deploy:failed', 'deploy:unlock');
