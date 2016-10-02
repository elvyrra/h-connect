<?php

/**
 * ContactController.class.php
 */

namespace Hawk\Plugins\HConnect;

class ConnectController extends Controller{

    /**
     * Index page of the plugin HConnect
     */
    public function index(){

        // Create main tabs
        $tabs = array(
            'contact' => array(
                'title' => Icon::make(array('icon' => 'user')) . ' ' . Lang::get($this->_plugin . '.contact-tab-title'),
                'content' => $this->getListContact()
            ),
            'group' => array(
                'title' => Icon::make(array('icon' => 'users')) . ' ' . Lang::get($this->_plugin . '.group-tab-title'),
                'content' => $this->getListGroup()
            ),
        );

        // Add admin tabs only for users allow
        if(App::session()->isAllowed($this->_plugin . '.admin-contact')){
            $tabs['settings'] = array(
                'title' => Icon::make(array('icon' => 'cogs')) . ' ' . Lang::get($this->_plugin . '.settings-tab-title'),
                'content' => SettingsController::getInstance()->listQuestions()
            );
        }

        // By default, tab selected is contact
        $tabSelected = "contact";

        // Check if tab is specified in parameter
        if(App::request()->getParams('tabs')) {
            $tabSelected = App::request()->getParams('tabs');
        }

        // Create page
        $page = Tabs::make(array(
            'id' => 'h-connect-contact-tabs',
            'selected' => $tabSelected,
            'tabs' => $tabs
        ));

        // Add css file
        $this->addCss(Plugin::current()->getCssUrl('contact.less'));

        return NoSidebarTab::make(array(
            'page' => array(
                'content' => $page,
            ),
            'title' => Lang::get($this->_plugin . '.page-title'),
            'tabId' => 'h-connect-contact-page',
            'icon' => $this->getPlugin()->getFaviconUrl()
        ));
    }

