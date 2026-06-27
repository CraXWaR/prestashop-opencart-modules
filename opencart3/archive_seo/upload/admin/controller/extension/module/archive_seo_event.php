<?php
class ControllerExtensionModuleArchiveSeoEvent extends Controller
{
    public function adminProductForm(&$route, &$data, &$output)
    {
        $this->load->language('extension/module/archive_seo');

        $data['text_seo_archive'] = $this->language->get('text_seo_archive');
        $data['status'] = isset($data['status']) ? (int) $data['status'] : 1;
    }
}