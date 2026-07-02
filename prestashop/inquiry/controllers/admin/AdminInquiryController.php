<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminInquiryController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function init()
    {
        if (!Tools::getValue('ajax')) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminModules') . '&configure=inquiry'
            );
        }
        parent::init();
    }

    public function ajaxProcessSearchProducts()
    {
        $query = trim((string) Tools::getValue('q'));
        $results = [];

        if (Tools::strlen($query) >= 2) {
            $idLang = (int) $this->context->language->id;
            $idShop = (int) $this->context->shop->id;

            $results = Db::getInstance()->executeS(
                'SELECT p.id_product, pl.name, p.reference
                FROM `' . _DB_PREFIX_ . 'product` p
                INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON (p.id_product = pl.id_product AND pl.id_lang = ' . $idLang . ' AND pl.id_shop = ' . $idShop . ')
                ' . Shop::addSqlAssociation('product', 'p') . '
                WHERE p.active = 1
                AND (
                    pl.name LIKE \'%' . pSQL($query) . '%\'
                    OR p.reference LIKE \'%' . pSQL($query) . '%\'
                    OR p.ean13 LIKE \'%' . pSQL($query) . '%\'
                    OR p.upc LIKE \'%' . pSQL($query) . '%\'
                    OR p.id_product = ' . (int) $query . '
                )
                ORDER BY pl.name ASC
                LIMIT 20'
            );
        }

        $options = [];
        foreach ($results as $row) {
            $options[] = [
                'id' => (int) $row['id_product'],
                'text' => $row['name'] . ($row['reference'] !== '' ? ' (' . $row['reference'] . ')' : ' (#' . (int) $row['id_product'] . ')'),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['results' => $options]);
        exit;
    }

    public function ajaxProcessSearchCategories()
    {
        $query = trim((string) Tools::getValue('q'));
        $results = [];

        if (Tools::strlen($query) >= 2) {
            $idLang = (int) $this->context->language->id;
            $idShop = (int) $this->context->shop->id;

            $results = Db::getInstance()->executeS(
                'SELECT c.id_category, cl.name
                FROM `' . _DB_PREFIX_ . 'category` c
                INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON (c.id_category = cl.id_category AND cl.id_lang = ' . $idLang . ' AND cl.id_shop = ' . $idShop . ')
                ' . Shop::addSqlAssociation('category', 'c') . '
                WHERE c.active = 1
                AND c.id_category != 1
                AND cl.name LIKE \'%' . pSQL($query) . '%\'
                ORDER BY cl.name ASC
                LIMIT 20'
            );
        }

        $options = [];
        foreach ($results as $row) {
            $options[] = [
                'id' => (int) $row['id_category'],
                'text' => $row['name'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['results' => $options]);
        exit;
    }
}