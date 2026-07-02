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

        if (trim(Tools::getValue('website')) !== '') {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'page', ['submitted' => 1])
            );
        }

        if (!Tools::getValue('terms_accepted') || !Tools::getValue('privacy_accepted')) {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'page', ['error' => 'terms'])
            );
        }

        $recaptchaSecret = Configuration::get('INQUIRY_RECAPTCHA_SECRET_KEY');
        if ($recaptchaSecret && !$this->verifyRecaptcha($recaptchaSecret, Tools::getValue('g-recaptcha-response'))) {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'page', ['error' => 'recaptcha'])
            );
        }

        $inquiry = new InquiryBox();
        $inquiry->email = trim(Tools::getValue('email'));
        $inquiry->title = trim(Tools::getValue('title'));
        $inquiry->content = trim(Tools::getValue('content'));
        $inquiry->approved = (bool) Configuration::get('INQUIRY_AUTO_APPROVE');
        $inquiry->admin_reply = '';

        if ($inquiry->validateFields(false, true) !== true) {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'page', ['error' => 'validation'])
            );
        }

        if (!$inquiry->save()) {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'page', ['error' => 'save'])
            );
        }

        Tools::redirect(
            $this->context->link->getModuleLink($this->module->name, 'page', ['submitted' => 1])
        );
    }

    private function verifyRecaptcha($secret, $response)
    {
        if (!$response) {
            PrestaShopLogger::addLog('Inquiry reCAPTCHA: no g-recaptcha-response token was submitted', 2);

            return false;
        }

        $result = Tools::file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
                'secret' => $secret,
                'response' => $response,
                'remoteip' => Tools::getRemoteAddr(),
            ])
        );

        if ($result === false) {
            PrestaShopLogger::addLog('Inquiry reCAPTCHA: siteverify HTTP request failed (network/SSL on this server)', 3);

            return false;
        }

        $data = json_decode($result, true);
        if (empty($data['success'])) {
            PrestaShopLogger::addLog('Inquiry reCAPTCHA: verification failed, response: ' . $result, 2);

            return false;
        }

        return true;
    }
}
