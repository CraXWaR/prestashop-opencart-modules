<?php
class ControllerExtensionModuleArchiveSeoEvent extends Controller
{
    public function flagArchivedProduct(&$route, &$args, &$output)
    {
        if (!$this->config->get('module_archive_seo_status')) {
            return;
        }

        $product_id = isset($this->request->get['product_id']) ? (int) $this->request->get['product_id'] : 0;

        if (!$product_id || !isset($args[0]) || (int) $args[0] !== $product_id) {
            return;
        }

        if (!empty($output) && (int) $output['status'] === 2) {
            $this->registry->set('seo_archive', true);
        }
    }

    public function injectArchiveNotice(&$route, &$data, &$output)
    {
        if (!$this->config->get('module_archive_seo_status')) {
            return;
        }

        if (!$this->registry->get('seo_archive')) {
            return;
        }

        $this->load->language('extension/module/archive_seo');

        $banner = '<div class="alert alert-warning"><i class="fa fa-archive"></i> '
            . $this->language->get('text_seo_archive_notice')
            . '<br /><a href="' . $this->url->link('common/home') . '">'
            . $this->language->get('text_seo_archive_browse') . '</a></div>';

        $product_id = isset($this->request->get['product_id']) ? (int) $this->request->get['product_id'] : 0;

        $related = $product_id ? $this->buildRelatedProducts($product_id) : '';
        $banner .= $related;

        $css_tag = '<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/archive_seo.css" type="text/css" />';
        $style = '<style>#button-cart,[data-quick-buy],#product-quantity,#input-quantity,#dsk-product-button-container{display:none !important;}</style>';
        $head = strpos($output, '</head>');
        if ($head !== false) {
            $output = substr($output, 0, $head) . $css_tag . $style . substr($output, $head);
        }

        $btn = strpos($output, 'id="button-cart"');
        $qty = strpos($output, 'name="quantity"');

        if ($btn !== false && ($tag = strrpos(substr($output, 0, $btn), '<')) !== false) {
            $output = substr($output, 0, $tag) . $banner . substr($output, $tag);
        } elseif ($qty !== false && ($tag = strrpos(substr($output, 0, $qty), '<')) !== false) {
            $output = substr($output, 0, $tag) . $banner . substr($output, $tag);
        } elseif (preg_match('/<div[^>]*id="content"[^>]*>/', $output, $match, PREG_OFFSET_CAPTURE)) {
            $at = $match[0][1] + strlen($match[0][0]);
            $output = substr($output, 0, $at) . $banner . substr($output, $at);
        } else {
            $output = $banner . $output;
        }
    }

    /**
     * Builds the related products block and renders it via Twig.
     */
    private function buildRelatedProducts($product_id)
    {
        $results = $this->getRelatedResults($product_id);

        if (empty($results)) {
            return '';
        }

        $results = array_values($results);
        shuffle($results);
        $results = array_slice($results, 0, 2);

        $journal = is_file(DIR_APPLICATION . 'model/journal3/product.php');

        if ($journal) {
            $this->load->model('journal3/image');
        } else {
            $this->load->model('tool/image');
        }

        $this->load->language('product/product');
        $this->load->language('extension/module/archive_seo');

        $button_cart = $this->language->get('button_cart');
        $button_wishlist = $this->language->get('button_wishlist');
        $button_compare = $this->language->get('button_compare');

        $show_price = $this->customer->isLogged() || !$this->config->get('config_customer_price');

        $width = 240;
        $height = 240;

        $products = [];

        foreach ($results as $result) {
            $image = !empty($result['image']) ? $result['image'] : 'placeholder.png';

            if ($journal) {
                $thumb = $this->model_journal3_image->resize($image, $width, $height);
                $thumb2x = $this->model_journal3_image->resize($image, $width * 2, $height * 2);
            } else {
                $thumb = $this->model_tool_image->resize($image, $width, $height);
                $thumb2x = $this->model_tool_image->resize($image, $width * 2, $height * 2);
            }

            $has_special = !is_null($result['special']) && (float) $result['special'] > 0;

            if ($show_price) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $special = $has_special ? $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : '';
            } else {
                $price = $special = '';
            }

            $discount_pct = 0;
            if ($has_special && (float) $result['price'] > 0 && (float) $result['special'] < (float) $result['price']) {
                $pct = (int) round((1 - ($result['special'] / $result['price'])) * 100);
                if ($pct > 0) {
                    $discount_pct = $pct;
                }
            }

            $products[] = [
                'product_id' => (int) $result['product_id'],
                'name' => htmlspecialchars($result['name'], ENT_QUOTES, 'UTF-8'),
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                'thumb' => $thumb,
                'thumb2x' => $thumb2x,
                'width' => $width,
                'height' => $height,
                'price' => $price,
                'special' => $special,
                'discount_pct' => $discount_pct,
                'rating' => isset($result['rating']) ? (int) $result['rating'] : 0,
            ];
        }

        $data = [
            'products' => $products,
            'title' => $this->language->get('text_seo_archive_related'),
            'button_cart' => $button_cart,
            'button_wishlist' => $button_wishlist,
            'button_compare' => $button_compare,
        ];

        return $this->load->view('extension/module/archive_seo', $data);
    }

    /**
     * Resolves the products to show.
     */
    private function getRelatedResults($product_id)
    {
        $limit = 8;

        if (is_file(DIR_APPLICATION . 'model/journal3/product.php')) {
            try {
                $this->load->model('journal3/product');
                $results = $this->model_journal3_product->getRelatedProductsByCategory($product_id, $limit);

                if (!empty($results)) {
                    return $results;
                }
            } catch (\Exception $e) {
                // Journal not fully loaded — fall through to the core source.
            }
        }

        $this->load->model('catalog/product');

        return $this->model_catalog_product->getProductRelated($product_id);
    }
}