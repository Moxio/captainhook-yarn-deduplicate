[![Latest Stable Version](https://poser.pugx.org/moxio/captainhook-yarn-deduplicate/v/stable)](https://packagist.org/packages/moxio/captainhook-yarn-deduplicate)

moxio/captainhook-yarn-deduplicate
==================================
This project is a plugin for [CaptainHook](https://github.com/captainhookphp/captainhook) to check your `yarn.lock` file
for duplicate packages using [yarn-deduplicate](https://github.com/atlassian/yarn-deduplicate). The commit is blocked
when one or more duplicate packages are found. You can then fix these manually by running `yarn-deduplicate`.

Installation
------------
Install as a development dependency using composer:
```
$ composer require --dev moxio/captainhook-yarn-deduplicate
```

Usage
-----
Add yarn-deduplicate validation as a `pre-commit` to your `captainhook.json` configuration file:
```json
{
    "pre-commit": {
        "enabled": true,
        "actions": [
            {
                "action": "\\Moxio\\CaptainHook\\YarnDeduplicate\\YarnDuplicationCheckAction"
            }
        ]
    }
}
```

The action expects [yarn-deduplicate](https://github.com/atlassian/yarn-deduplicate) to be installed as a local NPM
package (i.e. available at `node_modules/.bin/yarn-deduplicate`).

### Conditional usage
If you want to perform duplication checks only when yarn-deduplicate is installed (i.e. available at
`node_modules/.bin/yarn-deduplicate`), you can add a corresponding condition to the action:
```json
{
    "pre-commit": {
        "enabled": true,
        "actions": [
            {
                "action": "\\Moxio\\CaptainHook\\YarnDeduplicate\\YarnDuplicationCheckAction",
                "conditions": [
                    {
                        "exec": "\\Moxio\\CaptainHook\\YarnDeduplicate\\Condition\\YarnDeduplicateInstalled"
                    }
                ]
            }
        ]
    }
}
```
This may be useful in scenarios where you have a shared CaptainHook configuration file that is
[included](https://captainhookphp.github.io/captainhook/configure.html#includes) both in projects that use
yarn-deduplicate and projects that don't. If yarn-deduplicate is installed, the action is run. In projects without
yarn-deduplicate, the validation is skipped.

Versioning
----------
This project adheres to [Semantic Versioning](http://semver.org/).

Contributing
------------
Contributions to this project are welcome. Please make sure that your code follows the
[PSR-12](https://www.php-fig.org/psr/psr-12/) extended coding style.

License
-------
This project is released under the MIT license.
