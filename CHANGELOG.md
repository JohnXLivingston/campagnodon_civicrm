
# Changelog

## 0.0.7 (Not Released Yet)

* On retirer l'ancien filtre «problèmes» dans la recherche de transaction.
* API Start, UpdateStatus et Convert: on donne les droits max sur civicrm au processus en cours d'execution. Ceci afin d'éviter les problèmes avec Civirules.

## 0.0.6

* Ajout d'un nom optionnel pour les «souscriptions optionnelles», qui pourra servir à l'API de conversion.
* API Convert: nettoyage des paramètres non utilisés.
* Ajout d'un trigger Civirules à la création d'une transaction.
* Ajout d'une condition Civirules «non payé».
* Wrapper pour l'API Campaign, pour appliquer les droits «campagnodon api».

## 0.0.5

* Complément d'adresse 1 et 2.
* Fix: les API Start et Updatestatus ne doivent pas retourner de donnée personnelle.
* L'api Updatestatus retourne le statut de la transaction.
* Nouvelle API `convert`.
* Divers fix.

## 0.0.4

* Ajout du mail et du téléphone dans la liste des transactions.
* Ajout du lien vers la transaction sur le système d'origine.
* En cas de double adhésion, on ne créé rien (sauf les contributions), et on passe dans un statut spécial. WIP.
* Ajout d'un champ `operation_type`, affichage, et filtre de recherche.
* Filtre de recherche sur la date de transaction.
* Ajout de deduplication rules custom, qui peuvent se baser sur le type d'opération.
* Prise en compte du champs 'source' pour les création de contact et les contributions.
* Prise en compte du champs 'source' pour les créations d'adhésion (pas le renouvellement).

## 0.0.3

* WIP

## 0.0.2

* WIP

## 0.0.1

* Première version de ce plugin.
