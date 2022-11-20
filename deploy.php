<?php
namespace Deployer;

require 'recipe/zend_framework.php';

// Config

set('repository', 'https://github.com/jbelien/MapFile-Generator');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('mapfile.akoo.be')
    ->set('remote_user', 'debian')
    ->set('deploy_path', '/var/www/mapfile');

// Hooks

after('deploy:failed', 'deploy:unlock');
