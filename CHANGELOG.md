CHANGELOG
=========

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

[Unreleased]
------------

### Added

### Changed

- BUG: Correction d'un bug lors de la suppression des fichiers audio lorsqu'un utilisateur supprime un projet.


[1.7.0] - 2017-08-10
--------------------

### Added

- FEATURE: Création de différents controllers de stats et d'un spécifique pour les images.
- FEATURE: Création d'une entité et d'un CRON job calculant le nombre d'images postées chaque jour.
- FEATURE: Création d'une entité et d'un CRON job calculant le nombre de commentaires postés sur les images chaque jour.
- FEATURE: Création d'un CRON job calculant le nombre de fichiers images mis en ligne.
- FEATURE: Création d'un CRON job calculant le nombre de fichiers commentaires postés sur des images.
- FEATURE: Création d'un CRON job calculant la taille moyenne des fichiers images.
- FEATURE: Création d'un CRON job pour envoyer les notifications de commentaires sur les images.
- FEATURE: Création d'un controller ImageComment avec tous les endpoints qui vont bien.
- FEATURE: Création d'un controller Image avec tous les endpoints qui vont bien.
- FEATURE: On renvoit les droits des utilisateurs dans les endpoint du controller User.
- FEATURE: Création d'un bundle Image sur le modèle du bundle Video pour gérer tout ce qui a trait aux images.
- FEATURE: Création d'une table permettant de gérer les droits des utilisateurs.
- FEATURE: Mise en place d'une entité UserYoutube et modification du Controller User pour gérer les connexions à Youtube.

### Changed

- IMPROVEMENT: On adapte le CRON d'envoi des notifs de commentaires quotidien au fait qu'il y ait plusieurs types de fichiers.
- IMPROVEMENT: Modification du CRON job d'envoi des notifications de commentaires sur les vidéos, pour faire suite aux changements liés à l'introduction des images.
- IMPROVEMENT: Quand on supprime un projet, on supprime aussi les images qu'il contient.
- IMPROVEMENT: On renomme certaines propriété renvoyées par le JSON pour qu'elles soient plus génériques.
- IMPROVEMENT: On factorise le calcul du nombre d'éléments par projet.
- IMPROVEMENT: Suppression de méthodes inutiles dans le helper Avatar.
- BUG: Correction d'un bug dans la mise à jour des stats de durée d'encodage à la suppression d'une vidéo.
- IMPROVEMENT: Ajout d'un champ description aux vidéos et modification du controller en conséquence.


[1.6.1] - 2017-06-30
--------------------

### Changed

- BUG : Correction d'un bug dans le CRON pour générer les miniatures des commentaires.


[1.6.0] - 2017-06-22
--------------------

### Added

### Changed


[1.5.3] - 2017-06-22
--------------------

### Added

- FEATURE: Load Balancing : À l'ajout d'une vidéo, on détecte quel est le serveur d'encodage le moins chargé et on utilise celui-là.
- FEATURE: Création d'un helper permettant quel est le serveur d'encodage le moins chargé.
- FEATURE: Création d'un endpoint pour récupérer les stats sur les temps d'encodage.
- FEATURE: Mise en place d'un CRON job qui détecte les tâches d'encodage bloquées à 5%, les redémarre une fois ou les tue.
- FEATURE: Mise en place d'une entité UserFacebook et modification du Controller User pour gérer les connexions via Facebook.

### Changed

- IMPROVEMENT: On remet en place le vrai serveur de test.
- BUG: On s'assure qu'à la suppression d'un utilisateur, toutes les stats sur les durées d'encodage aient bien une valeur NULL pour la vidéo pour ne pas planter.


[1.5.3] - 2017-06-15
--------------------

### Changed

- IMPROVEMENT: Quand on supprime un encodage sans vidéo associée, on vérifie malgré tout qu'aucune vidéo n'y soit associée...


[1.5.2] - 2017-06-14
--------------------

### Changed

- BUG: Quand on supprime un encodage.


[1.5.1] - 2017-06-13
--------------------

### Changed

- BUG: Quand on supprime un projet, passer à NULL la valeur vidéo des stats de durée d'encodage.
- BUG: On met à jour la table EncodingJobTime quand on supprime un encodage ou une vidéo.
- BUG: amélioration de la gestion de la taille de l'entité EncodingJobTime dans le controller EncodingJob.
- BUG: correction de l'entité EncodingJobTime et du fait que la taille puisse avoir une valeur nulle.


