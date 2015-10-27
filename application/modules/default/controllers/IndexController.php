<?php
class Default_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        // Get a list of projects from the DB
        $this->_projectService = Zend_Registry::get('Default_DiContainer')->getProjectService();
        $this->view->projects = $this->_projectService->getAllProjects();
        
        $this->_issueService = Zend_Registry::get('Default_DiContainer')->getIssueService();
        $this->_labelService = Zend_Registry::get('Default_DiContainer')->getLabelService();
        $this->_userService = Zend_Registry::get('Default_DiContainer')->getUserService();
        $this->_milestoneService = Zend_Registry::get('Default_DiContainer')->getMilestoneService();
        $this->_aclService = Zend_Registry::get('Default_DiContainer')->getAclService();

        $this->view->user = $this->_userService->getIdentity();
        $this->view->headScript()->appendFile('/js/colorpicker/jquery.colorPicker.js');
        $this->view->headScript()->appendFile('/js/colorpicker-zf.js');
        $this->view->headLink()->appendStylesheet('/js/colorpicker/colorPicker.css');
    }

    public function indexAction()
    {
        $this->view->labels = $this->_labelService->getAllLabels();

        if ($this->_hasParam('mine')) {
            $this->view->openIssues = $this->_issueService->filterIssues('open', $this->view->user);
            $this->view->closedIssues = $this->_issueService->filterIssues('closed', $this->view->user);
        } else if ($this->_hasParam('unassigned')) {
            $this->view->openIssues = $this->_issueService->filterIssues('open', 0);
            $this->view->closedIssues = $this->_issueService->filterIssues('closed', 0);
        } else {
            $this->view->openIssues = $this->_issueService->filterIssues('open');
            $this->view->closedIssues = $this->_issueService->filterIssues('closed');
        }


        $this->view->milestones = $this->_milestoneService->getAllMilestones();

        $this->view->createLabelForm = $this->getCreateLabelForm();

        $this->view->labelsSelect = $this->_labelService->getLabelsForSelect($this->view->labels);

        $this->view->usersSelect = $this->_userService->getUsersForSelect();

        $this->view->milestonesSelect = $this->_milestoneService->getMilestonesForSelect($this->view->milestones);
    }

    public function getCreateLabelForm()
    {
        $fm = $this->getHelper('FlashMessenger')->getMessages(); 
        $form = (count($fm) > 0) ? $fm[0] : $this->_labelService->getCreateForm();

        if ($form) {
            return $form->setAction($this->_helper->url->direct('post','labels'));
        } else {
            return false;
        }
    }
}
