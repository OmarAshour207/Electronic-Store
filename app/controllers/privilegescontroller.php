<?php
namespace PHPMVC\Controllers;

use PHPMVC\LIB\Helper;
use PHPMVC\LIB\InputFilter;
use PHPMVC\Models\PrivilegeModel;
use PHPMVC\Models\UserGroupPrivilegeModel;

class PrivilegesController extends AbstractController
{
    use InputFilter;
    use Helper;

    public function defaultAction()
    {
        $this->language->load('template.common');
        $this->language->load('privileges.default');

        $this->_data['privileges'] = PrivilegeModel::getAll();

        $this->_view();
    }
    // TODO: wwe need to implement csrf(croos site request forgery) prevention
    public function createAction()
    {
        $this->language->load('template.common');
        $this->language->load('privileges.labels');
        $this->language->load('privileges.create');

        if(isset($_POST['submit'])){
            $privilege = new PrivilegeModel();
            $privilege->PrivilegeTitle = $this->filterString($_POST['PrivilegeTitle']);
            $privilege->Privilege = $this->filterString($_POST['Privilege']);
            var_dump($privilege);
            if($privilege->save())
            {
                $this->messenger->add('تم حفظ الصلاحية بنجاح');
                $this->redirect('/privileges');
            }
        }

        $this->_view();
    }

    public function editAction()
    {
        $id = $this->fiterInteger($this->_params[0]);
        $privilege = PrivilegeModel::getByPk($id);

        if($privilege === false){
            $this->redirect('/privileges');
        }

        $this->_data['privilege'] = $privilege;

        $this->language->load('template.common');
        $this->language->load('privileges.labels');
        $this->language->load('privileges.edit');

        if(isset($_POST['submit'])){
            $privilege->PrivilegeTitle = $this->filterString($_POST['PrivilegeTitle']);
            $privilege->Privilege = $this->filterString($_POST['Privilege']);
            if($privilege->save())
            {
                $this->messenger->add('تم تعديل الصلاحية بنجاح');
                $this->redirect('/privileges');
            }
        }
        $this->_view();
    }

    public function deleteAction()
    {
        $id = $this->fiterInteger($this->_params[0]);
        $privilege = PrivilegeModel::getByPk($id);

        if($privilege === false){
            $this->redirect('/privileges');
        }

        $groupPrivileges = UserGroupPrivilegeModel::getBy(['PrivilegeId' => $privilege->PrivilegeId]);
        if(false !== $groupPrivileges){
            foreach ($groupPrivileges as $groupPrivilege){
                $groupPrivilege->delete();
            }
        }

        if($privilege->delete()) {
        $this->redirect('/privileges');
        }
    }
}