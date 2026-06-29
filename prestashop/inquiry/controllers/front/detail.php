<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class InquiryDetailModuleFrontController  extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $db = Db::getInstance();
        $idInquiry = (int) Tools::getValue('id');

        $inquiry = $idInquiry ? $db->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'inquiry`
            WHERE id_inquiry = ' . $idInquiry . ' AND approved = 1'
        ) : false;

        // Unknown or unapproved inquiry — send the visitor back to the list
        if (!$inquiry) {
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'page'));
        }

        $inquiry['products'] = $db->executeS(
            'SELECT cp.id_product, pl.name, l.id_image, l.legend
            FROM `' . _DB_PREFIX_ . 'inquiry_product` cp
            INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (cp.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` l ON (l.id_image = (
                SELECT i.id_image FROM `' . _DB_PREFIX_ . 'image` i
                WHERE i.id_product = cp.id_product AND i.cover = 1 LIMIT 1
            ) AND l.id_lang = ' . (int) $this->context->language->id . ')
            WHERE cp.id_inquiry = ' . $idInquiry
        );

        foreach ($inquiry['products'] as &$product) {
            $product['url'] = $this->context->link->getProductLink((int) $product['id_product']);
            $product['image_url'] = $product['id_image']
                ? $this->context->link->getImageLink('product', $product['id_product'] . '-' . $product['id_image'])
                : null;
        }
        unset($product);

        $inquiry['employee_name'] = null;
        if ((int) $inquiry['id_employee']) {
            $emp = $db->getRow(
                'SELECT firstname, lastname FROM `' . _DB_PREFIX_ . 'employee`
                WHERE id_employee = ' . (int) $inquiry['id_employee']
            );
            if ($emp) {
                $inquiry['employee_name'] = trim($emp['firstname'] . ' ' . $emp['lastname']);
            }
        }

        $this->context->smarty->assign([
            'inquiry' => $inquiry,
            'back_url' => $this->context->link->getModuleLink($this->module->name, 'page'),
        ]);

        $this->setTemplate('module:inquiry/views/templates/front/inquiry.tpl');
    }
}
