<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Wordmailmerge_Form_WordMailMergeForm extends CRM_Core_Form {
  
  static protected $_searchFormValues;
  function preProcess() {
    self::preProcessCommon($this);
  }

  /**
   * @param $form
   * @param bool $useTable
   */
  static function preProcessCommon(&$form, $useTable = FALSE) {
    $form->_contactIds = array();
    $form->_contactTypes = array();
    // get the submitted values of the search form
    // we'll need to get fv from either search or adv search in the future
    $fragment = 'search';
    if ($form->_action == CRM_Core_Action::ADVANCED) {
      self::$_searchFormValues = $form->controller->exportValues('Advanced');
      $fragment .= '/advanced';
    }
    elseif ($form->_action == CRM_Core_Action::PROFILE) {
      self::$_searchFormValues = $form->controller->exportValues('Builder');
      $fragment .= '/builder';
    }
    elseif ($form->_action == CRM_Core_Action::COPY) {
      self::$_searchFormValues = $form->controller->exportValues('Custom');
      $fragment .= '/custom';
    }
    else {
      self::$_searchFormValues = $form->controller->exportValues('Basic');
    }

    //set the user context for redirection of task actions
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $form);
    $urlParams = 'force=1';
    if (CRM_Utils_Rule::qfKey($qfKey)) {
      $urlParams .= "&qfKey=$qfKey";
    }
    $cacheKey = "civicrm search {$qfKey}";
    $url = CRM_Utils_System::url('civicrm/contact/' . $fragment, $urlParams);
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext($url);
    $form->_task = CRM_Utils_Array::value('task', self::$_searchFormValues);
    $crmContactTaskTasks = CRM_Contact_Task::taskTitles();
    $form->assign('taskName', CRM_Utils_Array::value($form->_task, $crmContactTaskTasks));
    if ($useTable) {
      $form->_componentTable = CRM_Core_DAO::createTempTableName('civicrm_task_action', TRUE, $qfKey);
      $sql = " DROP TABLE IF EXISTS {$form->_componentTable}";
      CRM_Core_DAO::executeQuery($sql);

      $sql = "CREATE TABLE {$form->_componentTable} ( contact_id int primary key) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
      CRM_Core_DAO::executeQuery($sql);
    }

    // all contacts or action = save a search
    if ((CRM_Utils_Array::value('radio_ts', self::$_searchFormValues) == 'ts_all') ||
      ($form->_task == CRM_Contact_Task::SAVE_SEARCH)
    ) {
      $sortByCharacter = $form->get('sortByCharacter');
      $cacheKey = ($sortByCharacter && $sortByCharacter != 'all') ? "{$cacheKey}_alphabet" : $cacheKey;

      // since we don't store all contacts in prevnextcache, when user selects "all" use query to retrieve contacts
      // rather than prevnext cache table for most of the task actions except export where we rebuild query to fetch
      // final result set
      if ($useTable) {
        $allCids = CRM_Core_BAO_PrevNextCache::getSelection($cacheKey, "getall");
      }
      else {
        $allCids[$cacheKey] = $form->getContactIds();
      }

      $form->_contactIds = array();
      if ($useTable) {
        $count = 0;
        $insertString = array();
        foreach ($allCids[$cacheKey] as $cid => $ignore) {
          $count++;
          $insertString[] = " ( {$cid} ) ";
          if ($count % 200 == 0) {
            $string = implode(',', $insertString);
            $sql = "REPLACE INTO {$form->_componentTable} ( contact_id ) VALUES $string";
            CRM_Core_DAO::executeQuery($sql);
            $insertString = array();
          }
        }
        if (!empty($insertString)) {
          $string = implode(',', $insertString);
          $sql = "REPLACE INTO {$form->_componentTable} ( contact_id ) VALUES $string";
          CRM_Core_DAO::executeQuery($sql);
        }
      }
      else {
        // filter duplicates here
        // CRM-7058
        // might be better to do this in the query, but that logic is a bit complex
        // and it decides when to use distinct based on input criteria, which needs
        // to be fixed and optimized.

        foreach ($allCids[$cacheKey] as $cid => $ignore) {
          $form->_contactIds[] = $cid;
        }
      }
    }
    elseif (CRM_Utils_Array::value('radio_ts', self::$_searchFormValues) == 'ts_sel') {
      // selected contacts only
      // need to perform action on only selected contacts
      $insertString = array();

      // refire sql in case of custom seach
      if ($form->_action == CRM_Core_Action::COPY) {
        // selected contacts only
        // need to perform action on only selected contacts
        foreach (self::$_searchFormValues as $name => $value) {
          if (substr($name, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
            $contactID = substr($name, CRM_Core_Form::CB_PREFIX_LEN);
            if ($useTable) {
              $insertString[] = " ( {$contactID} ) ";
            }
            else {
              $form->_contactIds[] = substr($name, CRM_Core_Form::CB_PREFIX_LEN);
            }
          }
        }
      }
      else {
        // fetching selected contact ids of passed cache key
        $selectedCids = CRM_Core_BAO_PrevNextCache::getSelection($cacheKey);
        foreach ($selectedCids[$cacheKey] as $selectedCid => $ignore) {
          if ($useTable) {
            $insertString[] = " ( {$selectedCid} ) ";
          }
          else {
            $form->_contactIds[] = $selectedCid;
          }
        }
      }

      if (!empty($insertString)) {
        $string = implode(',', $insertString);
        $sql = "REPLACE INTO {$form->_componentTable} ( contact_id ) VALUES $string";
        CRM_Core_DAO::executeQuery($sql);
      }
    }

    //contact type for pick up profiles as per selected contact types with subtypes
    //CRM-5521
    if ($selectedTypes = CRM_Utils_Array::value('contact_type', self::$_searchFormValues)) {
      if (!is_array($selectedTypes)) {
        $selectedTypes = explode(' ', $selectedTypes);
      }
      foreach ($selectedTypes as $ct => $dontcare) {
        if (strpos($ct, CRM_Core_DAO::VALUE_SEPARATOR) === FALSE) {
          $form->_contactTypes[] = $ct;
        }
        else {
          $separator = strpos($ct, CRM_Core_DAO::VALUE_SEPARATOR);
          $form->_contactTypes[] = substr($ct, $separator + 1);
        }
      }
    }

    if (CRM_Utils_Array::value('radio_ts', self::$_searchFormValues) == 'ts_sel'
      && ($form->_action != CRM_Core_Action::COPY)
    ) {
      $sel = CRM_Utils_Array::value('radio_ts', self::$_searchFormValues);
      $form->assign('searchtype', $sel);
      $result = CRM_Core_BAO_PrevNextCache::getSelectedContacts();
      $form->assign("value", $result);
    }

    if (!empty($form->_contactIds)) {
      $form->_componentClause = ' contact_a.id IN ( ' . implode(',', $form->_contactIds) . ' ) ';
      $form->assign('totalSelectedContacts', count($form->_contactIds));

      $form->_componentIds = $form->_contactIds;
    }
  }

  function buildQuickForm() {
    $result = civicrm_api3('MessageTemplate', 'get', array(
                            'sequential' => 1,
                            'msg_title' => "veda test template",
                          ));
    $messageTpl = $result['values'][0]['msg_html'];
    $this->_msg_val = $result['values'][0];
    // add form elements
    $this->add('select', 'message_template', ts('Message Template'), array(''=> $messageTpl));
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Merge'),
        'isDefault' => TRUE,
      ),
    ));
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->_contactIds;
    $noofContact = count($this->_contactIds);
    $config = CRM_Core_Config::singleton();
    require_once $config->extensionsDir.'\uk.co.vedaconsulting.module.wordmailmerge\tinybutstrong\tbs_class.php';
    require_once $config->extensionsDir.'\uk.co.vedaconsulting.module.wordmailmerge\tinybutstrong-opentbs\tbs_plugin_opentbs.php';

    $TBS = new clsTinyButStrong; // new instance of TBS
    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin
    $template = $config->extensionsDir.'\uk.co.vedaconsulting.module.wordmailmerge\template.odt';
    foreach ($values as $key => $value) {
      if($key < $noofContact){
        $selectedCID = $values[$key];
        $contact = $this->getContact($selectedCID);
        $vars[$key]['DisplayName']  = $contact['display_name'];
        $vars[$key]['ContactId']  = $contact['contact_id'];
        $vars[$key]['StreetAddress']  = $contact['street_address'];
        $vars[$key]['SupplementalAddress_1']  = $contact['supplemental_address_1'];
        $vars[$key]['SupplementalAddress_2']  = $contact['supplemental_address_2'];
        $vars[$key]['City']  = $contact['city'];
        $vars[$key]['PostalCode']  = $contact['postal_code'];
        $vars[$key]['CountryId']  = CRM_Core_PseudoConstant::country($contact['country_id']);
        $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
        $TBS->MergeBlock('a',$vars);
      }
    }
    $output_file_name = 'wordmailmerge.odt';
    $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name);
    CRM_Utils_System::civiExit();
    parent::postProcess();
  }
  function getContact($selectedCID) {
    $result = civicrm_api3('Contact', 'getsingle', array(
                           'sequential' => 1,
                           'contact_id' => $selectedCID,
    ));
    return $result;
  }
  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
