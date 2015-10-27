<?php
class Default_IssuesController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_issueService = Zend_Registry::get('Default_DiContainer')->getIssueService();
        $this->_userService = Zend_Registry::get('Default_DiContainer')->getUserService();
        $this->_commentService = Zend_Registry::get('Default_DiContainer')->getCommentService();

        $this->view->user = $this->_userService->getIdentity();
    }

    public function newAction()
    {
        $fm = $this->getHelper('FlashMessenger')->setNamespace('createForm')->getMessages(); 
        $this->view->createForm = (count($fm) > 0) ? $fm[0] : $this->getCreateForm();
    }

    public function postAction()
    {
        $fm = $this->getHelper('FlashMessenger')->setNamespace('createForm')->getMessages(); 
        $this->view->createForm = (count($fm) > 0) ? $fm[0] : $this->getCreateForm();

        $form = $this->view->createForm;
        $request = $this->getRequest();
        if (!$request->isPost()) return $this->_helper->redirector('new');
        if (false === $form->isValid($request->getPost())) {
            $form->setDescription($this->view->translate('new_issue_failed'));
            $this->_helper->FlashMessenger->setNamespace('createForm')->addMessage($form);
            $this->_helper->redirector('new');
        }
        if (!$this->_issueService->createFromForm($form)) {
            $form->setDescription($this->view->translate('new_issue_failed'));
            $this->_helper->FlashMessenger->setNamespace('createForm')->addMessage($form);
            return $this->_helper->redirector('new');
        }
        return $this->_helper->redirector('list', 'issues');
    }

    public function editAction()
    {
        $fm = $this->getHelper('FlashMessenger')->setNamespace('editForm')->getMessages();
        if (count($fm) > 0) {
            $this->view->editForm = $fm[0];
        } else {
            $issue = $this->_issueService->getIssueById($this->_getParam('id'));
            $this->view->editForm = $this->getEditForm($issue);
        }
    }

    public function updateAction()
    {
        $fm = $this->getHelper('FlashMessenger')->setNamespace('editForm')->getMessages();
        if (count($fm) > 0) {
            $form = $fm[0];
        } else {
            $issue = $this->_issueService->getIssueById($this->_getParam('id'));
            $form = $this->getEditForm($issue);
        }

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->_helper->redirector
                ->gotoSimple('edit', null, null, array('id' => $this->_getParam('id')));
        }

        if (false === $form->isValid($request->getPost())) {
            $form->setDescription($this->view->translate('edit_issue_failed'));
            $this->_helper->FlashMessenger->setNamespace('editForm')->addMessage($form);
            return $this->_helper->redirector
                ->gotoSimple('edit', null, null, array('id' => $this->_getParam('id')));
        }

        if (!$this->_issueService->updateFromForm($form, $this->_getParam('id'))) {
            $form->setDescription($this->view->translate('edit_issue_failed'));
            $this->_helper->FlashMessenger->setNamespace('editForm')->addMessage($form);
            return $this->_helper->redirector
                ->gotoSimple('edit', null, null, array('id' => $this->_getParam('id')));
        }

        return $this->_helper->redirector
            ->gotoSimple('view', null, null, array('id' => $this->_getParam('id')));
    }

    public function viewAction()
    {
        $this->_commentService = Zend_Registry::get('Default_DiContainer')->getCommentService();
        $this->view->issue = $this->_issueService->getIssueById($this->_getParam('id'));
        if ($this->view->issue == false) {
            return $this->_helper->redirector('list', 'issues');
        }

        if ($this->_issueService->canEditIssue($this->view->issue)) {
            $this->view->canEdit = true;
        } else {
            $this->view->canEdit = false;
        }

        $this->view->comments = $this->_commentService->getCommentsByIssue($this->view->issue);

        $fm = $this->getHelper('FlashMessenger')->setNamespace('commentForm')->getMessages(); 
        $this->view->commentForm = (count($fm) > 0) ? $fm[0] : $this->getCommentForm();
    }

    public function addCommentAction()
    {
        $fm = $this->getHelper('FlashMessenger')->setNamespace('commentForm')->getMessages(); 
        $form = (count($fm) > 0) ? $fm[0] : $this->getCommentForm();
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->_helper
                ->redirector->gotoSimple('view', 'issues', 'default', array('id' => $this->_getParam('id')));
        }

        if (false === $form->isValid($request->getPost())) {
            $form->setDescription('Could not add comment');
            $this->_helper->FlashMessenger->setNamespace('commentForm')->addMessage($form);
            return $this->_helper
                ->redirector->gotoSimple('view', 'issues', 'default', array('id' => $this->_getParam('id')));
        }
        if (!$this->_commentService->createFromForm($form, $this->_getParam('id'))) {
            $form->setDescription('Could not add comment');
            $this->_helper->FlashMessenger->setNamespace('commentForm')->addMessage($form);
            return $this->_helper
                ->redirector->gotoSimple('view', 'issues', 'default', array('id' => $this->_getParam('id')));
        }

        return $this->_helper
            ->redirector->gotoSimple('view', 'issues', 'default', array('id' => $this->_getParam('id')));
    }

    public function editCommentAction()
    {
        $fm = $this->getHelper('FlashMessenger')->setNamespace('editCommentForm')->getMessages();
        if (count($fm) > 0) {
            $this->view->form = $fm[0];
        } else {
            $comment = $this->_commentService->getCommentById($this->_getParam('id'));
            $this->view->form = $this->getEditCommentForm($comment);
        }
    }

    public function deleteCommentAction()
    {
        $comment = $this->_commentService->getCommentById($this->_getParam('id'));
        $this->_commentService->deleteComment($comment);

        return $this->_helper->redirector
            ->gotoSimple('view', 'issues', 'default', array('id' => $comment->getIssue()->getIssueId()));
    }

    public function updateCommentAction()
    {
        $comment = $this->_commentService->getCommentById($this->_getParam('id'));
        $fm = $this->getHelper('FlashMessenger')->setNamespace('editCommentForm')->getMessages();
        if (count($fm) > 0) {
            $form = $fm[0];
        } else {
            $form = $this->getEditCommentForm($comment);
        }

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->_helper->redirector
                ->gotoSimple('edit-comment', null, null, array('id' => $this->_getParam('id')));
        }

        if (false === $form->isValid($request->getPost())) {
            $form->setDescription($this->view->translate('edit_comment_failed'));
            $this->_helper->FlashMessenger->setNamespace('editCommentForm')->addMessage($form);
            return $this->_helper->redirector
                ->gotoSimple('edit-comment', null, null, array('id' => $this->_getParam('id')));
        }

        if (!$this->_commentService->updateFromForm($form, $comment)) {
            $form->setDescription($this->view->translate('edit_comment_failed'));
            $this->_helper->FlashMessenger->setNamespace('editCommentForm')->addMessage($form);
            return $this->_helper->redirector
                ->gotoSimple('edit-comment', null, null, array('id' => $this->_getParam('id')));
        }

        return $this->_helper->redirector
            ->gotoSimple('view', null, null, array('id' => $comment->getIssue()->getIssueId()));
    }

    public function listAction()
    {
        $this->view->issues = $this->_issueService->getAllIssues();
    }

    public function getCommentForm()
    {
        $form = $this->_commentService->getCreateForm();
        if ($form) {
            return $form->setAction(
                $this->_helper->url->simple('add-comment', null, null, array('id' => $this->_getParam('id')))
            );
        }

        return false;
    }

    public function getEditCommentForm($comment)
    {
        return $this->_commentService->getEditForm($comment)
            ->setAction($this->_helper->url->simple('update-comment', 'issues', 'default', array('id' => $this->_getParam('id'))));
    }

    public function getCreateForm()
    {
        return $this->_issueService->getCreateForm()->setAction($this->_helper->url->direct('post'));
    }

    public function getEditForm($issue)
    {
        return $this->_issueService->getEditForm($issue)
            ->setAction($this->_helper->url->simple('update', 'issues', 'default', array('id' => $this->_getParam('id'))));
    }
}
