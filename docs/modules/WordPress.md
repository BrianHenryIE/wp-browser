# WordPress module
**This module requires good knowledge and attention to be used effectively; you can replace it with a combination of the [WPBrowser](WPBrowser.md) module together with the [WPLoader module in loadOnly mode](WPLoader.md#wploader-to-only-bootstrap-wordpress).**
This module should be used in functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module provides a middle-ground, in terms of testing and effects, between the fully isolated approach of the [WPBrowser](WPBrowser.md) module and the fully integrated approach of the [WPLoader module with loadOnly set to `false`](WPLoader.md).  
It allows to interact with WordPress on a very high level, using methods like `$I->loginAs()` or `$I->amOnPage()` as you could do with the `WPBrowser` module while also loading WordPress in the same variable scope as the tests as the `WPLoader` module would.
Due to WordPress reliance on constants, globals and side-effects this module will make requests to WordPress in an insualted manner and reproduce WordPress environment (globals and super-globals) after each response in the tests variable scope.
The module simulates a user interaction with the site **without Javascript support**, use the [WPWebDriver module](WPWebDriver.md) for any kind of testing that requires Javascript-based interaction with the site.

## Module requirements for Codeception 4.0+

This module requires the `codeception/lib-innerbrowser` Composer package to work when wp-browser is used with Codeception 4.0.  

To install the package run: 

```bash
composer require --dev codeception/lib-innerbrowser:^1.0
```

## Detecting requests coming from this module 
When it runs this module will set the `WPBROWSER_HOST_REQUEST` environment variable.  
You can detect and use that information to, as an example, use the correct database in your test site `wp-config.php` file:
```php
<?php
if ( 
    // Custom header.
    isset( $_SERVER['HTTP_X_TESTING'] )
    // Custom user agent.
    || ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] === 'wp-browser' )
    // The env var set by the WPClIr or WordPress modules.
    || getenv( 'WPBROWSER_HOST_REQUEST' )
) {
    // Use the test database if the request comes from a test.
    define( 'DB_NAME', 'wordpress_test' );
} else {
    // Else use the default one.
    define( 'DB_NAME', 'wordpress' );
}
```

## Configuration
* `wpRootFolder` *required* The absolute, or relative to the project root folder, path to the root WordPress installation folder. The WordPress installation root folder is the one that contains the `wp-load.php` file.
* `adminUsername` *required* - This is the login name, not the "nice" name, of the administrator user of the WordPress test site. This will be used to fill the username field in WordPress login page.  
* `adminPassword` *required* - This is the the password of the administrator use of the WordPress test site. This will be used to fill the password in WordPress login page.  
* `adminPath` *required* - The path, relative to the WordPress test site home URL, to the administration area, usually `/wp-admin`.

### Example configuration
```yaml
  modules:
      enabled:
          - WordPress
      config:
          WordPress:
              wpRootFolder: "/var/www/wordpress"
              adminUsername: 'admin'
              adminPassword: 'password'
              adminPath: '/wp-admin'
```
<!--doc-->

Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1078    4567112  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1089    4579768  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1090    4580672  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1090    4580672  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1105    4583952  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1113    4586816  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1113    4587720  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1113    4587720  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1140    4588832  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1148    4592848  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1149    4593872  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1149    4593872  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1140    4588832  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1148    4592848  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1155    4594936  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1156    4594936  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1163    4591792  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1171    4594952  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1171    4595984  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1171    4595984  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1163    4591792  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1171    4594952  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1178    4597056  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1178    4597056  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1386    4617320  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1393    4620248  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1394    4621160  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1394    4621160  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1421    4622720  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1427    4625488  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1427    4626400  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1428    4626400  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1435    4624640  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1441    4627408  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1442    4628320  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1442    4628320  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1448    4626472  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1454    4629208  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1455    4630120  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1455    4630120  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1461    4628272  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1468    4631040  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1468    4631952  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1468    4631952  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1474    4630104  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1482    4633032  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1482    4633944  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1482    4633944  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1495    4633968  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1502    4636848  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1502    4637760  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1502    4637760  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1507    4635944  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1514    4638928  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1514    4639832  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1515    4639832  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1521    4638608  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1527    4641432  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1527    4642344  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1528    4642344  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1534    4640584  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1541    4643344  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1541    4644256  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1542    4644256  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252


Deprecated: Function ReflectionParameter::export() is deprecated in /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php on line 344

Call Stack:
    0.0117     404600   1. {main}() /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:0
    0.0585    2236352   2. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/docs/bin/wpbdocmd:12
    0.0667    2842184   3. PHPDocsMD\Console\CLI->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/CLI.php:29
    0.0845    3094184   4. PHPDocsMD\Console\CLI->doRun(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:166
    0.0864    3107856   5. PHPDocsMD\Console\CLI->doRunCommand(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:290
    0.0865    3107856   6. PHPDocsMD\Console\PHPDocsMDCommand->run(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Application.php:971
    0.0876    3110488   7. PHPDocsMD\Console\PHPDocsMDCommand->execute(???, ???) /Users/lucatume/Repos/wp-browser/vendor/symfony/console/Command/Command.php:255
    0.1021    4352464   8. PHPDocsMD\Console\PHPDocsMDCommand->getClassEntity(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:169
    0.1052    4527056   9. PHPDocsMD\Reflector->getClassEntity() /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Console/PHPDocsMDCommand.php:57
    0.1066    4547880  10. PHPDocsMD\Reflector->getClassFunctions(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:83
    0.1551    4642408  11. PHPDocsMD\Reflector->createFunctionEntity(???, ???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:118
    0.1560    4645104  12. PHPDocsMD\Reflector->getParams(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:161
    0.1560    4646008  13. PHPDocsMD\Reflector->createParameterEntity(???, ???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:438
    0.1560    4646008  14. PHPDocsMD\Reflector::getParamType(???) /Users/lucatume/Repos/wp-browser/vendor/victorjonsson/markdowndocs/src/PHPDocsMD/Reflector.php:252



## Public API
<nav>
	<ul>
		<li>
			<a href="#ameditingpostwithid">amEditingPostWithId</a>
		</li>
		<li>
			<a href="#amonadminajaxpage">amOnAdminAjaxPage</a>
		</li>
		<li>
			<a href="#amonadminpage">amOnAdminPage</a>
		</li>
		<li>
			<a href="#amoncronpage">amOnCronPage</a>
		</li>
		<li>
			<a href="#amonpage">amOnPage</a>
		</li>
		<li>
			<a href="#amonpagespage">amOnPagesPage</a>
		</li>
		<li>
			<a href="#amonpluginspage">amOnPluginsPage</a>
		</li>
		<li>
			<a href="#dontseeplugininstalled">dontSeePluginInstalled</a>
		</li>
		<li>
			<a href="#extractcookie">extractCookie</a>
		</li>
		<li>
			<a href="#getresponsecontent">getResponseContent</a>
		</li>
		<li>
			<a href="#getwprootfolder">getWpRootFolder</a>
		</li>
		<li>
			<a href="#grabwordpresstestcookie">grabWordPressTestCookie</a>
		</li>
		<li>
			<a href="#logout">logOut</a>
		</li>
		<li>
			<a href="#loginas">loginAs</a>
		</li>
		<li>
			<a href="#loginasadmin">loginAsAdmin</a>
		</li>
		<li>
			<a href="#seeerrormessage">seeErrorMessage</a>
		</li>
		<li>
			<a href="#seemessage">seeMessage</a>
		</li>
		<li>
			<a href="#seepluginactivated">seePluginActivated</a>
		</li>
		<li>
			<a href="#seeplugindeactivated">seePluginDeactivated</a>
		</li>
		<li>
			<a href="#seeplugininstalled">seePluginInstalled</a>
		</li>
		<li>
			<a href="#seewpdiepage">seeWpDiePage</a>
		</li>
	</ul>
</nav>

<h3>amEditingPostWithId</h3>

<hr>

<p>Go to the admin page to edit the post with the specified ID. The method will <strong>not</strong> handle authentication the admin area.</p>
```php
$I->loginAsAdmin();
  $postId = $I->havePostInDatabase();
  $I->amEditingPostWithId($postId);
  $I->fillField('post_title', 'Post title');
```

<h4>Parameters</h4>
<ul>
<li><code>int</code> <strong>$id</strong> - The post ID.</li></ul>
  

<h3>amOnAdminAjaxPage</h3>

<hr>

<p>Go to the <code>admin-ajax.php</code> page to start a synchronous, and blocking, <code>GET</code> AJAX request. The method will <strong>not</strong> handle authentication, nonces or authorization.</p>
```php
$I->amOnAdminAjaxPage(['action' => 'my-action', 'data' => ['id' => 23], 'nonce' => $nonce]);
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string,mixed></code> <strong>$queryVars</strong> - A string or array of query variables to append to the AJAX path.</li></ul>
  

<h3>amOnAdminPage</h3>

<hr>

<p>Go to a page in the admininstration area of the site.</p>
```php
$I->loginAs('user', 'password');
  // Go to the plugins management screen.
  $I->amOnAdminPage('/plugins.php');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$page</strong> - The path, relative to the admin area URL, to the page.</li></ul>
  

<h3>amOnCronPage</h3>

<hr>

<p>Go to the cron page to start a synchronous, and blocking, <code>GET</code> request to the cron script.</p>
```php
// Triggers the cron job with an optional query argument.
  $I->amOnCronPage('/?some-query-var=some-value');
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string,mixed></code> <strong>$queryVars</strong> - A string or array of query variables to append to the AJAX path.</li></ul>
  

<h3>amOnPage</h3>

<hr>

<p>Go to a page on the site. The module will try to reach the page, relative to the URL specified in the module configuration, without applying any permalink resolution.</p>
```php
// Go the the homepage.
  $I->amOnPage('/');
  // Go to the single page of post with ID 23.
  $I->amOnPage('/?p=23');
  // Go to search page for the string "foo".
  $I->amOnPage('/?s=foo');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$page</strong> - The path to the page, relative to the the root URL.</li></ul>
  

<h3>amOnPagesPage</h3>

<hr>

<p>Go the &quot;Pages&quot; administration screen. The method will <strong>not</strong> handle authentication.</p>
```php
$I->loginAsAdmin();
  $I->amOnPagesPage();
  $I->see('Add New');
```

  

<h3>amOnPluginsPage</h3>

<hr>

<p>Go to the plugins administration screen. The method will <strong>not</strong> handle authentication.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->activatePlugin('hello-dolly');
```

  

<h3>dontSeePluginInstalled</h3>

<hr>

<p>Assert a plugin is not installed in the plugins administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->dontSeePluginInstalled('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>extractCookie</h3>

<hr>

<p>Grab a cookie value from the current session, sets it in the $_COOKIE array and returns its value. This method utility is to get, in the scope of test code, the value of a cookie set during the tests.</p>
```php
$id = $I->haveUserInDatabase('user', 'subscriber', ['user_pass' => 'pass']);
  $I->loginAs('user', 'pass');
  // The cookie is now set in the `$_COOKIE` super-global.
  $I->extractCookie(LOGGED_IN_COOKIE);
  // Generate a nonce using WordPress methods (see WPLoader in loadOnly mode) with correctly set context.
  wp_set_current_user($id);
  $nonce = wp_create_nonce('wp_rest');
  // Use the generated nonce to make a request to the the REST API.
  $I->haveHttpHeader('X-WP-Nonce', $nonce);
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$cookie</strong> - The cookie name.</li>
<li><code>array/\Codeception\Module\array<string,mixed>/array</code> <strong>$params</strong> - Parameters to filter the cookie value.</li></ul>
  

<h3>getResponseContent</h3>

<hr>

<p>Returns content of the last response. This method exposes an underlying API for custom assertions.</p>
```php
// In test class.
  $this->assertContains($text, $this->getResponseContent(), "foo-bar");
```

  

<h3>getWpRootFolder</h3>

<hr>

<p>Returns the absolute path to the WordPress root folder.</p>
```php
$root = $I->getWpRootFolder();
  $this->assertFileExists($root . '/someFile.txt');
```

  

<h3>grabWordPressTestCookie</h3>

<hr>

<p>Returns WordPress default test cookie object if present.</p>
```php
// Grab the default WordPress test cookie.
  $wpTestCookie = $I->grabWordPressTestCookie();
  // Grab a customized version of the test cookie.
  $myTestCookie = $I->grabWordPressTestCookie('my_test_cookie');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$name</strong> - Optional, overrides the default cookie name.</li></ul>
  

<h3>logOut</h3>

<hr>

<p>Navigate to the default WordPress logout page and click the logout link.</p>
```php
// Log out using the `wp-login.php` form and return to the current page.
  $I->logOut(true);
  // Log out using the `wp-login.php` form and remain there.
  $I->logOut(false);
  // Log out using the `wp-login.php` form and move to another page.
  $I->logOut('/some-other-page');
```

<h4>Parameters</h4>
<ul>
<li><code>bool/bool/string</code> <strong>$redirectTo</strong> - Whether to redirect to another (optionally specified) page after the logout.</li></ul>
  

<h3>loginAs</h3>

<hr>

<p>Login as the specified user. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
```php
$I->loginAs('user', 'password');
  $I->amOnAdminPage('/');
  $I->seeElement('.admin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$username</strong> - The user login name.</li>
<li><code>string</code> <strong>$password</strong> - The user password in plain text.</li></ul>
  

<h3>loginAsAdmin</h3>

<hr>

<p>Login as the administrator user using the credentials specified in the module configuration. The method will <strong>not</strong> follow redirection, after the login, to any page.</p>
```php
$I->loginAsAdmin();
  $I->amOnAdminPage('/');
  $I->see('Dashboard');
```

  

<h3>seeErrorMessage</h3>

<hr>

<p>In an administration screen look for an error admin notice. The check is class-based to decouple from internationalization. The method will <strong>not</strong> handle authentication and navigation the administration area. <code>.notice.notice-error</code> ones.</p>
```php
$I->loginAsAdmin()
  $I->amOnAdminPage('/');
  $I->seeErrorMessage('.my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string/string/\Codeception\Module\array<string></code> <strong>$classes</strong> - A list of classes the notice should have other than the</li></ul>
  

<h3>seeMessage</h3>

<hr>

<p>In an administration screen look for an admin notice. The check is class-based to decouple from internationalization. The method will <strong>not</strong> handle authentication and navigation the administration area.</p>
```php
$I->loginAsAdmin()
  $I->amOnAdminPage('/');
  $I->seeMessage('.missing-api-token.my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string/\Codeception\Module\array<string>/string</code> <strong>$classes</strong> - A list of classes the message should have in addition to the <code>.notice</code> one.</li></ul>
  

<h3>seePluginActivated</h3>

<hr>

<p>Assert a plugin is activated in the plugin administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->seePluginActivated('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seePluginDeactivated</h3>

<hr>

<p>Assert a plugin is not activated in the plugins administration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->seePluginDeactivated('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seePluginInstalled</h3>

<hr>

<p>Assert a plugin is installed, no matter its activation status, in the plugin adminstration screen. The method will <strong>not</strong> handle authentication and navigation to the plugin administration screen.</p>
```php
$I->loginAsAdmin();
  $I->amOnPluginsPage();
  $I->seePluginInstalled('my-plugin');
```

<h4>Parameters</h4>
<ul>
<li><code>string</code> <strong>$pluginSlug</strong> - The plugin slug, like &quot;hello-dolly&quot;.</li></ul>
  

<h3>seeWpDiePage</h3>

<hr>

<p>Checks that the current page is one generated by the <code>wp_die</code> function. The method will try to identify the page based on the default WordPress die page HTML attributes.</p>
```php
$I->loginAs('user', 'password');
  $I->amOnAdminPage('/forbidden');
  $I->seeWpDiePage();
```


*This class extends \Codeception\Lib\Framework*

*This class implements \Codeception\Lib\Interfaces\ConflictsWithModule, \Codeception\Lib\Interfaces\ElementLocator, \Codeception\Lib\Interfaces\PageSourceSaver, \Codeception\Lib\Interfaces\Web, \Codeception\Lib\Interfaces\DependsOnModule*

<!--/doc-->
