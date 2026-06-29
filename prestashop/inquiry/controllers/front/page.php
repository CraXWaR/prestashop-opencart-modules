<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class InquiryPageModuleFrontController extends ModuleFrontController
{
    const INQUIRIES_PER_PAGE = 10;

    const SEARCH_MAX_WORDS = 10;

    private $stopWords = [
        // Bulgarian
        'на',
        'за',
        'се',
        'си',
        'да',
        'не',
        'че',
        'ли',
        'то',
        'той',
        'тя',
        'те',
        'го',
        'ни',
        'ви',
        'им',
        'ми',
        'му',
        'от',
        'по',
        'до',
        'при',
        'със',
        'във',
        'ще',
        'как',
        'какво',
        'кога',
        'къде',
        'кой',
        'коя',
        'кое',
        'кои',
        'това',
        'този',
        'тази',
        'тези',
        'аз',
        'ти',
        'ние',
        'вие',
        'или',
        'но',
        'ако',
        'защото',
        'също',
        'още',
        'вече',
        'са',
        'съм',
        'сте',
        'сме',
        'бил',
        'била',
        'било',
        'били',
        'беше',
        // English (mixed-language fallback)
        'and',
        'or',
        'is',
        'are',
        'of',
        'to',
        'in',
        'on',
        'for',
        'an',
        'the',
        'with',
        'this',
        'that',
        'do',
        'can',
        'you',
        'your',
    ];

    /** @var string current search query */
    private $searchQuery = '';
    private $currentCategory = 0;

    public function initContent()
    {
        parent::initContent();

        $db = Db::getInstance();

        $this->searchQuery = trim(Tools::getValue('q', ''));
        $this->currentCategory = (int) Tools::getValue('category', 0);

        $where = 'approved = 1' . $this->buildSearchWhere($this->searchQuery);

        if ($this->currentCategory > 0) {
            $where .= ' AND id_category = ' . $this->currentCategory;
        }

        $total = (int) $db->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'inquiry` WHERE ' . $where
        );

        $perPage = self::INQUIRIES_PER_PAGE;
        $totalPages = max(1, (int) ceil($total / $perPage));

        $page = (int) Tools::getValue('p', 1);
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $inquiries = $db->executeS(
            'SELECT *
            FROM `' . _DB_PREFIX_ . 'inquiry`
            WHERE ' . $where . '
            ORDER BY date_add DESC
            LIMIT ' . (int) $offset . ', ' . (int) $perPage
        );
        $baseLink = $this->context->link->getModuleLink($this->module->name, 'page');
        $action = preg_replace('/\?.*$/', '', $baseLink);
        $hidden = [];
        $qpos = strpos($baseLink, '?');
        if ($qpos !== false) {
            parse_str(html_entity_decode(Tools::substr($baseLink, $qpos + 1)), $hidden);
        }
        unset($hidden['q'], $hidden['p']);

        $rawCategories = $db->executeS(
            'SELECT c.id_category, cl.name
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (c.id_category = cl.id_category
                    AND cl.id_lang = ' . (int) $this->context->language->id . '
                    AND cl.id_shop = ' . (int) $this->context->shop->id . ')
            WHERE c.active = 1
            AND c.id_category != 1
            ORDER BY cl.name ASC'
        );

        $categories = [];
        foreach ($rawCategories as $cat) {
            $categories[] = [
                'id_category' => (int) $cat['id_category'],
                'name' => $cat['name'],
                'url' => $this->context->link->getModuleLink(
                    $this->module->name,
                    'page',
                    ['category' => (int) $cat['id_category']]
                ),
                'active' => $this->currentCategory === (int) $cat['id_category'],
            ];
        }

        if ($this->currentCategory > 0) {
            foreach ($categories as $i => $cat) {
                if ($cat['active']) {
                    unset($categories[$i]);
                    array_unshift($categories, $cat);
                    break;
                }
            }
        }

        $employees = $db->executeS(
            'SELECT id_employee, firstname, lastname FROM `' . _DB_PREFIX_ . 'employee`'
        );
        $employeeMap = [];
        foreach ($employees as $emp) {
            $employeeMap[(int) $emp['id_employee']] = trim($emp['firstname'] . ' ' . $emp['lastname']);
        }

        foreach ($inquiries as &$inquiry) {
            $inquiry['url'] = $this->context->link->getModuleLink(
                $this->module->name,
                'detail',
                ['id' => (int) $inquiry['id_inquiry']]
            );

            $idEmployee = (int) $inquiry['id_employee'];
            $inquiry['employee_name'] = isset($employeeMap[$idEmployee]) ? $employeeMap[$idEmployee] : null;

            $inquiry['products'] = $db->executeS(
                'SELECT cp.id_product, pl.name,
            l.id_image,
            l.legend
                FROM `' . _DB_PREFIX_ . 'inquiry_product` cp
                INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (cp.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` l ON (l.id_image = (
                    SELECT i.id_image FROM `' . _DB_PREFIX_ . 'image` i
                    WHERE i.id_product = cp.id_product AND i.cover = 1 LIMIT 1
                ) AND l.id_lang = ' . (int) $this->context->language->id . ')
                WHERE cp.id_inquiry = ' . (int) $inquiry['id_inquiry']
            );

            foreach ($inquiry['products'] as &$product) {
                $product['url'] = $this->context->link->getProductLink((int) $product['id_product']);
                $product['image_url'] = $product['id_image']
                    ? $this->context->link->getImageLink(
                        'product',
                        $product['id_product'] . '-' . $product['id_image']
                    )
                    : null;
            }
            unset($product);
        }
        unset($inquiry);

        $this->context->smarty->assign([
            'inquiries' => $inquiries,
            'submit_url' => $this->context->link->getModuleLink($this->module->name, 'submit'),
            'search_query' => $this->searchQuery,
            'search_action' => $action,
            'search_hidden' => $hidden,
            'search_url' => $baseLink,
            'inq_pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_inquiries' => $total,
                'prev_url' => $page > 1 ? $this->buildPageLink($page - 1) : null,
                'next_url' => $page < $totalPages ? $this->buildPageLink($page + 1) : null,
                'pages' => $this->buildPages($page, $totalPages),
            ],
            'current_category' => $this->currentCategory,
            'categories' => $categories,
        ]);

        $this->setTemplate('module:inquiry/views/templates/front/page.tpl');
    }

    private function buildSearchWhere($search)
    {
        if ($search === '') {
            return '';
        }

        $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
        $conds = [];

        foreach ($words as $word) {
            if (count($conds) >= self::SEARCH_MAX_WORDS) {
                break;
            }
            if (Tools::strlen($word) < 2 || in_array(Tools::strtolower($word), $this->stopWords, true)) {
                continue;
            }

            $like = '%' . pSQL($word) . '%';
            $conds[] = '(`title` LIKE \'' . $like . '\''
                . ' OR `content` LIKE \'' . $like . '\''
                . ' OR `admin_reply` LIKE \'' . $like . '\')';
        }

        if (!$conds) {
            return '';
        }

        return ' AND (' . implode(' AND ', $conds) . ')';
    }

    private function buildPageLink($n)
    {
        $params = ['p' => (int) $n];
        if ($this->searchQuery !== '') {
            $params['q'] = $this->searchQuery;
        }

        if ($this->currentCategory > 0) {
            $params['category'] = $this->currentCategory;
        }

        return $this->context->link->getModuleLink($this->module->name, 'page', $params);
    }

    private function buildPages($current, $totalPages)
    {
        $window = 2;
        $pages = [];
        $lastWasGap = false;

        for ($i = 1; $i <= $totalPages; $i++) {
            $inWindow = ($i >= $current - $window && $i <= $current + $window);

            if ($i == 1 || $i == $totalPages || $inWindow) {
                $pages[] = [
                    'is_gap' => false,
                    'number' => $i,
                    'url' => $this->buildPageLink($i),
                    'current' => ($i == $current),
                ];
                $lastWasGap = false;
            } elseif (!$lastWasGap) {
                $pages[] = ['is_gap' => true];
                $lastWasGap = true;
            }
        }

        return $pages;
    }
}
