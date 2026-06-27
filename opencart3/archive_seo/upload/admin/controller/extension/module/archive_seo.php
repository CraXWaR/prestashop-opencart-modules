<?php

class ControllerExtensionModuleArchiveSeo extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/module/archive_seo');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_archive_seo', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/archive_seo', 'user_token=' . $this->session->data['user_token'], true),
        ];

        // Errors
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

        // Form action
        $data['action'] = $this->url->link('extension/module/archive_seo', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Settings
        $data['module_archive_seo_status'] = $this->config->get('module_archive_seo_status');

        // Texts
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/archive_seo', $data));
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_archive_seo', ['module_archive_seo_status' => 1]);

        $this->load->model('setting/event');

        // Admin event
        $this->model_setting_event->addEvent(
            'archive_seo',
            'admin/view/catalog/product_form/before',
            'extension/module/archive_seo_event/adminProductForm'
        );

        // Catalog events
        $this->model_setting_event->addEvent(
            'archive_seo',
            'catalog/model/catalog/product/getProduct/after',
            'extension/module/archive_seo_event/flagArchivedProduct'
        );

        $this->model_setting_event->addEvent(
            'archive_seo',
            'catalog/view/product/product/after',
            'extension/module/archive_seo_event/injectArchiveNotice'
        );
    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('archive_seo');

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_archive_seo');
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/archive_seo')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
