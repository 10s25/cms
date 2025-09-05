# CMS

CMS intégré aux sites de la manifestation numérique du 10 septembre 2025,
qui souhaitent personnaliser leur site,
et qui disposent d'un hébergement avec une base de données.

Documentation dans le [wiki](https://github.com/10s25/cms/wiki).


## Docker

Pour développer en local avec docker :

```
docker compose -f docker-compose-dev.yml up -d
```

Puis aller dans http://localhost:8880/

## Partage de la sauvegarde

Copier le fichier `database.php` en prod en modifiant l'en-tête. Les IP autorisées pourront consulter `http://localhost:8880/database.php?token=[access_token]` pour récupérer la base.