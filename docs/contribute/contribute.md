# Voici les instructions pour contribuer à l'évolution du projet :

## Convention de nommage

Le respect de la convention de nommage PSR-12 (pour toute collaboration se référer a cette
documentation) : https://www.php-fig.org/psr/psr-12/

## Versioning

Pour outil de gestion de version l’utilisation de github est obligatoire

Règles de participation au git : afin d'avoir une meilleure collaboration, il est recommandé de respecter le modèle de
gestion de version [git flow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow)
Utiliser un principe, chaque branche étant dédiée à une seule fonctionnalité qui est ensuite mergé sur la branche
principale.

##Réduction de la dette technique

La version de Symfony utilisée pour développer l’application est la version LTS 5.4, cette version sera donc maintenue
et mise à jour jusqu’à la sortie de la prochaine version LTS de Symfony (V.6.4). La mise à jour du framework de
l'application sera donc facilité.

##Axes d'amélioration :

Voici quelques suggestions d’améliorations possibles :

Mettre à jour le design graphique (responsive inclus)
Intégrer un outil monitoring d’erreur tel que sentry Un log d’erreur

###Fonctionnalités additionnelles :

Un utilisateur peut supprimer son compte un administrateur peut supprimer tous les comptes (sauf l'utilisateur anonyme)
Au moment de la suppression d'un compte toutes les tâches rattachées au compte supprimé seront assignées à l'utilisateur
anonyme. Les tâches deviennent individuelles, un utilisateur peut seulement voir les tâches qu’ils ont créées. Ou bien
introduire le concept d'équipes ou seuls les utilisateurs attachés à une équipe pourraient voir les tâches de
l'équipe. Mettre également le nom d'utilisateur de la personne ayant créé la tâche.