    /**
     * Get list of all contacts for a userId
     */
    public function getListContact(){

        // Create list with all contact for selected userId
        $param = array(
            'id' => 'h-connect-my-contacts-list',
            'model' => 'HContact',
            'action' => App::router()->getUri('h-connect-contact-list'),
            'reference' => 'id',
            'filter' => new DBExample(array(
                'userId' => App::session()->getUser()->id,
            )),
            'selectableLines' => true,
            'controls' => array(
                'download' => array(
                    'icon' => 'download',
                    'label' => Lang::get($this->_plugin . '.btn-export-contact-label'),
                    'class' => 'btn-info',
                    'id' => 'export-contact-list-button'
                ),
                'upload' => array(
                    'icon' => 'upload',
                    'label' => Lang::get($this->_plugin . '.btn-import-contact-label'),
                    'class' => 'btn-info',
                    'href' => App::router()->getUri('h-connect-contact-import'),
                    'target' => 'dialog',
                ),
                array(
                    'icon' => 'plus',
                    'label' => Lang::get($this->_plugin . '.btn-add-contact-label'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri('h-connect-contact-edit', array('contactId' => 0)),
                )
            ),
            'fields' => array(
                'actions' => array(
                    'independant' => true,
                    'search' => false,
                    'sort' => false,
                    'display' => function($value, $field, $contact){

                        return Icon::make(array(
                            'icon' => 'wrench',
                            'size' => 'lg',
                            'href' => App::router()->getUri('h-connect-contact-edit', array('contactId' => $contact->id)),
                            'title' => Lang::get($this->_plugin . '.list-edit-contact'),
                        )) .

                        Icon::make(array(
                            'icon' => 'times-circle',
                            'size' => 'lg',
                            'class' => 'text-danger delete-contact',
                            'data-contact' => $contact->id,
                            'title' => Lang::get($this->_plugin . '.list-remove-contact'),
                        ));
                    }
                ),
                
                'lastName' => array(
                    'label' => Lang::get($this->_plugin . '.lastName-label'),
                    'href' => function($value, $field, $contact) {
                        return App::router()->getUri('h-connect-contact-edit', array('contactId' => $contact->id));
                    },
                ),

                'firstName' => array(
                    'label' => Lang::get($this->_plugin . '.firstName-label'),
                    'href' => function($value, $field, $contact) {
                        return App::router()->getUri('h-connect-contact-edit', array('contactId' => $contact->id));
                    },
                ),
                
                'company' => array(
                    'label' => Lang::get($this->_plugin . '.company-label'),
                ),

                'job' => array(
                    'label' => Lang::get($this->_plugin . '.company-label'),
                ),

                'cellNumber' => array(
                    'label' => Lang::get($this->_plugin . '.cellNumber-label'),
                ),

                'email' => array(
                    'label' => Lang::get($this->_plugin . '.email-label'),
                ),

                'id' => array(
                    'hidden' => true
                ),
            )
        );

        $questionsInList = HContactQuestion::getAllInList(App::session()->getUser()->id);

        foreach ($questionsInList as $q) {
            $param['fields'][$q->name] = array(
                'independant' => true,
                'label' => $q->name,
                'sort' => false,
                'search' => false,
                'display' => function ($value, $field, $line){
                    $contact = HContact::getById($line->id);
                    $question = HContactQuestion::getByName($field->name, App::session()->getUser()->id);
                    $value = $contact->getProfileData($field->name);

                    if(($question->type === 'text') || ($question->type === 'textarea') || ($question->type === 'datetime')) {
                        return $value;
                    }
                    else if($question->type === 'file') {
                        return sprintf('<img src="%s" class="profile-image" />', $value ? $value : '');
                    }
                    else{
                        $parameters = json_decode($question->parameters, true);
                        if(!empty($parameters['options'])) {
                            $options = $parameters['options'];

                            if(isset($options[$value]))
                                return $options[$value];
                            else
                                return '';
                        }
                        else{
                            return '';
                        }
                    }
                },
            );
        }

        $list = new ItemList($param);

        // Manage action
        if(!$list->isRefreshing()) {
            $this->addJavaScript(Plugin::current()->getJsUrl('contact.js'));
            $this->addKeysToJavaScript($this->_plugin . '.delete-contact-confirmation');
        }

        return $list->display();
    }

    /**
     * Edit a contact
     */
    public function editContact(){

        // Set parameters for contact form
        $param = array(
            'id' => 'h-connect-contact-form',
            'model' => 'HContact',
            'reference' => array('id' => $this->contactId),
            'fieldsets' => array(
                'general' => array(

                    new TextInput(array(
                        'name' => 'lastName',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.lastName-label'),
                    )),

                    new TextInput(array(
                        'name' => 'firstName',
                        'required' => true,
                        'label' => Lang::get($this->_plugin .  '.firstName-label'),
                    )),

                    new TextInput(array(
                        'name' => 'company',
                        'label' => Lang::get($this->_plugin . '.company-label'),
                    )),

                    new TextInput(array(
                        'name' => 'job',
                        'label' => Lang::get($this->_plugin . '.job-label'),
                    )),

                    new TextInput(array(
                        'name' => 'phoneNumber',
                        'label' => Lang::get($this->_plugin . '.phoneNumber-label'),
                    )),

                    new TextInput(array(
                        'name' => 'cellNumber',
                        'label' => Lang::get($this->_plugin . '.cellNumber-label'),
                    )),

                    new TextInput(array(
                        'name' => 'personalNumber',
                        'label' => Lang::get($this->_plugin . '.personalNumber-label'),
                    )),

                    new EmailInput(array(
                        'name' => 'email',
                        'label' => Lang::get($this->_plugin . '.email-label')
                    )),

                    new TextareaInput(array(
                        'name' => 'address',
                        'rows' => '3',
                        'label' => Lang::get($this->_plugin . '.address-label')
                    )),

                    new TextInput(array(
                        'name' => 'city',
                        'label' => Lang::get($this->_plugin . '.city-label'),
                    )),

                    new TextInput(array(
                        'name' => 'postcode',
                        'label' => Lang::get($this->_plugin . '.postcode-label'),
                    )),

                    new TextInput(array(
                        'name' => 'country',
                        'label' => Lang::get($this->_plugin . '.country-label'),
                    )),

                    new HiddenInput(array(
                        'name' => 'mtime',
                        'value' => time(),
                    )),

                    new HiddenInput(array(
                        'name' => 'userId',
                        'value' => App::session()->getUser()->id,
                    )),

                    $this->contactId ? NULL : new HiddenInput(array(
                        'name' => 'ctime',
                        'value' => time(),
                    )),
                ),

                // Reserved for fields edit by customer
                'custom' => array(
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => ! $this->contactId
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.load(app.getUri("h-connect-contact-index"));'
                    ))
                ),
            ),
            'onsuccess' => 'app.load(app.getUri("h-connect-contact-index"));',
        );

