<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'inquiry/classes/InquiryBox.php';

class InquirySubmitModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Tools::isSubmit('submit_inquiry')) {
            return;
        }

        $inquiry = new InquiryBox();
        $inquiry->email = trim(Tools::getValue('email'));
        $inquiry->title = trim(Tools::getValue('title'));
        $inquiry->content = trim(Tools::getValue('content'));
        $inquiry->approved = (bool) Configuration::get('INQUIRY_AUTO_APPROVE');
        $inquiry->admin_reply = '';

        if ($inquiry->validateFields(false, true) !== true || !$inquiry->save()) {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'page', ['error' => 1])
            );
        }

        Tools::redirect(
            $this->context->link->getModuleLink($this->module->name, 'page', ['submitted' => 1])
        );
    }
}
