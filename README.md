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

## TODO

* Restreindre les droits nécessaires ? Ce qui permettrait d'avoir une API KEY liée à un user qui ne pourrait appeler que les API Campagnodon, et celles-ci by-passeraient les droits sur les autres API.