        // Get all questions create by this user
        $questions = HContactQuestion::getAllByUserId(App::session()->getUser()->id);

        // Get contact associate with this contactId to manage exception
        $contact = HContact::getById($this->contactId);

        // For each question, add it to the Form
        foreach($questions as $question){
            $classname = 'Hawk\\' . ucwords($question->type) . 'Input';
            $field = json_decode($question->parameters, true);
            
            $field['name'] = urlencode($question->name);
            $field['id'] = 'h-connect-my-contact-form-' . urlencode($question->name) . '-input';
            $field['independant'] = true;
            $field['label'] = $question->name; 

            // Manage file type
            if($question->type === 'file'){
                $param['upload'] = true;

                if($contact != null){ 
                    $field['after'] = sprintf('<img src="%s" class="profile-image" />',
                            $contact->getProfileData($question->name) ? $contact->getProfileData($question->name) : ''
                        );
                }
            }

            // Be sure contact exist before search value in database
            if($contact != null){
                $field['default'] = $contact->getProfileData($question->name);
            }

            // Create new field
            $param['fieldsets']['custom'][] = new $classname($field);
        }

        $form = new Form($param);

        if(!$form->submitted()){

            $this->addCss(Plugin::current()->getCssUrl("contact.less"));

            $content = View::make(
                Plugin::current()->getView("contact-form.tpl"), array(
                    'form' => $form,
                    'displayCustom' => !empty($questions)
                )
            );

            $page = NoSidebarTab::make(array(
                'page' => array(
                    'content' => $content,
                ),
                'title' => Lang::get($this->_plugin . '.edit-contact-page-title'),
                'tabId' => 'h-connect-form-contact-page',
                'icon' => $this->getPlugin()->getFaviconUrl()
            ));

            return $form->wrap($page);
        }
        else{

            if($form->submitted() === "delete"){
                $contact = HContact::getById($this->contactId);
                if($contact->userId != App::session()->getUser()->id){
                    return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.contact-form-error-no-userId'));
                }

                // Remove all QuestionValue for this contact
                HContactValue::deleteAllByContactId($contact->id);

                // Remove directory file if it exist
                $dir = Plugin::get('h-connect')->getPublicUserfilesDir()  . 'img/id_' . $contact->id;
                if(is_dir($dir)) {
                    shell_exec('rm -r ' . $dir);
                }

                return $form->delete();
            }
            elseif($form->check()){
                try{
                    $contactId = $form->register(Form::NO_EXIT);

                    // If an error occurs, stop save data
                    if($form->status ===  'error'){
                        return $form->response(Form::STATUS_ERROR);
                    }
                    else{
                        // Contact save correctly so now save custom data
                        $contact = HContact::getById($contactId);

                        foreach($questions as $question){
                            $qname = urlencode($question->name);

                            if($question->type === 'file') {
                                $upload = Upload::getInstance($qname);

                                if($upload) {
                                    $file = $upload->getFile(0);
                                    $dir = Plugin::get('h-connect')->getPublicUserfilesDir()  . 'img/id_' . $contactId . '/';
                                    $url = Plugin::get('h-connect')->getUserfilesUrl() . 'img/id_' . $contactId . '/';
                                    
                                    // Check if directory exist
                                    if(!is_dir($dir)) {
                                        mkdir($dir, 0755, true);
                                    }

                                    $upload->move($file, $dir);
                                    $contact->setProfileData($question->name, $url . $file->basename);
                                }
                            }
                            else{
                                $value = $form->inputs[str_replace(" ", "-", $qname)]->dbvalue();

                                if($value !== null) {
                                    $contact->setProfileData($question->name, $value);
                                }
                            }
                        }
                    }

                    $contact->saveProfile();

                    return $form->response(Form::STATUS_SUCCESS);
                }
                catch(Exception $e){
                    return $form->response(Form::STATUS_ERROR);
                }

                return $form->response(Form::STATUS_SUCCESS);
            }
        }
    }

    /**
     * Delete a contact from list
     */
    public function deleteContact(){
        $contact = HContact::getById($this->contactId);

        if($contact->userId !== App::session()->getUser()->id){
            App::response()->setStatusCode(403);
            return Lang::get('main.404-message');

            throw new AppStopException();
        }

        HContactValue::deleteAllByContactId($this->contactId);

        // Remove directory file if it exist
        $dir = Plugin::get('h-connect')->getPublicUserfilesDir()  . 'img/id_' . $contact->id;
        if(is_dir($dir)) {
            shell_exec('rm -r ' . $dir);
        }

        $contact->delete();

        return $this->index();
    }

    /**
     * Export contacts selected in list or all
     */
    public function export(){

        // Create filter from contacts selected
        if(App::request()->getParams('contacts')) {
            $filter = new DBExample(array(
                'userId' => App::session()->getUser()->id,
                'id' => array(
                    '$in' => explode(',', App::request()->getParams('contacts'))
                )
            ));
        }
        else {
            $filter = new DBExample(array(
                'userId' => App::session()->getUser()->id,
            ));
        }

        // Get contacts from db
        $listContacts = HContact::getListByExample($filter, 'id', array(), array('firstName' => DB::SORT_ASC));

        // Create temporary file
        $tempFileName = 'contacts_' . uniqid() . '.csv';
        $file = fopen( Plugin::get('h-connect')->getPublicUserfilesDir() . $tempFileName, "w");

        $questions = HContactQuestion::getAll();
        $dataTitle = array(
            'lastName', 
            'firstName', 
            'job', 
            'company',
            'phoneNumber',
            'cellNumber',
            'personalNumber',
            'email',
            'address',
            'city',
            'postcode',
            'country'
        );

        foreach ($questions as $key => $q) {
            if($q->type == 'file'){
                unset($questions[$key]);
            }
            else{
              $dataTitle[] = $q->name;  
            } 
        }

        // Write title in first line
        fputcsv($file, $dataTitle, ',');

        // For each contacts add data on one line
        foreach ($listContacts as $contact){
            $data = array(
                $contact->lastName, 
                $contact->firstName, 
                $contact->job, 
                $contact->company,
                $contact->phoneNumber,
                $contact->cellNumber,
                $contact->personalNumber,
                $contact->email,
                $contact->address,
                $contact->city,
                $contact->postcode,
                $contact->country
            );

            foreach ($questions as $q) {
                $data[] = HContactValue::getValueByName($q->name, $contact->id);
            }

            fputcsv($file, $data, ',');
        }

        // Close file
        fclose($file);

        // Read data
        $data = file_get_contents(Plugin::get('h-connect')->getPublicUserfilesDir() . $tempFileName);

        // Remove remporaray file
        shell_exec('rm ' . Plugin::get('h-connect')->getPublicUserfilesDir() . $tempFileName);

        // Create response
        $response = App::response();
        $response->setContentType('text');
        $response->header('Content-Disposition', 'attachment; filename="contacts.csv"');

        return $data;
    }

    /**
     * Import Contact from 
     */
    public function importForm(){
        // Set parameters for contact form
        $param = array(
            'id' => 'h-connect-contact-import-form',
            'action' => App::router()->getUri('h-connect-contact-import'),
            'upload' => true,
            'fieldsets' => array(
                'general' => array(
                    new HtmlInput(array(
                        'name' => 'intro',
                        'value' => '<div class="alert alert-info">' . Lang::get($this->_plugin . '.import-intro-hawk-text') . '</div>'
                    )),

                    /*
                    new HtmlInput(array(
                        'name' => 'intro',
                        'value' => '<div class="alert alert-info">' . Lang::get($this->_plugin . '.import-intro-text') . '</div>'
                    )),

                    new SelectInput(array(
                        'name' => 'type',
                        'independant' => true,
                         'options' => array(
                            'hawk' => Lang::get($this->_plugin . '.type-import-google-label'),
                            'google' => Lang::get($this->_plugin . '.type-import-google-label'),
                            'outlook' => Lang::get($this->_plugin . '.type-import-outlook-label'),
                            'vCard' => Lang::get($this->_plugin . '.type-import-vCard-label'),
                        ),
                        'label' => Lang::get($this->_plugin . '.type-import-label'),
                    )),
                    */
                    new FileInput(array(
                        'name' => 'archive',
                        'label' => Lang::get($this->_plugin . '.file-contacts-label'),
                        'independant' => true,
                        'required' => true,
                        'extensions' => array('csv'),
                    )),
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'icon' => 'upload',
                        'value' => Lang::get($this->_plugin . '.import-submit-value'),
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close");app.load(app.getUri("h-connect-contact-index"));'
                    ))
                ),
            ),
            'onsuccess' => 'app.dialog("close");app.load(app.getUri("h-connect-contact-index"));',
        );

        $form = new Form($param);

        if(!$form->submitted()){
            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'title' => Lang::get($this->_plugin . ".contact-import-title"),
                'icon' => $this->getPlugin()->getFaviconUrl(),
                'page' => $form
            ));
        }
        else{
            if($form->check()){
                try{
                    // Get upload instance
                    $uploader = Upload::getInstance('archive');

                    if(!$uploader){
                        // No file was uploaded
                        throw new \Exception(Lang::get($this->_plugin . '.no-cvs-file-error'));
                    }

                    // Get csv file
                    $csv_file = $uploader->getFile()->tmpFile;

                    //$this->toUTF8($csv_file);
                    // Read it
                    $data = $this->readCSV($csv_file);

                    $firstLine = true;
                    $title = array();

                    foreach ($data as $line) {
                        if($firstLine){
                            foreach ($line as $key => $value) {
                                $title[$value] = $key;
                            }
                            $firstLine = false;
                        }
                        else{
                            /*
                            if($form->getData('type') === 'google'){
                                
                                HContact::add(array(
                                    'mtime' => time(),
                                    'ctime' => time(),
                                    'userId' => App::session()->getUser()->id,
                                    'firstName' => isset($line[$title['Given Name']]) ? $line[$title['Given Name']] : "",
                                    'lastName' => isset($line[$title['Family Name']]) ? $line[$title['Family Name']] : "",
                                    'job' => isset($line[$title['Organization 1 - Title']]) ? $line[$title['Organization 1 - Title']] : "",
                                    'company' => isset($line[$title['Organization 1 - Name']]) ? $line[$title['Organization 1 - Name']] : "",
                                    'phoneNumber' => isset($line[$title['Phone 1 - Value']]) ? $line[$title['Phone 1 - Value']] : "",
                                    'email' => isset($line[$title['E-mail 1 - Value']]) ? $line[$title['E-mail 1 - Value']] : "",
                                    'address' => isset($line[$title['Address 1 - Street']]) ? $line[$title['Address 1 - Street']] : "",
                                    'city' => isset($line[$title['Address 1 - City']]) ? $line[$title['Address 1 - City']] : "",
                                    'postcode' => isset($line[$title['Address 1 - Postal Code']]) ? $line[$title['Address 1 - Postal Code']] : "",
                                    'country' => isset($line[$title['Address 1 - Country']]) ? $line[$title['Address 1 - Country']] : ""
                                ));
                            }
                            else if($form->getData('type') === 'outlook'){
                                App::logger()->error('Import Outlook: ' . $line[0]);
                            }
                            else {
                                App::logger()->error('Import IOS: ' . $line[0]);
                            }*/

                            if(!isset($line[$title['firstName']]) && !isset($line[$title['lastName']]))
                                continue;
                            /*
                            $data = array();

                            foreach ($line as $key => $value) {
                                

                            }*/

                            

                            HContact::add(array(
                                'mtime' => time(),
                                'ctime' => time(),
                                'userId' => App::session()->getUser()->id,
                                'firstName' => isset($line[$title['firstName']]) ? $line[$title['firstName']] : "",
                                'lastName' => isset($line[$title['lastName']]) ? $line[$title['lastName']] : "",
                                'job' => isset($line[$title['job']]) ? $line[$title['job']] : "",
                                'company' => isset($line[$title['company']]) ? $line[$title['company']] : "",
                                'phoneNumber' => isset($line[$title['phoneNumber']]) ? $line[$title['phoneNumber']] : "",
                                'cellNumber' => isset($line[$title['cellNumber']]) ? $line[$title['cellNumber']] : "",
                                'personalNumber' => isset($line[$title['personalNumber']]) ? $line[$title['personalNumber']] : "",
                                'email' => isset($line[$title['email']]) ? $line[$title['email']] : "",
                                'address' => isset($line[$title['address']]) ? $line[$title['address']] : "",
                                'city' => isset($line[$title['city']]) ? $line[$title['city']] : "",
                                'postcode' => isset($line[$title['postcode']]) ? $line[$title['postcode']] : "",
                                'country' => isset($line[$title['country']]) ? $line[$title['country']] : ""
                            ));
                        }
                    }

                    return $form->response(Form::STATUS_SUCCESS);
                }
                catch(Exception $e){
                    return $form->response(Form::STATUS_ERROR);
                }
            }
        }
    }

    private function toUTF8($url) {
        if(file_exists($url)){
            $contents = file_get_contents($url);
             //if(!mb_check_encoding($url, 'UTF-8')){// Exit la fonction si c'est déjà UTF-8
            $results = mb_convert_encoding($url, 'UTF-8', 'UTF-16LE');
            $file = fopen($url, 'w+');
            fputs($file, $results);
            fclose($file);               
            return true;
            //}
        }

        return false;    
    }

    /**
     * Parse csv file and extract line by line
     */
    private function readCSV($csvFile){
        ini_set('auto_detect_line_endings',TRUE);
        $data = array();
        $row = 0;
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $data[] = $line;
                $row++;
            }
            
            fclose($handle);
        }

        ini_set('auto_detect_line_endings',FALSE);
            
        return $data;
    }

    /**********************************************
                        GROUPS
    ***********************************************/
    /**
     * Get list of all groups for a userId
     */
    public function getListGroup(){

        $list = new ItemList(array(
            'id' => 'h-connect-my-group-list',
            'model' => 'HDirectoryGroup',
            'action' => App::router()->getUri('h-connect-group-list'),
            'reference' => 'id',
            'filter' => new DBExample(array(
                'userId' => App::session()->getUser()->id,
            )),
            'controls' => array(
                array(
                    'icon' => 'plus',
                    'label' => Lang::get($this->_plugin . '.btn-add-group-label'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri('h-connect-group-edit', array('groupId' => 0)),
                    'target' => 'dialog',
                )
            ),
            'fields' => array(
                'actions' => array(
                    'independant' => true,
                    'search' => false,
                    'sort' => false,
                    'display' => function($value, $field, $group){

                        return Icon::make(array(
                            'icon' => 'wrench',
                            'size' => 'lg',
                            'href' => App::router()->getUri('h-connect-group-edit', array('groupId' => $group->id)),
                            'title' => Lang::get($this->_plugin . '.list-edit-group'),
                            'target' => 'dialog',
                        )) .

                        Icon::make(array(
                            'icon' => 'times-circle',
                            'size' => 'lg',
                            'class' => 'text-danger delete-group',
                            'data-group' => $group->id,
                            'title' => Lang::get($this->_plugin . '.list-remove-group'),
                        ));
                    }
                ),
                'name' => array(
                    'label' => Lang::get($this->_plugin . '.group-name-label'),
                    'href' => function($value, $field, $group) {
                        return App::router()->getUri('h-connect-group-edit', array('groupId' => $group->id));
                    },
                    'target' => 'dialog',
                ),

                'description' => array(
                    'label' => Lang::get($this->_plugin . '.group-description-label'),
                    'display' => function($value){
                        $maxLength = 150;
                        $value = strip_tags($value);
                        if(strlen($value) > $maxLength){
                            return substr($value, 0, $maxLength - 4) . ' ...';
                        }
                        else{
                            return $value;
                        }
                    },
                ),

                'contacts' => array(
                    'label' => Lang::get($this->_plugin . '.group-preview-label'),
                    'independant' => true,
                    'search' => false,
                    'sort' => false,
                    'display' => function($value, $filed, $group){
                        $contacts = HGroupContact::getAllContactByGroupId($group->id);

                        return View::make(Plugin::current()->getView('list-contacts-group.tpl'), array(
                            'contacts' => $contacts
                        ));
                    },
                ),

            )
        ));

        if(!$list->isRefreshing()) {
            $this->addJavaScript(Plugin::current()->getJsUrl('group.js'));
            $this->addKeysToJavaScript($this->_plugin . '.delete-group-confirmation');
        }

        return $list->display();
    }

    /**
     * Edit a group
     */
    public function editGroup(){

        $contacts = array();

        if($this->groupId)
            $contacts = HGroupContact::getAllContactByGroupId($this->groupId);

        $idForm = 'h-connect-group-form-' . uniqid();

        $param = array(
            'id' => $idForm,
            'model' => 'HDirectoryGroup',
            'reference' => array('id' => $this->groupId),
            'fieldsets' => array(
                'general' => array(
                    new HiddenInput(array(
                        "independant" => true,
                        'name' => 'contacts',
                        'default' => json_encode($contacts, JSON_NUMERIC_CHECK),
                        'attributes' => array(
                            'ko-value' => 'ko.toJSON(contacts)'
                        ),
                    )),

                    new HiddenInput(array(
                        'name' => 'userId',
                        'value' => App::session()->getUser()->id,
                    )),

                    new HiddenInput(array(
                        'name' => 'mtime',
                        'value' => time(),
                    )),

                    $this->groupId ? NULL : new HiddenInput(array(
                        'name' => 'ctime',
                        'value' => time(),
                    )),

                    new TextInput(array(
                        'name' => 'name',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.group-name-label'),
                    )),

                    new TextInput(array(
                        'name' => 'description',
                        'label' => Lang::get($this->_plugin . '.group-description-label'),
                    )),

                    new TextInput(array(
                        "name" => "search",
                        "independant" => true,
                        "label" => Lang::get($this->_plugin . '.group-form-search-label'),
                        "placeholder" => Lang::get($this->_plugin . '.group-form-search-placeholder'),
                        'attributes' => array(
                            'autocomplete' => 'off',
                            'ko-value' => 'search',
                            'ko-autocomplete' => '{source : contactAutocompleteSource, change : onContactChosen}',
                            'style' => "width: 300px;",
                        )
                    )),
                ),

                'contacts' => array(
                    new HtmlInput(array(
                        "name" => "group-contact-list-name",
                        "value" => View::make(Plugin::current()->getView('list-contacts-group-in-form.tpl'), array(
                            'contacts' => $contacts
                        ))
                    ))
                ),

                '_submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => ! $this->groupId
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close");'
                    ))
                ),
            ),
            'onsuccess' => 'app.dialog("close");app.load(app.getUri("h-connect-contact-index") + "?tabs=group");',
        );

        $form = new Form($param);

        $form->autocomplete = false;

        if(!$form->submitted()){
            $this->addJavaScriptInline("var form = app.forms['" . $idForm . "'];");
            $this->addJavascript($this->getPlugin()->getJsUrl('group-form.js'));
            $this->addCss($this->getPlugin()->getCssUrl('contact.less'));
            $this->addKeysToJavaScript($this->_plugin . '.contact-already-in-group');

            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'page' => $form,
                'title' => Lang::get($this->_plugin . '.group-form-title'),
                'icon' => 'users'
            ));
        }
        else{

            if($form->submitted() === "delete"){
                $group = HDirectoryGroup::getById($this->groupId);
                if($group->userId != App::session()->getUser()->id){
                    return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.group-form-error-no-userId'));
                }

                HGroupContact::deleteContacts($this->groupId);
                return $form->delete();
            }
            elseif($form->check()){
                try {
                    $groupId = $form->register(Form::NO_EXIT);

                    $data = json_decode($form->getData('contacts'), true);

                    HGroupContact::deleteContacts($groupId);

                    HGroupContact::saveContacts($groupId, $data);

                    return $form->response(Form::STATUS_SUCCESS);
                }
                catch (Exception $e) {
                    return $form->response(Form::STATUS_ERROR);
                }
            }
        }
    }

    /**
     * Delete a group
     */
    public function deleteGroup(){

        // Get group from database
        $group = HDirectoryGroup::getById($this->groupId);

        // Be sure group own this user
        if($group->userId !== App::session()->getUser()->id){
            App::response()->setStatusCode(403);
            return Lang::get('main.404-message');

            throw new AppStopException();
        }

        // delete allinformation about this group
        HGroupContact::deleteContacts($this->groupId);
        $group->delete();
    }

    /**
     * Autocomplete contact
     */
    public function autoCompleteContact() {
        App::response()->setContentType('json');

        $contacts = HContact::getListByExample(new DBExample(array(
            '$or' => array(
                array(
                    'CONCAT(lastName, " ", firstName)' => array(
                        '$like' => App::request()->getParams('q') . '%'
                    )
                ),
                array(
                    'CONCAT(firstName, " ", lastName)' => array(
                        '$like' => App::request()->getParams('q') . '%'
                    )
                ),
                array(
                    'company' => array(
                        '$like' => App::request()->getParams('q') . '%'
                    )
                ),
            ),
        )));

        return $contacts;
    }
}