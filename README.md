zf2-for-1-example
=================

This repo provides some lazy examples of using Zf2for1. I should have spent more
than 30 minutes making examples but i am lazy ass, sorry.


Installation
------------

Clone this repo, check one of the example branches and run vagrant up.
Vagrant box will expose application at port 8085.


zf1app
------

This branch contains some random zf1 app i decided to use as example.


zf1app-with-zf2-modules
-----------------------

This branch is an example of using Zf2for1 to bootstrap ZF2 modules and using
service manager registered in `Zend_Registry` under `service_manager` key.

This is a starting point that will allow you to start migrating application
bootstrap to ZF2 modules.


zf1app-as-zf2-fallback
----------------------

This branch is an example of using Zf2for1 to run ZF1 application as fallback of
ZF2 app.

Bootstrap class is changed to extend `Zf2for1\Application\Zf2Bootstrap` which
provides overrides and runs ZF2 app instead of ZF1.
