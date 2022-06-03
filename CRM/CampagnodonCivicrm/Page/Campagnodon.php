<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Page_Campagnodon extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Campagnodon'));

    $controller = new CRM_Core_Controller_Simple('CRM_CampagnodonCivicrm_Form_Search',
      ts('Campagnodon'), NULL
    );
    $controller->setEmbedded(TRUE);

    // $controller->set('limit', 10);
    // $controller->set('force', 1);
    $controller->set('context', 'dashboard');
    $controller->process();
    $controller->run();

    parent::run();
  }

}
