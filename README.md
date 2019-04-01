# Satis2Nexus
Satis2Nexus est un fork de Satis, il permet d'automatiser le déploiement des packages 
composer développés localement sur une instance Nexus (nécessite le plugin composer).

Vous pouvez vous appuyer sur la documentation de Satis pour configurer votre satis.json, et vous baser sur le fichier "satis.json.dist" 
La seule différence majeure du "satis.json" est la suppression du "homepage" et l'ajout de "nexus", "nexus-repository", "nexus-user", "nexus-password"

Le but étant de pouvoir créer 1 package pour chaque tag sur Git.

Cette application est une application de Console, proposant 2 commandes :
* **Build** : Envoi les nouveaux packages sur Nexus (remplace si modification) :
    * **php bin/satis build --no-interaction \<configuration file> \<build dir>**
        * **configuration file** : le fichier de paramètre satis.json
        * **build dir** : le dossier où seront créées les archives et le 'package.json'
    * Cette commande réalise un clone / pull du code GIT pour créer une archive zip par tag de version
    * Chaque archive à un nom différent (nom vendor, bundle, version, id du commit ...). 
    * Les archives déjà créées précédemment ne sont pas recréés à chaque build (on se base sur le nom de l'archive).
    * Les archives des versions qui ne sont pas déjà présentes sur Nexus sont envoyées.
    * Si un tag est modifié, cela créé une nouvelle archive (en plus de l'ancienne) et remplace la version sur Nexus.
    * Les anciennes archives qui ne sont plus utilisées pourront être supprimées via la commande Purge.
    * Cette commande va générer un 'packages.json' qui va être utilisé par la commande Purge.
    
* **Purge** : Supprime les packages de Nexus qui n'existent pas dans le dépôt GIT :
    * **php bin/satis purge \<configuration file> \<build dir>**
            * **configuration file** : le fichier de paramètre satis.json
            * **build dir** : le dossier où sont créées les archives et le 'package.json'
    * Attention : Le Purge ne fait pas de clone ou de pull, il se base sur l'état du GIT en fonction du dernier Build (package.json)! Il est donc fortement recommandé de lancer cette commande après un Build.
    * Les archives présentes sur Nexus qui n'apparaît pas dans les tags git seront supprimées.
 
 L'idéale pour utiliser cette application est de mettre en place un cron de quelques minutes qui va pouvoir Build puis Purge.