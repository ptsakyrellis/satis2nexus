# Environnement de dev sur Docker

## Serveur NFS

Le serveur NFS permet l'accélération du transfert de fichier entre Windows et les containers docker.

Vous pouvez le lancer via le script docker/nfs/startNFS.bat

Pour désactiver les logs :
```
log off
```

## Configuration de PHPStorm

```
Compose file(s) : .\docker\docker-compose.yml;
Service(s) : satis_php, satis_nginx, satis_composer,
```

## Configuration des Hosts


```
# C:\Windows\System32\drivers\etc\hosts
...
# localhost name resolution is handled within DNS itself.
	127.0.0.1       autre.local
	127.0.0.1       satis.local
#	::1             localhost
```
[satis.local](http://satis.local/)
