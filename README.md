The BFOSSyncContentBundle
=========================

This Symfony 2 bundle helps you synchronize your content with your remote server.

This bundle was inspired by sfSyncContentPlugin by Punk'Ave and MadalynnPlumBundle.


Installation
------------

You need to install de submodule on the deps file::

    // deps
    [BFOSSyncContentBundle]
        git=git://github.com/BrazilianFriendsOfSymfony/BFOSSyncContentBundle.git
        target=/bundles/BFOS/SyncContentBundle

And then::

    bash$ php bin/vendors install


Configuration
-------------

Add this to app/autoload.php::

    // app/autoload.php
    $loader->registerNamespaces(array(
      // ...
      'BFOS'              => __DIR__.'/../vendor/bundles',
      // ...
    ));

And this to app/AppKernel.php::

    // app/AppKernel.php
    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        $bundles[] = new BFOS\SyncContentBundle\BFOSSyncContentBundle();
    }

And this to your app/config/config_dev.yml

    bfos_sync_content:
        options: # Global options
            rsync_exclude: "%kernel.root_dir%/config/rsync_exclude.txt"
            content:
                - "web/uploads"
        servers: "%kernel.root_dir%/config/deployment_sync_content.yml"

Example of deployment_sync_content.yml :

    servers:
        staging:
            host: staging.mysite.com
            port: 22
            user: mysite
            dir: /home/user/mysite
            options : # Server options, override the globals
                rsync_options: '-azC --force --delete --verbose --progress'
        production:
            host: www.mysite.com
            port: 22
            user: mysite
            dir: /home/user/mysite
            options : # Server options, override the globals
                rsync_options: '-azC --force --delete --verbose --progress'

Example of rsync_exclude.txt :

    # Project files
    # rsync doesn't need this explicit rule, but our cloud deployment tools do
    */.svn/*
    /web/uploads/*
    /web/bundles/*
    /app/cache/*
    /app/logs/*
    /web/*_dev.php
    # Separate version on the server allows both prod and dev to be tested locally with the
    # local one. Neither version is checked into svn for security reasons since this is a public repo
    /app/config/parameters.yml
    /app/config/deployment_sync_content.yml
    /app/config/parameters.ini

    # SCM files
    .arch-params
    .bzr
    _darcs
    .git
    .hg
    .monotone
    .svn
    .idea
    CVS

Usage
-----

How to use:

php app/console bfos:sync-content:to dev@staging

php app/console bfos:sync-content:to prod@production


