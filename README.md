# campagnodon_civicrm

Cette extension CiviCRM va de paire avec le module SPIP [Campagnodon](https://code.globenet.org/attacfr/campagnodon).

Ces modules ont été développés pour les besoins d'[Attac France](https://france.attac.org).
Le but est d'intégrer des formulaires de dons et d'adhésions sur un site SPIP, mais en hébergeant toutes les données personnelles dans un CiviCRM.

Cette extension est publiée sous licence [AGPL-3.0](LICENSE.txt).
Elle est inspirée du travail fais sur les extensions [attac-api](https://github.com/TechToThePeople/attac-api) et [proca-civicrm](https://github.com/fixthestatusquo/proca-civicrm) (toutes deux sous licence AGP-3.0).

## Requirements

* PHP v7.2+
* CiviCRM 5.*

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl campagnodon_civicrm@https://github.com/FIXME/campagnodon_civicrm/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/campagnodon_civicrm.git
cv en campagnodon_civicrm
```

## Getting Started

(* FIXME: Where would a new user navigate to get started? What changes would they see? *)

Aller sur la page de configuration (Adminitration > Campagnodon), et vérifier les paramètres.

## Known Issues

(* FIXME *)

## Développement

### Environnement de dev

Commencer par installer les [outils de développement civibuild](https://docs.civicrm.org/dev/en/latest/tools/civibuild/) quelque part sur votre machine.

NB: si vous ne les ajoutez pas dans le `$PATH` de votre machine, il faudra parfois ajouter `PATH="$HOME/xxx/civicrm/bin/:$PATH"` en début de ligne de commande (adapter le dossier pointé).

Vous pouvez ensuite déployer un site de développement (voir la doc de `civibuild create`).
Dans la suite de ce document, on l'appellera `campagnodon_dev`.

Ensuite, créez un lien symbolique de `civicrm/build/campagnodon_dev/web/sites/default/files/civicrm/ext/campagnodon_civicrm` vers le dossier où vous avez cloné le dépot git:

```bash
cd le_dossier_des_tools/civicrm/build/campagnodon_dev/web/sites/default/files/civicrm/ext/
ln -s /home/xxx/mon_dossier_de_dev/campagnodon_civicrm
```

### Tests unitaires

Pour lancer les tests unitaires, il faut d'abord installer [civix](https://docs.civicrm.org/dev/en/latest/extensions/civix/). La documentation officielle pour les tests unitaires est [ici](https://docs.civicrm.org/dev/en/latest/testing/phpunit/#running-tests).

Ensuite, allez dans le dossier `civicrm/build/campagnodon_dev/web/sites/default/files/civicrm/ext/campagnodon_civicrm` et lancez:

```bash
phpunit6
```

### Mettre à jour les fichiers de langue

On peut régénérer le fichier [de langue](./l10n/campagnodon_civicrm.pot) via la commande:

```bash
civistrings  -o l10n/campagnodon_civicrm.pot .
```

TODO: ne vaut-il pas mieux faire `civistrings  -o l10n/campagnodon_civicrm.pot .` ? Il semblerait que l'option `-a` ajoute en fin au lieu d'écraser.

Bien vérifier qu'on n'a rien perdu avant de commiter.

Ensuite, il faut reporter les modifications sur ce fichier dans [./l10n/fr_FR/LC_MESSAGES/campagnodon_civicrm.po](./l10n/fr_FR/LC_MESSAGES/campagnodon_civicrm.po).
Attention, ne pas écraser le fichier (sinon on perd les traductions).

Ensuite, recompiler (il faut avoir gettext installé sur sa machine):

```bash
msgfmt l10n/fr_FR/LC_MESSAGES/campagnodon_civicrm.po -o l10n/fr_FR/LC_MESSAGES/campagnodon_civicrm.mo
```

## Scripts utilitaires

Dans le dossier `utils`, on pourra trouver des scripts utilitaires.

### fix_contribution_date.php

Les premières versions de Campagnodon avaient des bugs sur les dates des contributions :

* pour les paiements par chèque, c'est la date de saisie du chèque qui était prise en compte, alors qu'on veut la date de la transaction
* pour les paiements récurrents en virement SEPA, on a un décalage de 15 jours environ à cause du fonctionnement de SPIP Bank.

La version 1.6.0 de campagnodon-civicrm introduit donc un nouveau champs sur les transactions : contribution_date.
Ce sera la date à utiliser pour les contributions crées.

Reste à migrer les données existantes.

Pour cela, il a été choisi de créer le présent script.
Celui-ci prend en entrée du JSON de la forme :

```json
[
  {idx:'campagnodon/00001228', date:'2023-01-01 12:32:00'}
]
```

On pourra donc générer de tels fichiers JSON à partir de la base de donnée d'origine (SPIP), en fonction des cas.
Ensuite, il suffira d'injecter ces données dans le script.

À noter que le script comparera la date (sans l'heure) des champs civicrm_campagnodon_transaction.contribution_date et
civicrm_contribution.receive_date. Si la date est la bonne, rien ne change. Sinon, il appliquera la date et l'heure demandées.

Le script dispose d'un mode `test`, qui ne fait aucune modification, et d'un mode `run` qui effectue les modifications.

Le script écrit sur la sortie standard un journal des actions effectuées (ou non).

Pour l'appeler, il faut avoir l'utilitaire `cv` de Civicrm installé. Et se placer dans un sous-dossier du CiviCRM concerné,
pour que `cv` puisse trouver la configuration.

Attention: il peut tout à fait arriver que Campagnodon soit installé dans un dossier qui n'est pas dans la bonne arborescence.

Voilà un example d'appel :

```bash
cd /srv/civicrm.tld/www/sites/default/;
cat /tmp/contribution_dates.json | php /srv/civicrm.tld/www/sites/custom_ext/campagnodon_civicrm/utils.fix_contribution_date.php test;
cat /tmp/contribution_dates.json | php /srv/civicrm.tld/www/sites/custom_ext/campagnodon_civicrm/utils.fix_contribution_date.php run | tee /tmp/contribution_dates.log;
```

Pour obtenir le JSON, on pourra effectuer ce genre de requêtes SQL:

```mysql
select JSON_ARRAYAGG(
  JSON_OBJECT(
    'idx', spip_campagnodon_transactions.transaction_distant,
    'contribution_date', spip_campagnodon_transactions.date_transaction
  )
) from spip_campagnodon_transactions WHERE type_transaction = 'don';

select JSON_ARRAYAGG(
  JSON_OBJECT(
    'idx', spip_campagnodon_transactions.transaction_distant,
    'contribution_date', spip_campagnodon_transactions.date_transaction
  )
) from spip_campagnodon_transactions WHERE type_transaction = 'adhesion';

select JSON_ARRAYAGG(
  JSON_OBJECT(
    'idx', spip_campagnodon_transactions.transaction_distant,
    'contribution_date', spip_campagnodon_transactions.date_transaction
  )
) from spip_campagnodon_transactions WHERE type_transaction = 'don_mensuel';

select JSON_ARRAYAGG(
  JSON_OBJECT(
    'idx', spip_campagnodon_transactions.transaction_distant,
    'contribution_date', spip_transactions.date_paiement
  )
) from spip_campagnodon_transactions
LEFT JOIN spip_transactions ON spip_campagnodon_transactions.id_transaction = spip_transactions.id_transaction
WHERE spip_campagnodon_transactions.type_transaction = 'don_mensuel_echeance';

select JSON_ARRAYAGG(
  JSON_OBJECT(
    'idx', spip_campagnodon_transactions.transaction_distant,
    'contribution_date', spip_transactions.date_paiement
  )
) from spip_campagnodon_transactions
LEFT JOIN spip_transactions ON spip_campagnodon_transactions.id_transaction = spip_transactions.id_transaction
WHERE spip_campagnodon_transactions.type_transaction = 'don_mensuel_migre';
```

## TODO

* Documentation les règles de dédoublonnage custom.
