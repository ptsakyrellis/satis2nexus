# Satis2Nexus

*Satis2Nexus* est un fork du projet [Satis](https://github.com/composer/satis) qui permet d'automatiser le déploiement des packages composer développés localement sur une instance [Nexus](<https://github.com/sonatype/nexus-public>) sur laquelle est installée le [plugin](<https://github.com/sonatype-nexus-community/nexus-repository-composer>) permettant d'héberger des dépôts composer. 

Ce plugin Nexus est actuellement en beta et à moitié abandonné, le contributeur principal ayant déclaré qu'il ne s'y consacrerait plus. Le problème est qu'il n'existe pas actuellement d'outil permettant d'automatiquement permettre de publier des librairies locales dans ce dépôt composer Nexus. 

La seule fonctionnalité automatisée est le miroir (ou proxy) des librairies publiques hébergées par [packagist.org](https://packagist.org). Et ceci parce que Nexus et son plugin n'ont été codés que pour comprendre un seul type de fichiers "packages.json", celui fourni par packagist (et les dépots payant hébergés sur [packagist.com](https://packagist.com)). 

Le fichier json fourni par les dépôts Satis en particulier n'est pas reconnu. 

Nous avons donc décidé de forker le projet Satis pour envoyer les zip des différentes librairies directement sur Nexus. 

## Utilisation

### Installation

Sur le serveur qui devra réaliser l'envoi des librairies vers le serveur Nexus, commencez par cloner le dépôt de notre projet *satis2nexus*.

```
$ git clone git@gitlabssh.tyforge.in.ac-rennes.fr:ac-toulouse/ressources-support/gestion-si/satis2nexus.git
```

### Mise à jour

Sur le serveur qui devra réaliser l'envoi des librairies vers le serveur Nexus, pullez le dépôt *satis2nexus*.

```
$ git pull origin master
```

Il est fortement recommandé de supprimer le dossier de build, sans cela, vous risquez de rencontrer des problèmes de mise à jour des dépôts.

### Configuration

La configuration se fait en éditant le fichier `satis.json` à la racine du projet. Un fichier d'exemple vous est proposé avec le fichier `satis.json.dist`. 

Le fichier `satis.json` vous permet de définir les adresses des différents dépôts des projets que vous voulez rendre disponibles via votre dépôt composer. Pour cela, vous pouvez suivre la [documentation du projet Satis](<https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#setup>).

Nous avons supprimé l'attribut `"homepage"`. 
Les éléments de configuration ajoutés concernant votre dépôt Nexus sont les suivants : `

```
{
 "nexus": "http://nexus3.tyforge.in.ac-rennes.fr",
 "nexus-repository": "composer-TO_REPLACE",
 "nexus-user": "TO_REPLACE",
 "nexus-password": "TO_REPLACE"
}
```

Le but étant de pouvoir créer un package pour chaque tag présent sur les dépôts Git.

### Commandes

Cette application est une application [symfony console](https://symfony.com/doc/current/components/console.html), proposant deux commandes, build et purge. L'idéal pour utiliser cette application est de mettre en place une tâche planifiée (cron) qui va régulièrement builder et purger le dépôt Nexus afin d'avoir la disponibilité des librairies au plus proche de la réalité. 

#### Pré-requis

Les commandes vont effectuer des opérations en utilisant la commande git, il faut donc que le binaire git soit présent sur le serveur d'installation et que les URL des dépôts correspondent à des sources auquel l'utilisateur qui lance les commandes a accès (voir à ce propos la [documentation du projet Satis](<https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#authentication>)).

#### Build

Envoi les nouveaux packages sur Nexus (et les remplace si modification).

##### Syntaxe

```
$ php bin/satis build --no-interaction \<configuration file> \<build dir>
```

- Paramètres
  - **configuration file** : le fichier de paramètre satis.json
  - **build dir** : le dossier où seront créées les archives et le fichier `packages.json`

##### Opérations effectuées

- Clone / pull du code GIT pour créer une archive zip par tag de version
- Chaque archive a un nom différent (vendor, bundle, version, id du commit ...). 
- Les archives déjà créées précédemment ne sont pas recréés à chaque build (on se base sur le nom de l'archive).
- Les archives des versions qui ne sont pas déjà présentes sur le dépôt Nexus sont envoyées.
- Si un tag est modifié, cela créé une nouvelle archive (en plus de l'ancienne) et remplace la version sur le dépôt Nexus.
- Les anciennes archives qui ne sont plus utilisées seront supprimées via la commande `purge`.
- Cette commande va générer un fichier `packages.json` qui va être utilisé par la commande `purge`.

#### Purge

Supprime les packages de Nexus qui n'existent pas dans les dépôts GIT.

##### Syntaxe

```
$ php bin/satis purge <configuration file> <build dir>
```

- Paramètres
  - **configuration file** : le fichier de paramètre satis.json
  - **build dir** : le dossier où sont créées les archives et le 'package.json'

##### Opérations effectuées

- Attention : La commande purge ne fait pas de fetch ou de pull, elle se base sur l'état du GIT en fonction du dernier build (`packages.json`) ! Il est donc fortement recommandé de lancer cette commande après un Build.
- Les archives présentes sur Nexus qui n’apparaissent pas dans les tags GIT seront supprimées.