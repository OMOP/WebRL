<?php
/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    10 Dec 2010

    Controller plugin that sets page title automatically

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

    Licensed under the Apache License, Version 2.0 (the "License"); you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.

    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.

==============================================================================*/

class Application_Plugin_PageTitle extends Zend_Controller_Plugin_Abstract
{

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (!$request->isDispatched())
            return;

        $frontInstance = Zend_Controller_Front::getInstance();
        $bootstrap = $frontInstance->getParam('bootstrap');
        $view = $bootstrap->getResource('view');

        $activePage = $view->navigation()->findOneBy('active', true);
        if ($activePage) {
            $title = $activePage->getTitle();
            $view->headTitle()->append($title);
            $view->placeholder('pageTitle')->append($title);
        }
    }

}