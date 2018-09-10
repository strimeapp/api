Strime versions
===============

This file is made to list the evolutions of Strime over the time.

v1.0.10
------
Launch date: 2017/01/26

  * IMPROVEMENT: Mise en place d'un exception listener pour mieux gérer les erreurs éventuelles, notamment celle que l'on renvoit en cas de connexion indue à l'API.
  * FEATURE: On ajoute des commentaires de démo aux vidéos de démo.

v1.0.9
------
Launch date: 2017/01/05

  * IMPROVEMENT : Au moment du signin, on checke le statut de l'utilisateur et si il a été désactivé, on renvoie une erreur.
  * IMPROVEMENT : Si quelqu'un tente d'accéder à l'API via une IP non autorisée, on le redirige vers le site Strime.
  * IMPROVEMENT : Mise en place de GuzzleHTTP à la place de cURL.
  * IMPROVEMENT : Ajout de tests.
  * IMPROVEMENT : On ajoute un timestamp dans les logs des CRON jobs.
  * FEATURE : On gère dans l'API la liste des adresses mail non confirmées.
  * FEATURE : Lorsque l'on supprime un utilisateur, on enregistre son ID et son nom pour chacune de ses factures.

v1.0.8
------

Launch date: 2016/11/21

  * IMPROVEMENT: On passe en HTTPS sur l'environnement de test.
  * IMPROVEMENT : On enrichit les infos renvoyées dans le JSON à l'ajout d'une vidéo.
  * IMPROVEMENT : On ajoute l'ID de l'encoding dans le retour qu'on fait sur le endpoint /videos/user/{user_id}/get.
  * IMPROVEMENT : Upgrade des bundles
  * BUG : Correction dans la table de sessions à utiliser avec l'API.
  * FEATURE : Mise en place de test pour les controllers Offer, Token & User.
  * IMPROVEMENT: on crée des data fixtures pour peupler les tables principales de la base pour les tests.
  * IMPROVEMENT: on gère l'authentification dans un listener.

v1.0.7
------

Launch date: 2016/10/18

  * FEATURE: création d'un endpoint renvoyant le nombre de commentaires associés à une vidéo.
  * IMPROVEMENT: quand on supprime un commentaire, supprimer également les réponses qui correspondent à ce commentaire.
  * IMPROVEMENT: on ajoute un paramètre dans les infos renvoyées pour les commentaires, spécifiant de quel type est l'auteur (contact ou user).
  * IMPROVEMENT: on modifie le nom du champ renvoyé pour le secret_id de l'auteur d'un commentaire.

v1.0.6
------

Launch date: 2016/10/07

  * FEATURE: lorsque l'on supprime un encoding job, ca supprime également la vidéo associée.
  * FEATURE: lorsque l'on supprime un projet, ca supprime également les vidéos contenues dans ce projet.
  * IMPROVEMENT: factorisation du processus de suppression d'une vidéo dans une classe indépendante.
  * BUG FIX: dans la suppression d'un projet, on force la suppression des tâches d'encodage correspondantes.

v1.0.5
------

Launch date: 2016/08/08

  * Correction d'une erreur de logique dans le calcul des stats.
  * Création d'un endpoint pour récupérer le % d'utilisateurs actifs par jour.
  * Création d'un CRON job permettant de calculer le % d'utilisateurs actifs.
  * On ajoute enregistre le dernier login de chaque utilisateur.

v1.0.4
------

Launch date: 2016/07/28

  * On fait évoluer l'entité Invoice et création d'un endpoint pour éditer une facture.

V1.0.3
------

Launch date: 2016/07/19

  * On crée un endpoint qui permet de récupérer la liste des encodings jobs coincés à 5%.
  * On change la longueur allouée aux IDs Stripe.
  * On remplace le numéro de téléphone par le numéro de TVA intra.
  * Création d'une entité coupon et du controller qui va avec.
  * On ajoute le paramètre espace de stockage à la création d'une offre.
  * Correction d'un bug dans l'édition de l'offre d'un utilisateur.
  * Modification de la manière dont on récupère le nombre de commentaires par vidéos et de vidéos par projet.
  * Création d'un nouveau CRON calculant le nombre d'utilisateur par type d'offre.
  * Correction d'un bug dans la récupération des factures pour le cas d'utilisateurs ayant supprimé leur compte.
  * Ajout d'un endpoint permettant de récupérer la liste des factures générées sur une période donnée.
  * Ajout d'un endpoint permettant de récupérer la liste complète des factures générées.
  * On modifie le endpoint de suppression d'une vidéo pour qu'il supprime également les encoding jobs associés.

V1.0.2
------

Launch date: 2016/05/31

  * On change le fonctionnement des logs en prod.
  * Correction d'un bug dans la mise à jour des encoding jobs.
  * Correction d'un bug dans la récupération des encoding jobs.
  * Légère modification dans l'édition d'un commentaire.

V1.0.1
------

Launch date: 2016/05/23

  * Petit correctif sur la vérification du nombre de vidéos mises en ligne par l'utilisateur dans addVideo.

V1.0.0
------

Launch date: 2016/05/20

  * Full version of the app with Stripe integrated, real offers, encoding out of the API, ...