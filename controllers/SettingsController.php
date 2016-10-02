<?php

/**
 * SettingsController.class.php
 */

namespace Hawk\Plugins\HConnect;

class SettingsController extends Controller{

    /**
    * Manage question
    */
    public function listQuestions(){

        // Create parameters for the list to display
        $paramList = array(
            'id' => 'h-connect-contact-questions-list',
            'model' => 'HContactQuestion',
            'filter' => new DBExample(array(
                'userId' => App::session()->getUser()->id,
            )),
            'action' => App::router()->getUri('h-connect-contact-questions'),
            'navigation' => false,
            'sort' => array('order' => DB::SORT_ASC),
            'controls' => array(
                array(
                    'name' => 'new-question',
                    'label' => Lang::get($this->_plugin . '.new-question-btn'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri('h-connect-edit-contact-question', array('name' => '_new')),
                    'target' => 'dialog',
                    'icon' => 'plus'
                ),
            ),
            'fields' => array(
                'actions' => array(
                    'independant' => true,
                    'display' => function ($value, $field, $line) {
                        $nameEncoded = urlencode($line->name);
                        return Icon::make(array(
                            'icon' => 'pencil',
                            'class' => 'text-info',
                            'size' => 'lg',
                            'href' => App::router()->getUri('h-connect-edit-contact-question', array('name' => $nameEncoded)),
                            'target' => 'dialog',
                            'title' => Lang::get($this->_plugin . '.edit-contact-question')
                        )) .
                        Icon::make(array(
                            'icon' => 'times-circle',
                            'size' => 'lg',
                            'class' => 'text-danger delete-contact-question',
                            'data-question' => $nameEncoded,
                            'title' => Lang::get($this->_plugin . '.delete-contact-question')
                        ));
                    },
                    'sort' => false,
                    'search' => false,
                ),
                'name' => array(
                    'label' => Lang::get($this->_plugin . '.name-question-label'),
                ),
                'type' => array(
                    'label' => Lang::get($this->_plugin . '.type-question-label'),
                    'sort' => false,
                    'search' => false,
                    'display' => function ($value, $field, $line) {
                        return Lang::get('admin.profile-question-form-type-' . $value);
                    },
                ),
                
                'displayInList' => array(
                    'label' => Lang::get($this->_plugin . '.displayInList-question-label'),
                    'sort' => false,
                    'search' => false,
                    'display' => function ($value, $field, $line) {
                        return Lang::get($this->_plugin . '.displayInList-label-' . $value);
                    },
                ),    
            ),
        );

        // Create List
        $list = new ItemList($paramList);

        if(!$list->isRefreshing()) {

            $this->addJavaScript(Plugin::current()->getJsUrl('settings.js'));

            $this->addKeysToJavaScript($this->_plugin . '.delete-contact-question-confirmation');

            return View::make(
                Plugin::current()->getView("questions-list.tpl"), array(
                    'list' => $list,
                )
            );
        }
        else {
            return $list->display();
        }
    }

    /**
     * Edit a profile question
     */
    public function edit(){

        $allowedTypes = ProfileQuestion::$allowedTypes;

        $param = array(
            'id' => 'h-contact-question-form',
            'model' => 'HContactQuestion',
            'reference' => array('name' => urldecode($this->name)),
            'labelWidth' => '200px',
            'fieldsets' => array(
                'general' => array(
                    'legend' => Lang::get($this->_plugin . '.contact-question-form-general-legend'),

                    new TextInput(array(
                        'name' => 'name',
                        'maxlength' => 64,
                        'label' =>  Lang::get($this->_plugin . '.contact-question-form-name-label'),
                        'required' => true,
                    )),

                    new SelectInput(array(
                        'name' => 'type',
                        'required' => true,
                        'options' => array_combine($allowedTypes, array_map(function ($type) {
                            return Lang::get('admin.profile-question-form-type-' . $type);
                        }, $allowedTypes)),
                        'label' => Lang::get($this->_plugin . '.contact-question-form-type-label'),
                        'attributes' => array(
                            'ko-value' => 'type',
                        )
                    )),

                    new CheckboxInput(array(
                        'name' => 'displayInList',
                        'label' => Lang::get($this->_plugin . '.contact-question-form-inList-label'),
                    )),

                    new HiddenInput(array(
                        'name'  => 'userId',
                        'value' => App::session()->getUser()->id,
                    )),
                ),

                'parameters' => array(
                    'legend' => Lang::get($this->_plugin . '.contact-question-form-parameters-legend'),

                    new ObjectInput(array(
                        'name' => 'parameters',
                        'id' => 'question-form-parameters',
                        'hidden' => true,
                        'attributes' => array(
                            'ko-value' => 'parameters'
                        )
                    )),

                    new TextareaInput(array(
                        'name' => 'options',
                        'independant' => true,
                        'required' => App::request()->getBody('type') == 'select' || App::request()->getBody('type') == 'radio',
                        'label' =>  Lang::get($this->_plugin . '.contact-question-form-options-label'),
                        'labelClass' => 'required',
                        'attributes' => array(
                            'ko-value' => "options",
                        ),
                        'cols' => 20,
                        'rows' => 10
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
                        'notDisplayed' => $this->name == '_new'
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                )

            ),
            'onsuccess' => 'app.dialog("close");app.load(app.getUri("h-connect-contact-index") + "?tabs=settings");',
        );

        $form = new Form($param);

        if(!$form->submitted()) {
            $content = View::make(Plugin::current()->getView("question-contact-form.tpl"), array(
                'form' => $form
            ));

            return View::make(Theme::getSelected()->getView("dialogbox.tpl"), array(
                'title' => Lang::get($this->_plugin . ".contact-questions-title"),
                'icon' => 'cogs',
                'page' => $content
            ));
        }
        else{
            if($form->submitted() === "delete"){

                // Remove all QuestionValue for this question
                HContactValue::deleteAllByQuestionName($this->name, App::session()->getUser()->id);

                return $form->delete();
            }
            else{
                if($form->check()){ 

                    if($this->name === '_new'){
                        $exist = HContactQuestion::getByName($form->getData("name"), App::session()->getUser()->id);
                        if($exist)
                            return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . ".contact-question-already-exist"));
                    }

                    return $form->register();
                }
            }
        }
    }

    public function deleteQuestion(){

        $qname = urldecode($this->name);
        
        $question = HContactQuestion::getByName($qname, App::session()->getUser()->id);

        if($question){
            HContactValue::deleteAllByQuestionName($qname, App::session()->getUser()->id);
            $question->delete();
        }
        else{
            App::response()->setStatusCode(403);
            return Lang::get('main.404-message');

            throw new AppStopException();
        }
    }
}