[1.5.0] - 2017-06-12
--------------------

### Added

- FEATURE: Mise en place d'un CRON job pour générer de manière automatisée l'envoi des notifs de commentaires, laissant ainsi le temps de générer les miniatures.
- FEATURE: Mise en place d'une entité et de controller permettant le calcul du temps des encodages.
- FEATURE: Mise en place de requête vers l'API (concept de sonde) au début et en fin d'encodage permettant le calcul de stats sur les temps d'encodage.

### Changed

- IMPROVEMENT: Dans le endpoint pour récupérer les infos d'un projet, on retourne le screenshot de la vidéo la plus récente du projet.
- IMPROVEMENT: Quand on supprime une vidéo, on supprime aussi d'Amazon les screenshots de ses commentaires.
- IMPROVEMENT: Modification du endpoint de modification d'une vidéo pour pouvoir changer son dossier, et éventuellement la sortir d'un dossier.
- IMPROVEMENT: Création d'une entité CommentWithThumbInError et modification du CRON correspondant pour faire en sorte qu'après 3 essais infructueux, on ne retente pas de générer la miniature des commentaires.
- IMPROVEMENT: On stocke dans Mailchimp la locale de l'utilisateur, et on la met à jour si besoin.
- IMPROVEMENT: On stocke dans l'entité vidéo la durée de celle-ci.


[1.4.0] - 2017-04-28
--------------------

### Added

- FEATURE: Création d'un CRON job et d'un endpoint pour connaître le nombre d'utilisateurs utilisant Slack
- FEATURE: Création d'un CRON job et d'un endpoint pour connaître le nombre d'utilisateurs utilisant le Google Signin

### Changed

- IMPROVEMENT: On agrandit la taille du champ image pour les utilisateurs utilisant Google.
- IMPROVEMENT: On change l'encodage des commentaires pour pouvoir gérer les emojis en front.
- IMPROVEMENT: Ajout, dans le JSON retourné par les endpoints GET du controller video, de la taille des vidéos.
- IMPROVEMENT: Suppression d'une fonction inutilisée dans le controller video, envoyant un mail de debug.

[1.3.0] - 2017-04-06
--------------------

### Added

- FEATURE: Création d'un endpoint permettant de lister toutes les vidéos.
- FEATURE: Création d'un CRON job pingant l'API d'encoding pour générer un screenshot pour chaque commentaire.
- FEATURE: Création d'une entité UserSlack et modification du controller User.
- FEATURE: Création d'un CRON job calculant la répartition des utilisateurs par langue, et du endpoint correspondant.
- FEATURE: Ajout des infos Google des utilisateurs dans les JSON et création d'un endpoint pour révoquer Google d'un utilisateur.
- FEATURE: Mise en place d'une entité pour gérer les infos Google des utilisateurs.

### Changed

- IMPROVEMENT: On envoie un mail de relance pour la confirmation de l'email 1j avant la désactivation.
- IMPROVEMENT: On utilise un bucket dédié au stockage des screenshots de commentaires.
- IMPROVEMENT: Ajout d'un champ s3_url à l'entité Comment.

[1.2.0] - 2017-03-22
--------------------

### Added

- FEATURE: Création d'une entité pour enregistrer les éléments d'info venant de Google, et modification des endpoints user en conséquence.
- FEATURE: Création de CRON jobs effectuant des stats sur les projets, vidéos, commentaires et contacts.
- FEATURE: Mise en place d'un script d'initialisation de la valeur mail_notification.
- FEATURE: Ajout d'un champ mail_notification pour gérer les notifications mail des utilisateurs.

### Changed

- IMPROVEMENT: Amélioration de CRON job de désactivation des comptes.
- IMPROVEMENT: On ajoute les données de base de la vidéo dans le JSON de réponse pour les détails d'un commentaire.
- IMPROVEMENT: Corrections dans la manière dont on gère mailchimp dans l'édition du profil d'un utilisateur.

[1.1.0] - 2017-03-07
--------------------

### Added

- FEATURE: Traduction des noms du projet par défaut, des vidéos par défaut, et des commentaires associés.
- FEATURE: On adapte la numérotation de l'app au Semantic Version (http://semver.org/)
- FEATURE: Création d'un CHANGELOG-1.0.md pour archivage

### Changed

- IMPROVEMENT: On attribue les commentaires de démo à l'utilisateur créé.
