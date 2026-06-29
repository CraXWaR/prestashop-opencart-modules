<?php
class AdminInquiryController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function init()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminModules') . '&configure=inquiry'
        );
    }
}