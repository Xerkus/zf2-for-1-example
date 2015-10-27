<?php
class Default_Service_Issue extends Issues_ServiceAbstract 
{
    protected $_createForm;
    protected $_aclService;

    public function __construct(Issues_Model_Mapper_DbAbstract $mapper = null)
    {
        parent::__construct($mapper);
        $this->_aclService = Zend_Registry::get('Default_DiContainer')->getAclService();
    }

    public function getCreateForm()
    {
        if (null === $this->_createForm) {
            $this->_createForm = new Default_Form_Issue_Create();
        }
        return $this->_createForm;
    }

    public function getEditForm($issue)
    {
        $form = new Default_Form_Issue_Edit();
        $form->setDefaultValues($issue);
        return $form;
    }

    public function getIssueById($id)
    {
        $issue = $this->_mapper->getIssueById($id);

        if ($this->_aclService->isAllowed($issue, 'view') || $this->_aclService->isAllowed('issue', 'view-all')) {
            return $issue;
        } else {
            return false;
        }
    }

    public function getIssueCounts()
    {
        return $this->_mapper->getIssueCounts();
    }

    public function getAllIssues()
    {
        return $this->_mapper->getAllIssues();
    }

    public function getIssuesByProject($project)
    {
        return $this->_mapper->getIssuesByProject($project);
    }

    public function getIssuesByMilestone($milestone, $status = null)
    {
        return $this->_mapper->getIssuesByMilestone($milestone, $status);
    }

    public function filterIssues($status, $assignee = false)
    {
        if ($assignee instanceof Default_Model_User) {
            $assignee = $assignee->getUserId();
        }

        return $this->_mapper->filterIssues($status, $assignee);
    }

    public function createFromForm(Default_Form_Issue_Create $form)
    {
        $acl = Zend_Registry::get('Default_DiContainer')->getAclService();
        if (!$acl->isAllowed('issue', 'create')) {
            return false;
        }

        $permissions = $form->getValue('permissions');

        $issue = new Default_Model_Issue();
        $issue->setTitle($form->getValue('title'))
            ->setDescription($form->getValue('description'))
            ->setStatus('open')
            ->setProject($form->getValue('project'))
            ->setCreatedBy(Zend_Auth::getInstance()->getIdentity())
            ->setAssignedTo($form->getValue('assigned_to'))
            ->setPrivate($permissions['private'] ? true : false);
        $issueId = $this->_mapper->save($issue);
        $issue->setIssueId($issueId);

        $milestones = $form->getValue('milestones');
        if ($milestones) {
            foreach ($milestones as $i) {
                Zend_Registry::get('Default_DiContainer')
                    ->getMilestoneService()
                    ->addIssueToMilestone($i, $issue, false);
            }
        }

        $labels = $form->getValue('labels');
        if ($labels) {
            foreach ($labels as $i) {
                $this->addLabelToIssue($issue, $i, false);
            }
        }

        if ($permissions['private']) {
            Zend_Registry::get('Default_DiContainer')->getAclService()
                ->addResourceRecord($permissions['roles'], 'issue', $issue);
        }

        return $issue;
    }

    public function updateFromForm(Default_Form_Issue_Edit $form, $issueId)
    {
        $issue = $this->getIssueById($issueId);
        if (!$issue) {
            return false;
        }

        if (!$this->canEditIssue($issue)) {
            return false;
        }

        $issue->setTitle($form->getValue('title'))
            ->setDescription($form->getValue('description'))
            ->setProject($form->getValue('project'))
            ->setStatus($form->getValue('status'))
            ->setAssignedTo($form->getValue('assigned_to'))
            ->setPrivate($form->getSubform('permissions')->getElement('private')->isChecked());
        $result = $this->_mapper->save($issue);

        $labels = $form->getValue('labels');
        $this->_mapper->updateIssueLabels($issue, $labels, true);

        $milestones = $form->getValue('milestones');
        $this->_mapper->updateIssueMilestones($issue, $milestones, true);

        $this->_mapper->clearIssueResourceRecords($issue);

        if ($issue->isPrivate()) {
            Zend_Registry::get('Default_DiContainer')
                ->getAclService()
                ->addResourceRecord(
                    $form->getSubform('permissions')->getElement('roles')->getValue(),
                    'issue',
                    $issue->getIssueId());
        }

        if ($result === false) {
            return false;
        } else if ($result === 0) {
            return true;
        }

        return $result;
    }

    public function canEditIssue(Default_Model_Issue $issue)
    {
        $acl = Zend_Registry::get('Default_DiContainer')->getAclService();
        if ($acl->isAllowed('issue', 'edit-all')) {
            return true;
        }

        $user = Zend_Registry::get('Default_DiContainer')->getUserService()
            ->getIdentity();

        if ($acl->isAllowed('issue', 'edit-own')) {
            if ($issue->getAssignedTo() instanceof Default_Model_User && $issue->getAssignedTo()->getUserId() == $user->getUserId()) {
                return true;
            }

            if ($issue->getCreatedBy()->getUserId() == $user->getUserId()) {
                return true;
            }
        }

        return false;
    }

    public function addLabelToIssue($issue, $label, $audit = true)
    {
        if (!($issue instanceof Default_Model_Issue)) {
            $issue = $this->_mapper->getIssueById($issue);
        }

        if (!$this->canLabelIssue($issue)) {
            return false;
        }

        if (!($label instanceof Default_Model_Label)) {
            $label = Zend_Registry::get('Default_DiContainer')->getLabelService()->getLabelDetect($label);
        }

        $this->_mapper->addLabelToIssue($issue, $label, $audit);
    }

    public function removeLabelFromIssue($issue, $label)
    {
        if (!($issue instanceof Default_Model_Issue)) {
            $issue = $this->_mapper->getIssueById($issue);
        }

        if (!$this->canLabelIssue($issue)) {
            return false;
        }

        if (!($label instanceof Default_Model_Label)) {
            $label = Zend_Registry::get('Default_DiContainer')->getLabelService()->getLabelById($label);
        }

        $this->_mapper->removeLabelFromIssue($issue, $label);
    }

    public function countIssuesByLabel(Default_Model_Label $label)
    {
        return $this->_mapper->countIssuesByLabel($label);
    }

    public function canLabelIssue($issue)
    {
        $acl = Zend_Registry::get('Default_DiContainer')->getAclService();
        if ($acl->isAllowed('issue', 'label-all')) {
            return true;
        }

        $identity = Zend_Registry::get('Default_DiContainer')->getUserService()
            ->getIdentity();

        if ($acl->isAllowed('issue', 'label-own')) {
            if ($identity->getUserId() == $issue->getCreatedBy()->getUserId()) {
                return true;
            }
        }

        return false;
    }
}

