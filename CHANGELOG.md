
# Changelog

## 2.2.1

* Fix adhésions renouvelable après 1 ans (introduction du paramètre campagnodon_renewal_force_status_id).
* Fix colonne manquante dans auto_install.sql

## 2.2.0

* Nouveau settings "campagnodon_new_membership_rollover_day_month" optionnel, pour gérer une date de "rollover" différente pour les nouvelles adhésions. Ceci est un paramètre utilisé par Attac, et semple assez spécifique. Il n'est pas recommandé de l'utiliser dans d'autres contextes.

## 2.1.0

* Compatibility CiviCRM jusqu'à au moins 5.75.x, et Smarty v2/v3/v4
* Fix de warnings PHP.

## 2.0.0

* Compatibilité avec Campagnodon SPIP v2.0.0 (et les adhésions avec renouvellement).
* Recherche de transactions: la recherche sur le type d'opération permet d'en sélectionner plusieurs.
* Note: en théorie on est toujours compatible avec Campagnodon SPIP < v2.0.0.

## 1.6.3

* Fix: la recherche de doublon n'utilisant pas le paramètre "check_permissions=FALSE", et ne trouvais donc pas les doublons dans toutes les situations.

## 1.6.2

* Logs utiles en cas de rollback.

## 1.6.1

* Recherche des contacts: on ignore les contacts supprimés. Cela n'est pas censé arriver, mais il semblerait que ce soit le cas sur certains environnements.
* Script fix_contribution_date.php pour corriger les dates des contributions.

## 1.6.0

* Ajout d'un champs «contribution_date» sur les transactions. Cette date sera celle utilisée pour le champs «receive_date» des contributions. Par défaut, égal à start_date.
* L'API «Recurrence» prend un paramètre contribution_date optionnel.

## 1.5.0

* Api Campaign: ajout de current_revenue
* Api Campaign: correctif de sécurité. On filtre les paramètres et les valeurs retournées, pour éviter des fuites (et des jointures).

## 1.4.2

* Fix: quand on re-synchronise une double adhésion, on garde le statut double adhésion.
* Ajout de l'identifiant de la transaction dans les logs de l'API UpdateStatus.

## 1.4.1

* Fix: quand on re-synchronise une adhésion qui est déjà completed, il ne faut pas la passer en double adhésion.

## 1.4.0

* Ajout d'un filtre «campagne» sur les Civirules.

## 1.3.0

* Changements sur l'API de migration.

## 1.2.1

* Options pour spécifier un champs optionnel sur les contributions sur lequel mapper «tax_receipt» (Attac France utilise custom_1).
* Migration dons récurrents: migration du champs «tax_receipt».

## 1.2.0

* Simplification de l'API de récurrence: c'est le système d'origine qui spécifie le montant, on ne cherche plus à dériver de la transaction parent.

## 1.1.0

* API de Migration pour les anciennes contributions.

## 1.0.2

* Affichage des statuts de récurrence.
* Affichage de la liste des transactions enfants.

## 1.0.1

* Mise à jour des infos de l'extension.

## 1.0.0

* Dons récurrents.
* Descriptions des permissions.
* Ajout de traductions manquantes.

## 0.1.1

* Ajout de l'action d'envoi de mail.

## 0.1.0

* API de test.

## 0.0.14

* Dsp2: on retourne aussi le téléphone.

## 0.0.13

* Fix échappement html dans les templates.

## 0.0.12

* Condition NoOther: labels and help.
* Fix Trigger CamapnodonTransactionDaysAfter: on ne le lance qu'une fois par transaction.

## 0.0.11

* Condition NoOther: days est optionnel.

## 0.0.10

* Condition NoOther: ajout d'une option «ces X derniers jours».

## 0.0.9

* Fix formulaire de la condition DaysAfter, quand days = 0.
* Ajout de la condition CiviRules «pas d'autre transaction pour le contact».
* Meilleure présentation pour les formulaires de condition Civirule.
* Logs: utilisation de __METHOD__ au lieu de __FUNCTION__.
* Fix Civirules: le mauvais ID était stocké dans la table de log.

## 0.0.8

* Ajout de la condition CiviRules sur le type de transaction.
* Fix url pour l'édition des conditions, qui pouvaient entrainer une corruption de la base à la sauvegarde.

## 0.0.7

* On retirer l'ancien filtre «problèmes» dans la recherche de transaction.
* Remplacement du trigger Civirule new_campagnodon_transaction par un trigger de type cron «X jours après».
* Ajout de la condition CiviRules sur le Status des transactions.

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
