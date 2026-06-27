<?php

class ControllerExtensionModuleEnergyLabel extends Controller
{
    public function index()
    {
        $status = $this->config->get('module_energy_label_status');

        if (!$status) {
            return '';
        }

        $this->load->model('extension/module/energy_label');

        $productId = isset($this->request->get['product_id'])
            ? (int)$this->request->get['product_id']
            : 0;

        if (!$productId) {
            return '';
        }

        $labels = $this->model_extension_module_energy_label->getProductLabels($productId);

        if (empty($labels)) {
            return '';
        }

        $this->load->language('extension/module/energy_label');

        $data['labels'] = $labels;
        $data['show_product'] = $this->config->get('module_energy_label_show_product');
        $data['show_category'] = $this->config->get('module_energy_label_show_category');
        $data['text_view_label'] = $this->language->get('text_view_label');
        $data['text_datasheet'] = $this->language->get('text_datasheet');
        $data['label_cooling'] = $this->language->get('label_cooling');
        $data['label_heating'] = $this->language->get('label_heating');
        $data['label_general'] = $this->language->get('label_general');

        $data['classes'] = $this->getResizedClasses();
        return $this->load->view('extension/module/energy_label_block', $data);
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
}