<?php

class Default_ExampleController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->getResponse()
            ->appendBody(Zend_Registry::get('service_manager')->get('exampleservice'));
        $this->_helper->viewRenderer->setNoRender(true);
    }
}
