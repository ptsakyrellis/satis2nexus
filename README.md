# Satis2Nexus

*Satis2Nexus* is a fork of the [Satis project](https://github.com/composer/satis) which automates the build and push of PHP private packages to a [Nexus Repository Manager](https://sonatype.com/nexus-repository-oss) instance where the [composer repository plugin](https://github.com/sonatype-nexus-community/nexus-repository-composer) is installed. 

The composer repository Nexus plugin is still in beta and only half done, the sole contributor has decided no to update it anymore.  Thus there is no simple way to upload private PHP packages automatically from source repositories to Nexus the same way Satis was doing it. 

The only automated feature of the plugin is the mirror / proxy of the public libraries hosted by [packagist.org](https://packagist.org). The main reason for that is that the plugin is only able to understand one type of "packages.json" file, and this is the one from packagist (Satis uses a slightly different schema). 

So we decided to fork the Satis project to include an additional step to push the generated zip files to a Nexus composer repository. 

## Usage

### Install

On the server which will be used to send the packages to the Nexus instance, clone the project repository : 

```
$ git clone git@github.com:ptsakyrellis/satis2nexus.git
```

### Update

On the server which will be used to send the packages to the Nexus instance, update the repository : *satis2nexus*.

```
$ git pull origin master
```

Please delete your packages build directory before updating to avoid any problems. 

### Configuration

The configuration is made by editing the  `satis.json`  file located at the root of the project.  `satis.json.dist` is an example to guide you. 

The  `satis.json`  file allow you to define the list of the different source code repositories of the projects you want to make available in your composer repository. For this step, you can read [the original Satis documentation](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#setup).

We have removed the `"homepage"` attribute and added the following elements :   

```
{
 "nexus": "http://nexus3.yourdomain.com",
 "nexus-repository": "composer-repository-name",
 "nexus-user": "user_nexus_login",
 "nexus-password": "user_nexus_password"
}
```

A release will be created for each of the tags found inside you Git repositories. 

### Commands

This application is a [symfony console application](https://symfony.com/doc/current/components/console.html), consisting of two tasks, **build** and **purge**. 

The best way to use the application is to use a cronjob which will be regularly building and purging the Nexus repository in order to mirror closely what is being released or deleted in your source repositories. 

#### Prerequisites

The application will be making operations using the underlying git command, so you should have the git binary installed on your server, and the user executing the application should have access to the repositories you'll be pushing to Nexus. You can read the [original Nexus documentation](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#authentication) for more about this. 

#### Build command

Sends new packages to Nexus (or replaces modified ones).

##### Usage syntax

```
$ php bin/satis build --no-interaction \<configuration file> \<build dir>
```

- Paramètres
  - **configuration file** : `satis.json` configuration file
  - **build dir** : directory where the `packages.json` file and the zip archives will be created

##### What is going to be done

- Clone each declared source code repository to create a zip archive for each version tag
- Each archive gets an unique name
- Archives previously created are not created again (based on archive name)
- Archives not present in the Nexus repository are sent
- If a tag is modified, a new archived is created and pushed to the Nexus repository to replace the old one 
- A `packages.json` file will be generated and used by the `purge` command

#### Purge Command

Deletes packages in the Nexus repository that no longer exist in the source code repositories.

##### Usage syntax

```
$ php bin/satis purge <configuration file> <build dir>
```

- Parameters
  - **configuration file** : satis.json configuration file
  - **build dir** : directory where the `packages.json` file and the zip archives will be created

##### What is going to be done

- Warning : The purge command is based solely on the content of the `packages.json` file, so you can run this command only after the build command has been run.
- Archives found in the Nexus repository but not anymore in the git repository (as a tag) will be deleted

# License
Satis2Nexus is licensed under the MIT License - see the LICENSE file for details
