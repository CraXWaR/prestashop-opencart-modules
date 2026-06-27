<?php

class ControllerExtensionModuleCategoryFaq extends Controller
{
    public function install()
    {
        $this->load->model('setting/setting');
        $this->load->model('setting/event');
        $this->load->model('extension/module/category_faq');
        $this->model_extension_module_category_faq->install();

        $this->model_setting_event->addEvent(
            'category_faq',
            'admin/model/catalog/category/editCategory/after',
            'extension/module/category_faq_event/onAfterEditCategory'
        );

        $this->model_setting_event->addEvent(
            'category_faq',
            'admin/view/catalog/category_form/before',
            'extension/module/category_faq_event/onBeforeEditCategory'
        );

        $this->model_setting_event->addEvent(
            'category_faq',
            'admin/model/catalog/category/deleteCategory/after',
            'extension/module/category_faq_event/onAfterDeleteCategory'
        );

        $this->model_setting_event->addEvent(
            'category_faq',
            'catalog/view/product/category/before',
            'extension/module/category_faq_event/onCategoryPageBefore'
        );
    }

    public function uninstall()
    {
        $this->load->model('extension/module/category_faq');
        $this->load->model('setting/event');

        $this->model_extension_module_category_faq->uninstall();
        $this->model_setting_event->deleteEventByCode('category_faq');

    }

    public function index()
    {
        $this->load->language('extension/module/category_faq');
        $this->load->model('extension/module/category_faq');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('view/javascript/category_faq.js');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

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
            'href' => $this->url->link('extension/module/category_faq', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/category_faq/save', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['entry_status'] = $this->language->get('entry_status');
        $data['status'] = $this->config->get('module_category_faq_status') ?? '0';

        $this->response->setOutput($this->load->view('extension/module/category_faq', $data));
    }

    public function save()
    {
        $this->load->language('extension/module/category_faq');

        $json = array();

        if ($this->validate()) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('module_category_faq', $this->request->post);
            $json['success'] = $this->language->get('text_success');
        } else {
            $json['error'] = $this->error['warning'];
        }

        $this->response->setOutput(json_encode($json));
    }

    public function validate()
    {
        $this->load->language('extension/module/category_faq');

        if (!$this->user->hasPermission('modify', 'extension/module/category_faq')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
