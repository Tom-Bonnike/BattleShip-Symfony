# Équipe 6 - Symfony / Bataille Navale
- AGUADO Nicolas
- AMIOT Louis
- BONNIKE Tom
- GOUTRY Martin
- RAJATHURAI Chrissen

## Instructions
- Cloner le repository
- Composer install
- Create DB `php bin/console doctrine:database:create`
- Create tables `php app/console doctrine:schema:update --force`
- Exécuter les requêtes de la collection POSTMAN (collection située à la racine du repo)

## Tests unitaires
`phpunit -c app/` *or* `php bin/phpunit -c app/`
Les tests passent à 100%.

## Documentation
La génération de la documentation ne semble pas fonctionner (à cause d'un problème de dépendances?) mais tous les DocBlocks sont présents.
