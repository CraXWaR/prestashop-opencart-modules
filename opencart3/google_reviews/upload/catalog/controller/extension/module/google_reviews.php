<?php

class ControllerExtensionModuleGoogleReviews extends Controller
{
    public function index()
    {
        $this->load->language('extension/module/google_reviews');
        $this->load->model('extension/module/google_reviews');

        if (!$this->config->get('module_google_reviews_status')) {
            return '';
        }

        $this->document->addStyle('catalog/view/theme/default/stylesheet/google_reviews.css');
        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
        $this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');

        $titles = $this->config->get('module_google_reviews_title');
        $language_id = $this->config->get('config_language_id');

        if (is_array($titles) && isset($titles[$language_id])) {
            $data['heading_title'] = $titles[$language_id];
        } else {
            $data['heading_title'] = '';
        }
        $data['show_date'] = $this->config->get('module_google_reviews_show_date');
        $data['show_logo'] = $this->config->get('module_google_reviews_show_logo');
        $data['enable_slider'] = $this->config->get('module_google_reviews_enable_slider');
        $data['per_row'] = $this->config->get('module_google_reviews_reviews_per_row') ?: 3;
        $data['mobile_view'] = $this->config->get('module_google_reviews_mobile_view') ?: 'slider';
        $data['bg_color'] = $this->config->get('module_google_reviews_background_color') ?: '#f9f9f9';
        $data['card_bg_color'] = $this->config->get('module_google_reviews_card_bg_color') ?: '#ffffff';
        $data['text_color'] = $this->config->get('module_google_reviews_text_color') ?: '#333333';
        $data['accent_color'] = $this->config->get('module_google_reviews_accent_color') ?: '#f5a623';

        $data['text_read_more'] = $this->language->get('text_read_more');

        $data['reviews'] = $this->model_extension_module_google_reviews->getReviews();

        if (empty($data['reviews'])) {
            return '';
        }

        return $this->load->view('extension/module/google_reviews', $data);
    }
}