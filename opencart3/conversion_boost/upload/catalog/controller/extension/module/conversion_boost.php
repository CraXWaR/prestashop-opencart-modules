<?php

class ControllerExtensionModuleConversionBoost extends Controller
{
    public function index() {
        $this->load->language('extension/module/conversion_boost');

        if (!$this->config->get('module_conversion_boost_status')) {
            return;
        }

        $this->load->model('extension/module/conversion_boost');

        $product_id = (int)$this->request->get['product_id'];

        if (!$product_id) {
            return;
        }

        $data['product_id'] = $product_id;

        $data['timer_style'] = $this->config->get('module_conversion_boost_timer_style') ?? 'plain';
        $data['timer_accent_color'] = $this->config->get('module_conversion_boost_timer_accent_color') ?? '#ff6600';
        $data['timer_text'] = $this->config->get('module_conversion_boost_timer_text') ?? 'Offer ends in:';

        $data['text_days'] = $this->language->get('text_days');
        $data['text_hours'] = $this->language->get('text_hours');
        $data['text_minutes'] = $this->language->get('text_minutes');
        $data['text_seconds'] = $this->language->get('text_seconds');

        $this->document->addStyle('catalog/view/theme/default/stylesheet/conversion_boost.css');
        $this->document->addScript('catalog/view/javascript/conversion_boost.js');
        return $this->load->view('extension/module/countdown_timer', $data);
    }

    public function getServerTime() {
        $this->load->model('extension/module/conversion_boost');

        $product_id = (int)($this->request->get['product_id'] ?? 0);
        $json = array();

        if ($product_id) {
            $special = $this->model_extension_module_conversion_boost->getSpecialPrice($product_id);
            if (!empty($special['date_end'])) {
                $end = strtotime($special['date_end']);
                $json['server_time'] = time();
                $json['end_time'] = $end;
                $json['remaining_seconds'] = max(0, $end - time());
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function stickyBar()
    {
        if (!$this->config->get('module_conversion_boost_status')) {
            return;
        }

        $this->load->model('extension/module/conversion_boost');
        $this->load->model('tool/image');

        $product_id = (int)$this->request->get['product_id'];

        if (!$product_id) {
            return;
        }

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if (!$product_info) {
            return;
        }

        $data['product_id'] = $product_id;
        $data['product_info'] = $product_info;

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $data['price'] = $this->currency->format(
                $this->tax->calculate(
                    $product_info['price'],
                    $product_info['tax_class_id'],
                    $this->config->get('config_tax')
                ),
                $this->session->data['currency']
            );
        } else {
            $data['price'] = false;
        }

        if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
            $data['special'] = $this->currency->format(
                $this->tax->calculate(
                    $product_info['special'],
                    $product_info['tax_class_id'],
                    $this->config->get('config_tax')
                ),
                $this->session->data['currency']
            );
        } else {
            $data['special'] = false;
        }

        $data['thumbnail'] = $this->model_tool_image->resize($product_info['image'], 200, 200);
        $data['minimum'] = $product_info['minimum'];
        $data['sticky_position'] = $this->config->get('module_conversion_boost_sticky_position') ?? 'top';
        $data['sticky_bg_color'] = $this->config->get('module_conversion_boost_sticky_bg_color') ?? '#ffffff';
        $data['sticky_btn_color'] = $this->config->get('module_conversion_boost_sticky_btn_color') ?? '#ff6600';
        $data['sticky_hide_mobile'] = $this->config->get('module_conversion_boost_sticky_hide_mobile') ?? '0';

        $data['text_add_to_cart'] = $this->language->get('button_cart');

        $this->document->addStyle('catalog/view/theme/default/stylesheet/sticky_bar.css');
        $this->document->addScript('catalog/view/javascript/conversion_boost.js');
        return $this->load->view('extension/module/sticky_bar', $data);
    }
}
