# Deployer Example for Laravel

An example to show how to deploy a [laravel](https://laravel.com) project through [deployer](https://deployer.org).

## Preview

[![asciicast](https://asciinema.org/a/172938.png)](https://asciinema.org/a/172938)

## Requirements

 - deployer
 - laravel project

Also, don't forget to review the official deployer [documentation](https://deployer.org/docs) at first.

## Installation

Install deployer package through **global** installation of composer (**recommended**), so that you can access command `dep` everywhere, or you may use `vendor/bin/dep` instead.

```
$ composer global require deployer/deployer
```

Then clone the source:

```
$ git clone https://github.com/MilesPong/laravel-deployer
$ cd laravel-deployer
$ cp .env.example .env
$ cp hosts.yml.example hosts.yml
$ composer install
```

Modify `slack_webhook` in `.env` if you are going to use slack notification.

Modify the hosts configurations in `hosts.yml`, by default there are two example hosts, a **remote** host and a **local** host.

## Usage

Once you have set up the configurations, run command as follow:

```
$ dep deploy <stage_or_host>
````

This will deploy the code to your specific host(s) indicated by argument `stage_or_host`, mainly flow including:

- update code
- recreate application's cache
- build the static resources
- send slack notifications

You can take a look the whole flow in **main task** section in [`deploy.php`](deploy.php).

Or advanced usage:

```
$ dep deploy <stage_or_host> --branch=GIT_BRANCHE --npm-env=NPM-ENV
# e.g. dep deploy localhost --branch=develop --npm-env=prod
```

## Contributing

Contributions are welcome through pull request and issue.

## License

This project is released under [MIT license](https://opensource.org/licenses/MIT).
