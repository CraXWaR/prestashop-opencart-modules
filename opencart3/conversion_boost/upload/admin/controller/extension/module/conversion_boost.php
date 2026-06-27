<?php

class ControllerExtensionModuleConversionBoost extends Controller
{
    public function index() {
        $this->load->language('extension/module/conversion_boost');
        $this->load->model('setting/setting');

        $this->document->addScript('view/javascript/conversion_boost.js');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['error_permission'] = $this->language->get('error_permission');

        $data['status'] = $this->config->get('module_conversion_boost_status') ?? '0';
        $data['sticky_position'] = $this->config->get('module_conversion_boost_sticky_position') ?? 'top';
        $data['sticky_bg_color'] = $this->config->get('module_conversion_boost_sticky_bg_color') ?? '#ffffff';
        $data['sticky_btn_color'] = $this->config->get('module_conversion_boost_sticky_btn_color') ?? '#ff6600';
        $data['sticky_hide_mobile'] = $this->config->get('module_conversion_boost_sticky_hide_mobile') ?? '0';
        $data['timer_style'] = $this->config->get('module_conversion_boost_timer_style') ?? 'plain';
        $data['timer_accent_color'] = $this->config->get('module_conversion_boost_timer_accent_color') ?? '#ff6600';
        $data['timer_text'] = $this->config->get('module_conversion_boost_timer_text');
        $data['text_sticky_settings'] = $this->language->get('text_sticky_settings');
        $data['text_timer_settings'] = $this->language->get('text_timer_settings');
        $data['text_plain'] = $this->language->get('text_plain');
        $data['text_flip'] = $this->language->get('text_flip');

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/conversion_boost', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/conversion_boost/save', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $this->response->setOutput($this->load->view('extension/module/conversion_boost', $data));
    }

    public function save() {
        $this->load->language('extension/module/conversion_boost');

        $json = array();

        if ($this->validate()) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('module_conversion_boost', $this->request->post);
            $json['success'] = $this->language->get('text_success');
        } else {
            $json['error'] = $this->error['warning'];
        }

        $this->response->setOutput(json_encode($json));
    }

    public function validate() {
        $this->load->language('extension/module/conversion_boost');

        if (!$this->user->hasPermission('modify', 'extension/module/conversion_boost')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
