<?php

class ControllerExtensionModuleEnergyLabelEvent extends Controller
{
    /** Badges built in *before* events, consumed by *after* events on the same request. */
    private static $pendingBadges = [];

    public function onProductPageBefore(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }

        if (!$this->config->get('module_energy_label_show_product')) {
            return;
        }

        $product_id = isset($this->request->get['product_id'])
            ? (int)$this->request->get['product_id']
            : 0;

        if (!$product_id) {
            return;
        }

        $data['energy_label_block'] = $this->load->controller('extension/module/energy_label/index');

        // Enrich related products with badge markers
        if (!empty($data['products']) && is_array($data['products'])) {
            $this->load->model('extension/module/energy_label');

            $classes    = $this->getResizedClasses();
            $relatedIds = array_column($data['products'], 'product_id');
            $allLabels  = $this->model_extension_module_energy_label->getProductsLabels($relatedIds);

            foreach ($data['products'] as &$product) {
                $rpid = isset($product['product_id']) ? (int)$product['product_id'] : 0;
                if (!$rpid) {
                    continue;
                }
                $labels = $allLabels[$rpid] ?? [];
                if (empty($labels)) {
                    continue;
                }

                $badgeHtml = $this->load->view(
                    'extension/module/energy_label_badge',
                    ['labels' => $labels, 'classes' => $classes]
                );

                if (!isset(self::$pendingBadges[$rpid])) {
                    self::$pendingBadges[$rpid] = $badgeHtml;
                    $product['price'] = '<span class="el-anchor" data-pid="' . $rpid . '"></span>' . ($product['price'] ?? '');
                }
            }
            unset($product);
        }
    }

    public function onProductPageAfter(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }
        if (!$this->config->get('module_energy_label_show_product')) {
            return;
        }
        $this->injectPendingBadges($output);
    }

    public function onCategoryPageBefore(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }

        if (!$this->config->get('module_energy_label_show_category')) {
            return;
        }

        if (empty($data['products'])) {
            return;
        }

        $this->load->model('extension/module/energy_label');

        $classes    = $this->getResizedClasses();
        $productIds = array_column($data['products'], 'product_id');
        $allLabels = $this->model_extension_module_energy_label->getProductsLabels($productIds);

        foreach ($data['products'] as &$product) {
            $labels = $allLabels[$product['product_id']] ?? [];
            $product['energy_badge'] = '';

            if (!empty($labels)) {
                $pid = (int)$product['product_id'];
                $badgeHtml = $this->load->view(
                    'extension/module/energy_label_badge',
                    ['labels' => $labels, 'classes' => $classes]
                );

                // Only plant marker if not already planted by another before-event this request
                if (!isset(self::$pendingBadges[$pid])) {
                    self::$pendingBadges[$pid] = $badgeHtml;
                    $product['price'] = '<span class="el-anchor" data-pid="' . $pid . '"></span>' . ($product['price'] ?? '');
                }

                $product['energy_badge'] = $badgeHtml; // backwards compat
            }
        }
    }

    public function onCategoryPageAfter(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }
        if (!$this->config->get('module_energy_label_show_category')) {
            return;
        }
        $this->injectPendingBadges($output);
    }

    public function onSearchPageAfter(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }
        if (!$this->config->get('module_energy_label_show_search')) {
            return;
        }
        $this->injectPendingBadges($output);
    }

    public function onSpecialPageAfter(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }
        if (!$this->config->get('module_energy_label_show_special')) {
            return;
        }
        $this->injectPendingBadges($output);
    }

    public function onManufacturerPageAfter(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }
        if (!$this->config->get('module_energy_label_show_manufacturer')) {
            return;
        }
        $this->injectPendingBadges($output);
    }

    public function onHeaderBefore(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }

        if (!$this->shouldInjectStyles()) {
            return;
        }

        $data['styles']['catalog/view/theme/default/stylesheet/energy_label_block.css'] = [
            'href' => 'catalog/view/theme/default/stylesheet/energy_label_block.css',
            'rel' => 'stylesheet',
            'media' => 'screen'
        ];
        $data['styles']['catalog/view/theme/default/stylesheet/energy_label_badge.css'] = [
            'href' => 'catalog/view/theme/default/stylesheet/energy_label_badge.css',
            'rel' => 'stylesheet',
            'media' => 'screen'
        ];

        $customCss = $this->config->get('module_energy_label_custom_css');
        if (!empty($customCss)) {
            $data['custom_css'] = preg_replace('/<\/style/i', '', $customCss);
        }
    }

    private function getResizedClasses(): array
    {
        $this->load->model('tool/image');
        $classes = $this->model_extension_module_energy_label->getAllClassesIndexed();

        foreach ($classes as &$class) {
            $class['icon'] = ($class['icon'] && is_file(DIR_IMAGE . $class['icon']))
                ? $this->model_tool_image->resize($class['icon'], 40, 40)
                : '';
        }
        unset($class);

        return $classes;
    }

    private function shouldInjectStyles(): bool
    {
        // Journal3 widgets can appear on any page, so always inject if enabled
        if ($this->config->get('module_energy_label_show_journal3')) {
            return true;
        }

        $pageRoute = $this->request->get['route'] ?? '';

        $map = [
            'product/product'      => 'module_energy_label_show_product',
            'product/category'     => 'module_energy_label_show_category',
            'product/search'       => 'module_energy_label_show_search',
            'product/special'      => 'module_energy_label_show_special',
            'product/manufacturer' => 'module_energy_label_show_manufacturer',
        ];

        return isset($map[$pageRoute]) && (bool)$this->config->get($map[$pageRoute]);
    }

    /**
     * Replaces every <span class="el-anchor" data-pid="N"></span> marker
     * in $output with its matching badge HTML. First occurrence per pid wins;
     * duplicate markers are stripped so nothing visible is left behind.
     */
    private function injectPendingBadges(&$output): void
    {
        if (!self::$pendingBadges || !is_string($output) || $output === '') {
            return;
        }

        $injected = [];

        // Inject the badge BEFORE the nearest <div|p class="price"> that contains the marker.
        // The {0,3000} bound keeps a stray marker from grabbing a different card's price block.
        $output = preg_replace_callback(
            '/(<(?:div|p)\s[^>]*class\s*=\s*"(?:[^"]*\s)?price(?:\s[^"]*)?"[^>]*>.{0,3000}?)<span class="el-anchor" data-pid="(\d+)"><\/span>/is',
            function ($m) use (&$injected) {
                $pid = (int)$m[2];
                if (!isset($injected[$pid]) && isset(self::$pendingBadges[$pid])) {
                    $injected[$pid] = true;
                    return self::$pendingBadges[$pid] . $m[1];
                }
                return $m[1]; // strip marker only
            },
            $output
        );

        // Fallback: strip any markers not preceded by a price block
        $output = preg_replace('/<span class="el-anchor" data-pid="\d+"><\/span>/', '', $output);
    }

    public function onProductListBefore(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }

        /*
         * THIS is the key change:
         * We detect which page fired this event by looking at $route,
         * then check the matching per-page setting.
         * If the admin disabled badges for that specific page → bail out.
         */
        $settingKey = $this->resolveListingSettingKey($route);
        if (!$settingKey || !$this->config->get($settingKey)) {
            return;
        }

        // Handle Journal3 nested items structure
        $productIds = [];

        if (!empty($data['products'])) {
            $productIds = array_column($data['products'], 'product_id');
        } elseif (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!empty($item['products'])) {
                    $productIds = array_merge($productIds, array_keys($item['products']));
                }
            }
        }

        if (empty($productIds)) {
            return;
        }

        $productIds = array_unique($productIds);
        $badges = $this->buildBadges($productIds);

        if (!$badges) {
            return;
        }

        // Standard OC listing pages
        if (!empty($data['products'])) {
            foreach ($data['products'] as &$product) {
                $pid = isset($product['product_id']) ? (int)$product['product_id'] : 0;
                if ($pid && isset($badges[$pid])) {
                    if (!isset(self::$pendingBadges[$pid])) {
                        self::$pendingBadges[$pid] = $badges[$pid];
                        $product['price'] = '<span class="el-anchor" data-pid="' . $pid . '"></span>' . ($product['price'] ?? '');
                    }
                    $product['energy_badge'] = $badges[$pid]; // backwards compat
                } else {
                    $product['energy_badge'] = '';
                }
            }
            unset($product);
        }

        // Journal3 nested structure
        if (!empty($data['items'])) {
            foreach ($data['items'] as &$item) {
                if (empty($item['products'])) {
                    continue;
                }
                foreach ($item['products'] as $pid => &$product) {
                    $pid = (int)$pid;
                    if (isset($badges[$pid])) {
                        if (!isset(self::$pendingBadges[$pid])) {
                            self::$pendingBadges[$pid] = $badges[$pid];
                            $product['price'] = '<span class="el-anchor" data-pid="' . $pid . '"></span>' . ($product['price'] ?? '');
                        }
                        $product['energy_badge'] = $badges[$pid]; // backwards compat
                    } else {
                        $product['energy_badge'] = '';
                    }
                }
                unset($product);
            }
            unset($item);
        }
    }

    /**
     * Maps the firing route to the correct per-page setting key.
     */
    private function resolveListingSettingKey(string $route): string
    {
        // Strip the event suffix (e.g. "/before") to get the base route
        $base = preg_replace('#/before$#', '', $route);

        $map = [
            'catalog/view/product/category' => 'module_energy_label_show_category',
            'catalog/view/product/search' => 'module_energy_label_show_search',
            'catalog/view/product/special' => 'module_energy_label_show_special',
            'catalog/view/product/manufacturer' => 'module_energy_label_show_manufacturer',
            'catalog/view/journal3/products' => 'module_energy_label_show_journal3',
        ];

        return $map[$base] ?? '';
    }

    private function buildBadges(array $productIds): array
    {
        if (!$productIds) {
            return [];
        }

        $this->load->model('extension/module/energy_label');
        $allLabels = $this->model_extension_module_energy_label->getProductsLabels($productIds);
        if (!$allLabels) {
            return [];
        }

        $classes = $this->getResizedClasses();

        $output = [];
        foreach ($allLabels as $product_id => $labels) {
            $output[$product_id] = $this->load->view(
                'extension/module/energy_label_badge',
                ['labels' => $labels, 'classes' => $classes]
            );
        }
        return $output;
    }

    public function onJournal3ProductsViewAfter(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_energy_label_status')) {
            return;
        }

        // Bail on listing routes — those are handled by our after-event injector now
        $listingRoutes = ['product/category', 'product/catalog', 'product/search', 'product/special', 'product/manufacturer'];
        if (in_array($this->request->get['route'] ?? '', $listingRoutes, true)) {
            return;
        }

        if (!$this->config->get('module_energy_label_show_journal3')) {
            return;
        }

        if (empty($output)) {
            return;
        }

        if (strpos($output, 'energy-label-badge') !== false) {
            return;
        }

        $productId = $data['product']['product_id'] ?? null;
        if (!$productId) {
            return;
        }

        $badges = $this->buildBadges([$productId]);
        if (empty($badges[$productId])) {
            return;
        }

        $badge = $badges[$productId];

        // Match any <div> that has "price" as one of its classes.
        // Using preg_replace_callback avoids backreference issues with $ in badge HTML.
        $injected = false;
        $output = preg_replace_callback(
            '/(<div[^>]+\bprice\b[^>]*>)/i',
            function ($m) use ($badge, &$injected) {
                if ($injected) {
                    return $m[0];
                }
                $injected = true;
                return $badge . $m[0];
            },
            $output
        );

        if (!$injected) {
            $output = $badge . $output;
        }
    }

    public function onInjectModal(&$route, &$data, &$output)
    {
        $this->document->addScript('catalog/view/javascript/energy_label_modal.js');
        $modal = $this->load->view('extension/module/energy_label_modal');

        if (strpos($output, '</body>') !== false) {
            $output = str_replace('</body>', $modal . '</body>', $output);
        }
    }

}