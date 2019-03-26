<?php
namespace PHPMVC\Controllers;

use PHPMVC\LIB\FileUpload;
use PHPMVC\LIB\Helper;
use PHPMVC\LIB\InputFilter;
use PHPMVC\LIB\Messenger;
use PHPMVC\LIB\Validate;
use PHPMVC\Models\ProductCategoryModel;
use PHPMVC\Models\ProductModel;

class ProductListController extends AbstractController
{
    use InputFilter;
    use Helper;
    use Validate;

    private $_createActionRoles = [
        'CategoryId' => 'req|num',
        'Name'       => 'req|alphanum|between(3,30)',
        'Quantity'   => 'req|num',
        'BuyPrice'   => 'req|num',
        'SellPrice'  => 'req|num',
        'Unit'       => 'req|num'
    ];

    public function defaultAction()
    {
        $this->language->load('template.common');
        $this->language->load('productlist.default');

        $this->_data['products'] = ProductModel::getAll();

        $this->_view();
    }

    public function createAction()
    {
        $this->language->load('template.common');
        $this->language->load('productlist.create');
        $this->language->load('productlist.labels');
        $this->language->load('productlist.messages');
        $this->language->load('productlist.units');
        $this->language->load('validation.error');

        $this->_data['categories'] = ProductCategoryModel::getAll();

        $uploadError = false;

        if(isset($_POST['submit']) && $this->isValid($this->_createActionRoles, $_POST)){

            $product = new ProductModel();
            $product->Name  = $this->filterString($_POST['Name']);
            $product->CategoryId  = $this->fiterInteger($_POST['CategoryId']);
            $product->Quantity  = $this->fiterInteger($_POST['Quantity']);
            $product->BuyPrice  = $this->filterFloat($_POST['BuyPrice']);
            $product->SellPrice  = $this->filterFloat($_POST['SellPrice']);
            $product->Unit  = $this->fiterInteger($_POST['Unit']);
            $product->BarCode  = $this->filterString($_POST['BarCode']);

            if(!empty($_FILES['image']['name'])) {
                $uploader = new FileUpload($_FILES['image']);
                try {
                    $uploader->upload();
                    $product->Image = $uploader->getFileName();
                } catch (\Exception $e) {
                    $this->messenger->add($e->getMessage(), Messenger::APP_MESSAGE_ERROR);
                    $uploadError = true;
                }
            }

            if($uploadError === false && $product->save())
            {
                $this->messenger->add($this->language->get('message_create_success'));
                $this->redirect('/productlist');
            } else {
                $this->messenger->add($this->language->get('message_create_failed'), Messenger::APP_MESSAGE_ERROR);
            }
        }
        $this->_view();
    }

    public function editAction()
    {
        $id = $this->fiterInteger($this->_params[0]);
        $product = ProductModel::getByPk($id);

        if($product === false){
            $this->redirect('/productlist');
        }

        $this->language->load('template.common');
        $this->language->load('productlist.edit');
        $this->language->load('productlist.labels');
        $this->language->load('productlist.messages');
        $this->language->load('productlist.units');
        $this->language->load('validation.error');

        $this->_data['categories'] = ProductCategoryModel::getAll();

        $this->_data['product'] = $product;
        $uploadError = false;

        if(isset($_POST['submit']) && $this->isValid($this->_createActionRoles, $_POST)){

            $product->Name       = $this->filterString($_POST['Name']);
            $product->CategoryId = $this->fiterInteger($_POST['CategoryId']);
            $product->Quantity   = $this->fiterInteger($_POST['Quantity']);
            $product->BuyPrice   = $this->filterFloat($_POST['BuyPrice']);
            $product->SellPrice  = $this->filterFloat($_POST['SellPrice']);
            $product->Unit       = $this->fiterInteger($_POST['Unit']);
            $product->BarCode    = $this->filterString($_POST['BarCode']);

            if(!empty($_FILES['image']['name'])) {
                $uploader = new FileUpload($_FILES['image']);
                if($product->Image !== '' && file_exists(IMAGE_UPLOAD_STORAGE.DS.$product->Image) && is_writable(IMAGE_UPLOAD_STORAGE)) {
                    // Remove the old Image
                    unlink(IMAGE_UPLOAD_STORAGE.DS.$product->Image);
                }
                // Create a new Image
                $uploader = new FileUpload($_FILES['image']);
                try {
                    $uploader->upload();
                    $product->Image = $uploader->getFileName();
                } catch (\Exception $e) {
                    $this->messenger->add($e->getMessage(), Messenger::APP_MESSAGE_ERROR);
                    $uploadError = true;
                }

            }
            if($uploadError === false && $product->save())
            {
                $this->messenger->add($this->language->get('message_edit_success'));
                $this->redirect('/productlist');
            } else {
                $this->messenger->add($this->language->get('message_edit_failed'), Messenger::APP_MESSAGE_ERROR);
            }
        }
        $this->_view();
    }

    public function deleteAction()
    {
        $id = $this->fiterInteger($this->_params[0]);
        $product = ProductModel::getByPk($id);

        if($product === false){
            $this->redirect('/productlist');
        }

        $this->language->load('productlist.messages');

        if($product->delete())
        {
            if($product->Image !== '' && file_exists(IMAGE_UPLOAD_STORAGE.DS.$product->Image) && is_writable(IMAGE_UPLOAD_STORAGE)) {
                // Remove the old Image
                unlink(IMAGE_UPLOAD_STORAGE.DS.$product->Image);
            }
            $this->messenger->add($this->language->get('message_delete_success'));
        } else {
            $this->messenger->add($this->language->get('message_delete_failed'));
        }
        $this->redirect('/productlist');
    }

}