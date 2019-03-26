<?php
namespace PHPMVC\Controllers;

use PHPMVC\LIB\FileUpload;
use PHPMVC\LIB\Helper;
use PHPMVC\LIB\InputFilter;
use PHPMVC\LIB\Messenger;
use PHPMVC\LIB\Validate;
use PHPMVC\Models\ProductCategoryModel;

class ProductCategoriesController extends AbstractController
{
    use InputFilter;
    use Helper;
    use Validate;

    private $_createActionRoles = [
        'Name'       => 'req|alphanum|between(3,30)'
    ];

    public function defaultAction()
    {
        $this->language->load('template.common');
        $this->language->load('productcategories.default');

        $this->_data['categories'] = ProductCategoryModel::getAll();

        $this->_view();
    }

    public function createAction()
    {
        $this->language->load('template.common');
        $this->language->load('productcategories.create');
        $this->language->load('productcategories.labels');
        $this->language->load('productcategories.messages');
        $this->language->load('validation.error');

        $uploadError = false;

        if(isset($_POST['submit']) && $this->isValid($this->_createActionRoles, $_POST)){

            $category = new ProductCategoryModel();
            $category->Name  = $this->filterString($_POST['Name']);
            if(!empty($_FILES['image']['name'])) {
                $uploader = new FileUpload($_FILES['image']);
                try {
                    $uploader->upload();
                    $category->Image = $uploader->getFileName();
                } catch (\Exception $e) {
                    $this->messenger->add($e->getMessage(), Messenger::APP_MESSAGE_ERROR);
                    $uploadError = true;
                }
            }

            if($uploadError === false && $category->save())
            {
                $this->messenger->add($this->language->get('message_create_success'));
                $this->redirect('/productcategories');
            } else {
                $this->messenger->add($this->language->get('message_create_failed'), Messenger::APP_MESSAGE_ERROR);
            }
        }
        $this->_view();
    }

    public function editAction()
    {
        $id = $this->fiterInteger($this->_params[0]);
        $category = ProductCategoryModel::getByPk($id);

        if($category === false){
            $this->redirect('/productcategories');
        }

        $this->language->load('template.common');
        $this->language->load('productcategories.edit');
        $this->language->load('productcategories.labels');
        $this->language->load('productcategories.messages');
        $this->language->load('validation.error');

        $this->_data['category'] = $category;
        $uploadError = false;

        if(isset($_POST['submit']) && $this->isValid($this->_createActionRoles, $_POST)){

            $category->Name  = $this->filterString($_POST['Name']);
            if(!empty($_FILES['image']['name'])) {
                $uploader = new FileUpload($_FILES['image']);
                if($category->Image !== '' && file_exists(IMAGE_UPLOAD_STORAGE.DS.$category->Image) && is_writable(IMAGE_UPLOAD_STORAGE)) {
                    // Remove the old Image
                    unlink(IMAGE_UPLOAD_STORAGE.DS.$category->Image);
                }
                // Create a new Image
                $uploader = new FileUpload($_FILES['image']);
                try {
                    $uploader->upload();
                    $category->Image = $uploader->getFileName();
                } catch (\Exception $e) {
                    $this->messenger->add($e->getMessage(), Messenger::APP_MESSAGE_ERROR);
                    $uploadError = true;
                }

            }
            if($uploadError === false && $category->save())
            {
                $this->messenger->add($this->language->get('message_edit_success'));
                $this->redirect('/productcategories');
            } else {
                $this->messenger->add($this->language->get('message_edit_failed'), Messenger::APP_MESSAGE_ERROR);
            }
        }
        $this->_view();
    }

    public function deleteAction()
    {
        $id = $this->fiterInteger($this->_params[0]);
        $category = ProductCategoryModel::getByPk($id);

        if($category === false){
            $this->redirect('/productcategories');
        }

        $this->language->load('productcategories.messages');

        if($category->delete())
        {
            if($category->Image !== '' && file_exists(IMAGE_UPLOAD_STORAGE.DS.$category->Image)) {
                // Remove the old Image
                unlink(IMAGE_UPLOAD_STORAGE.DS.$category->Image);
            }
            $this->messenger->add($this->language->get('message_delete_success'));
        } else {
            $this->messenger->add($this->language->get('message_delete_failed'));
        }
        $this->redirect('/productcategories');
    }

}