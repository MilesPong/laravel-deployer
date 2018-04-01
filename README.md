# Deployer for Indigo

**Important**, this `indigo` branch is **only** for project [indigo](https://github.com/MilesPong/indigo) usage, otherwise please checkout [`master`](https://github.com/MilesPong/laravel-deployer) branch.

## Illustration

Because deployer is a part of project indigo, which means deployer service should come after indigo, but we also need deployer service to deploy indigo source. Above words seems a little bit contradictory, those **two services depend on each other**, especially the very first time.

But we still could figure out a workaround by some additional operations, which is usually required just at the beginning of the deployment.

The basic idea is that we **manually** deploy indigo to the remote server (from local machine or remote host), and make sure we can access it through web URL, such as `http://project-indigo.com`, then set up **webhook** (by default is `http://project-indigo.com/webhook`) in Github. So that every time we make a **release**, Github will **trigger** the webhook which results in an auto-deployment by deployer, and no more manual intervention.

**In a nutshell, we just need to manually deploy parent project at the first time.**

something like this:

```
# Assume on the local machine
$ dep deploy remote_host
```

*P.S. usually command above will fail at the first time, because we are missing the configuration for the project, especially in laravel's migration. So take a look some tips from [this section](#fyi).*

Then on the remote host, we have a structure like this:

```
$ ls -la /path/to/deploy

lrwxrwxrwx  1 miles    miles      49 3月  28 11:57 current -> /path/to/deploy/releases/1
drwxrwxr-x  2 miles    miles    4.0K 3月  29 17:46 .dep
drwxrwxr-x  8 miles    miles    4.0K 3月  29 17:46 releases
drwxrwxr-x  4 miles    miles    4.0K 3月  29 17:46 shared
```

Finally set up **root path** to `/path/to/deploy/current/public` in **nginx**.

## FYI

At the first time, we have to set up the **proper permissions** and **shared files** for the deployer. Assume we have a deploy_path `/path/to/deploy`, then **manually** create the shared directories and files structure as follow:

```
$ cd /path/to/deploy
# Create (or copy) and set up indigo's configuration file
$ mkdir shared && touch shared/.env
# Create (or copy) and set up indigo's submodule configuration file
$ mkdir shared/deployer && touch shared/deployer/.env && touch shared/deployer/hosts.yml
```

Give the whole deploy path with right permissions, so that **HTTP user** (`www` or `www-data` based on your web server) can have a full read and write permissions.

```
$ sudo chown -R www-data:www-data /path/to/deploy
$ sudo setfacl -LR -m u:`whoami`:rwX /path/to/deploy
$ sudo setfacl -R -d -m u:`whoami`:rwX /path/to/deploy
```