<?php
class GuestbookController extends Zend_Controller_Action 
{
    public function indexAction()
    {
        $guestbook = new Default_Model_Guestbook();
        $this->view->entries = $guestbook->fetchAll();
    }

    public function signAction()
    {
        $request = $this->getRequest();
        $form    = new Default_Form_Guestbook();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $model = new Default_Model_Guestbook($form->getValues());
                $model->save();
                return $this->_helper->redirector('index');
            }
        }
        
        $this->view->form = $form;
    }
}
