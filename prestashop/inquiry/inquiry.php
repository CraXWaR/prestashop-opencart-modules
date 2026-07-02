<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Inquiry extends Module
{
    public function __construct()
    {
        $this->name = 'inquiry';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Emanuil';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Запитвания');
        $this->description = $this->l('Отделна страница за запитвания. Администраторът може да одобрява и да отговаря.');
    }

    public function install()
    {
        return parent::install()
            && $this->installDb()
            && $this->installTab()
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('INQUIRY_AUTO_APPROVE', 0)
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayNav')
            && $this->registerHook('displayHeader')
            && Configuration::updateValue('INQUIRY_SEO_META_TITLE', '%title% - %shop_name%')
            && Configuration::updateValue('INQUIRY_SEO_META_DESC', '%title%');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallDb()
            && $this->uninstallTab()
            && Configuration::deleteByName('INQUIRY_AUTO_APPROVE')
            && Configuration::deleteByName('INQUIRY_SEO_META_TITLE')
            && Configuration::deleteByName('INQUIRY_SEO_META_DESC')
            && Configuration::deleteByName('INQUIRY_RECAPTCHA_SITE_KEY')
            && Configuration::deleteByName('INQUIRY_RECAPTCHA_SECRET_KEY');
    }

    private function installDb()
    {
        $sql = file_get_contents(dirname(__FILE__) . '/sql/install.sql');
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }
        return true;
    }

    private function uninstallDb()
    {
        $sql = file_get_contents(dirname(__FILE__) . '/sql/uninstall.sql');
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }
        return true;
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminInquiry';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Запитвания';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentCustomer'); // ← Clients dropdown
        $tab->module = $this->name;

        return $tab->add();
    }

    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminInquiry');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function hookDisplayHeader($params)
    {
        $faqSchema = $this->context->smarty->getTemplateVars('faq_schema_json');
        if (!$faqSchema) {
            return '';
        }

        return '<script type="application/ld+json">' . $faqSchema . '</script>';
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (
            $this->context->controller instanceof ModuleFrontController
            && $this->context->controller->module instanceof Inquiry
        ) {
            $this->context->controller->registerStylesheet(
                'inquiry-css',
                'modules/' . $this->name . '/views/css/page.css'
            );
            $this->context->controller->registerJavascript(
                'inquiry-js',
                'modules/' . $this->name . '/views/js/inquiry.js',
                ['position' => 'bottom', 'priority' => 200]
            );
        }
    }

    public function hookModuleRoutes()
    {
        return [
            'module-inquiry-page' => [
                'controller' => 'page',
                'rule' => 'inquiries',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => 'inquiry',
                ],
            ],
            'module-inquiry-detail' => [
                'controller' => 'detail',
                'rule' => 'inquiries/{id}',
                'keywords' => [
                    'id' => ['regexp' => '[0-9]+', 'param' => 'id'],
                ],
                'params' => ['fc' => 'module', 'module' => 'inquiry'],
            ],
        ];
    }

    public function hookDisplayFooter($params)
    {
        $employees = Db::getInstance()->executeS(
            'SELECT id_employee, firstname, lastname FROM `' . _DB_PREFIX_ . 'employee`'
        );
        $employeeMap = [];
        foreach ($employees as $emp) {
            $employeeMap[(int) $emp['id_employee']] = $emp['firstname'] . ' ' . $emp['lastname'];
        }

        $this->context->smarty->assign([
            'inquiry_url' => $this->context->link->getModuleLink('inquiry', 'page'),
            'employee_map' => $employeeMap,
        ]);
        return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
    }

    public function hookDisplayNav($params)
    {
        $this->context->smarty->assign([
            'inquiry_url' => $this->context->link->getModuleLink('inquiry', 'page'),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/nav.tpl');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_settings')) {
            Configuration::updateValue('INQUIRY_AUTO_APPROVE', (int) Tools::getValue('INQUIRY_AUTO_APPROVE'));
            Configuration::updateValue('INQUIRY_SEO_META_TITLE', Tools::substr(trim(Tools::getValue('seo_meta_title')), 0, 255));
            Configuration::updateValue('INQUIRY_SEO_META_DESC', Tools::substr(trim(Tools::getValue('seo_meta_desc')), 0, 255));
            Configuration::updateValue('INQUIRY_RECAPTCHA_SITE_KEY', trim(Tools::getValue('recaptcha_site_key')));
            Configuration::updateValue('INQUIRY_RECAPTCHA_SECRET_KEY', trim(Tools::getValue('recaptcha_secret_key')));
            $output .= $this->displayConfirmation($this->l('Настройките са запазени.'));
        }

        if (Tools::isSubmit('approve_inquiry')) {
            Db::getInstance()->update('inquiry', ['approved' => 1], 'id_inquiry = ' . (int) Tools::getValue('id_inquiry'));
            $output .= $this->displayConfirmation($this->l('Запитването е одобрено.'));
        }

        if (Tools::isSubmit('delete_inquiry')) {
            Db::getInstance()->delete('inquiry', 'id_inquiry = ' . (int) Tools::getValue('id_inquiry'));
            $output .= $this->displayConfirmation($this->l('Запитването е изтрито.'));
        }

        if (Tools::isSubmit('save_inquiry')) {
            $idInquiry = (int) Tools::getValue('id_inquiry');

            $fields = [
                'admin_reply' => pSQL(Tools::purifyHTML(Tools::getValue('admin_reply'))),
            ];

            // Stamp the replying employee only when a reply was actually written
            if (trim(Tools::getValue('admin_reply')) !== '') {
                $fields['id_employee'] = (int) $this->context->employee->id;
            }
            Db::getInstance()->update('inquiry', $fields, 'id_inquiry = ' . $idInquiry);

            Db::getInstance()->delete('inquiry_product', 'id_inquiry = ' . $idInquiry);
            foreach ((array) Tools::getValue('id_product', []) as $idProduct) {
                $idProduct = (int) $idProduct;
                if ($idInquiry && $idProduct) {
                    Db::getInstance()->insert('inquiry_product', [
                        'id_inquiry' => $idInquiry,
                        'id_product' => $idProduct,
                    ]);
                }
            }

            Db::getInstance()->delete('inquiry_category', 'id_inquiry = ' . $idInquiry);
            foreach ((array) Tools::getValue('id_category', []) as $idCategory) {
                $idCategory = (int) $idCategory;
                if ($idInquiry && $idCategory) {
                    Db::getInstance()->insert('inquiry_category', [
                        'id_inquiry' => $idInquiry,
                        'id_category' => $idCategory,
                    ]);
                }
            }

            $output .= $this->displayConfirmation($this->l('Промените са запазени.'));
        }

        $pageUrl = $this->context->link->getModuleLink($this->name, 'page');

        $inquiries = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'inquiry` ORDER BY `date_add` DESC');

        foreach ($inquiries as &$inquiry) {
            $inquiry['products'] = Db::getInstance()->executeS(
                'SELECT cp.id_product, pl.name,
                (SELECT image_shop.id_image
                    FROM `' . _DB_PREFIX_ . 'image` i
                    INNER JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (i.id_image = image_shop.id_image AND image_shop.id_shop = ' . (int) $this->context->shop->id . ')
                    WHERE i.id_product = cp.id_product AND i.cover = 1
                    LIMIT 1) as id_image
                    FROM `' . _DB_PREFIX_ . 'inquiry_product` cp
                    INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (cp.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id . ' AND pl.id_shop = ' . (int) $this->context->shop->id . ')
                    INNER JOIN `' . _DB_PREFIX_ . 'product` p ON cp.id_product = p.id_product
                    WHERE cp.id_inquiry = ' . (int) $inquiry['id_inquiry']
            );

            $inquiry['categories'] = Db::getInstance()->executeS(
                'SELECT ic.id_category, cl.name
                    FROM `' . _DB_PREFIX_ . 'inquiry_category` ic
                    INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (ic.id_category = cl.id_category AND cl.id_lang = ' . (int) $this->context->language->id . ' AND cl.id_shop = ' . (int) $this->context->shop->id . ')
                    WHERE ic.id_inquiry = ' . (int) $inquiry['id_inquiry']
            );
        }
        unset($inquiry);

        $employees = Db::getInstance()->executeS(
            'SELECT id_employee, firstname, lastname FROM `' . _DB_PREFIX_ . 'employee`'
        );
        $employeeMap = [];
        foreach ($employees as $emp) {
            $employeeMap[(int) $emp['id_employee']] = trim($emp['firstname'] . ' ' . $emp['lastname']);
        }

        $this->context->smarty->assign([
            'inquiries' => $inquiries,
            'auto_approve' => Configuration::get('INQUIRY_AUTO_APPROVE'),
            'action_url' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'page_url' => $pageUrl,
            'employee_map' => $employeeMap,
            'ajax_search_products_url' => $this->context->link->getAdminLink('AdminInquiry') . '&ajax=1&action=SearchProducts',
            'ajax_search_categories_url' => $this->context->link->getAdminLink('AdminInquiry') . '&ajax=1&action=SearchCategories',
            'seo_meta_title' => Configuration::get('INQUIRY_SEO_META_TITLE'),
            'seo_meta_desc' => Configuration::get('INQUIRY_SEO_META_DESC'),
            'recaptcha_site_key' => Configuration::get('INQUIRY_RECAPTCHA_SITE_KEY'),
            'recaptcha_secret_key' => Configuration::get('INQUIRY_RECAPTCHA_SECRET_KEY'),
        ]);

        $output .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');
        $this->context->controller->addCSS($this->_path . 'views/css/configure.css');
        $this->context->controller->addCSS($this->_path . 'views/css/select2.min.css');
        $this->context->controller->addJS($this->_path . 'views/js/select2.min.js');

        return $output;
    }
}
