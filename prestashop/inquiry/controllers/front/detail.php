<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class InquiryDetailModuleFrontController  extends ModuleFrontController
{
    private $metaTitle = '';
    private $metaDescription = '';

    public function initContent()
    {
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

        $metaTitle = str_replace(
            ['%title%', '%shop_name%'],
            [$inquiry['title'], $this->context->shop->name],
            Configuration::get('INQUIRY_SEO_META_TITLE')
        );
        $metaDescription = str_replace(
            ['%title%', '%shop_name%'],
            [$inquiry['title'], $this->context->shop->name],
            Configuration::get('INQUIRY_SEO_META_DESC')
        );
        $this->metaTitle = Tools::substr($metaTitle, 0, 70);
        $this->metaDescription = Tools::substr($metaDescription, 0, 160);

        $this->context->smarty->assign([
            'faq_schema_json' => $inquiry['admin_reply'] !== '' && $inquiry['admin_reply'] !== null
                ? $this->buildFaqSchema($inquiry['title'], $inquiry['admin_reply'])
                : null,
        ]);

        parent::initContent();

        $inquiry['products'] = $db->executeS(
            'SELECT cp.id_product, pl.name, l.id_image, l.legend
            FROM `' . _DB_PREFIX_ . 'inquiry_product` cp
            INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (cp.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id . ' AND pl.id_shop = ' . (int) $this->context->shop->id . ')
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

        $inquiry['masked_email'] = $this->maskEmail($inquiry['email']);

        $this->context->smarty->assign([
            'inquiry' => $inquiry,
            'back_url' => $this->context->link->getModuleLink($this->module->name, 'page'),
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
        ]);

        $this->setTemplate('module:inquiry/views/templates/front/inquiry.tpl');
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = $this->metaTitle;
        $page['meta']['description'] = $this->metaDescription;

        return $page;
    }

    private function maskEmail($email)
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return $email;
        }

        return Tools::substr($parts[0], 0, 1) . '***@' . $parts[1];
    }

    private function buildFaqSchema($title, $adminReply)
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $title,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($adminReply),
                    ],
                ],
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT);
    }
}
