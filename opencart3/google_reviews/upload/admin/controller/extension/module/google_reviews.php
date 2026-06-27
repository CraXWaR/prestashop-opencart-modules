<?php

class ControllerExtensionModuleGoogleReviews extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/google_reviews');

        $this->document->addStyle('view/stylesheet/google_reviews.css');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->deleteSetting('module_google_reviews');

            $this->model_setting_setting->editSetting(
                'module_google_reviews',
                $this->request->post
            );

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array(
                'success' => $this->language->get('text_success')
            )));

            return;
        }

        $data['error_warning'] = isset($this->error['warning'])
            ? $this->error['warning']
            : '';

        $data['error_place_id'] = isset($this->error['place_id'])
            ? $this->error['place_id']
            : '';

        $data['error_api_key'] = isset($this->error['api_key'])
            ? $this->error['api_key']
            : '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=module',
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'extension/module/google_reviews',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );

        $data['action'] = $this->url->link(
            'extension/module/google_reviews',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        $data['cancel'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=module',
            true
        );

        $data['sync_reviews'] = $this->url->link(
            'extension/module/google_reviews/sync',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $config_fields = array(
            'module_google_reviews_status',
            'module_google_reviews_place_id',
            'module_google_reviews_api_key',
            'module_google_reviews_limit',
            'module_google_reviews_min_rating',
            'module_google_reviews_cache_time',
            'module_google_reviews_show_date',
            'module_google_reviews_show_logo',
            'module_google_reviews_enable_slider',
            'module_google_reviews_reviews_per_row',
            'module_google_reviews_mobile_view',
            'module_google_reviews_background_color',
            'module_google_reviews_card_bg_color',
            'module_google_reviews_text_color',
            'module_google_reviews_accent_color',
        );

        foreach ($config_fields as $field) {

            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } else {
                $data[$field] = $this->config->get($field);
            }
        }

        if (isset($this->request->post['module_google_reviews_title'])) {
            $data['module_google_reviews_title'] = $this->request->post['module_google_reviews_title'];
        } else {
            $data['module_google_reviews_title'] = $this->config->get('module_google_reviews_title');
        }

        $data['last_sync'] = $this->config->get('module_google_reviews_last_sync');
        $data['last_error'] = $this->config->get('module_google_reviews_last_error');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/google_reviews', $data));
    }

    public function install()
    {

        $this->load->model('extension/module/google_reviews');

        $this->model_extension_module_google_reviews->install();
    }

    public function uninstall()
    {

        $this->load->model('extension/module/google_reviews');

        $this->model_extension_module_google_reviews->uninstall();
    }

    public function sync()
    {
        $this->load->language('extension/module/google_reviews');
        $this->load->model('extension/module/google_reviews');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/google_reviews')) {
            $json['success'] = false;
            $json['message'] = $this->language->get('error_permission');
        } else {
            $result = $this->model_extension_module_google_reviews->syncReviews();
            $json['success'] = $result['success'];
            $json['message'] = isset($result['message']) ? $this->language->get($result['message']) : $this->language->get('error_sync_failed');
            $json['last_sync'] = $result['last_sync'] ?? '';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/google_reviews')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['module_google_reviews_place_id'])) {
            $this->error['place_id'] = $this->language->get('error_place_id');
        }

        if (empty($this->request->post['module_google_reviews_api_key'])) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }

        return !$this->error;
    }
}