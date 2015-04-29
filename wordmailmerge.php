<?php

require_once 'wordmailmerge.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function wordmailmerge_civicrm_config(&$config) {
  _wordmailmerge_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function wordmailmerge_civicrm_xmlMenu(&$files) {
  _wordmailmerge_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function wordmailmerge_civicrm_install() {
  return _wordmailmerge_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function wordmailmerge_civicrm_uninstall() {
  return _wordmailmerge_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function wordmailmerge_civicrm_enable() {
  return _wordmailmerge_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function wordmailmerge_civicrm_disable() {
  return _wordmailmerge_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function wordmailmerge_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _wordmailmerge_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function wordmailmerge_civicrm_managed(&$entities) {
  return _wordmailmerge_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function wordmailmerge_civicrm_caseTypes(&$caseTypes) {
  _wordmailmerge_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function wordmailmerge_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _wordmailmerge_civix_civicrm_alterSettingsFolders($metaDataFolders);
}


require_once 'CRM/Contact/Task.php';
function wordmailmerge_civicrm_searchTasks( $objectName, &$tasks ){
  $addArray = array(
          'title' => ts('Word Mail Merge'),
          'class' => 'CRM_Wordmailmerge_Form_WordMailMergeForm',
          'result' => TRUE,
        );
  array_push($tasks, $addArray);
}
  
function wordmailmerge_civicrm_buildForm( $formName, &$form ){
  
  if($formName == 'CRM_Admin_Form_MessageTemplates'){
    $templatePath = realpath(dirname(__FILE__)."/templates");
    $config = CRM_Core_Config::singleton();
    $uploadFileSize = CRM_Core_Config_Defaults::formatUnitSize($config->maxFileSize.'m');
    $uploadSize = round(($uploadFileSize / (1024 * 1024)), 2);
    $form->setMaxFileSize($uploadFileSize);
    $form->add('File', 'uploadFile', ts('Import Data File'), 'size=30 maxlength=255', TRUE);
    $form->addRule('uploadFile', ts('File size should be less than %1 MBytes (%2 bytes)', array(1 => $uploadSize, 2 => $uploadFileSize)), 'maxfilesize', $uploadFileSize);
    //$form->addRule('uploadFile', ts('A valid file must be uploaded.'), 'uploadedfile');
    //$form->addRule('uploadFile', ts('Input file must be in opt format'), 'utf8File');
     $form->addElement('text', "attachDesc", NULL, array(
        'size' => 40,
        'maxlength' => 255,
        'placeholder' => ts('Description')
      ));
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "{$templatePath}/CRM/Wordmailmerge/testfield.tpl"
    ));
  }
}

