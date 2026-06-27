<?php

class ControllerExtensionModuleEnergyLabelEvent extends Controller
{
    public function onProductFormBefore(&$route, &$data, &$output)
    {
        $this->load->language('extension/module/energy_label');
        $this->load->model('extension/module/energy_label');

        $data['tab_energy_efficiency'] = $this->language->get('tab_energy_efficiency');
        $data['label_energy_class'] = $this->language->get('label_energy_class');
        $data['label_eu_label'] = $this->language->get('label_eu_label');
        $data['label_datasheet'] = $this->language->get('label_datasheet');
        $data['help_eu_label'] = $this->language->get('help_eu_label');
        $data['help_datasheet'] = $this->language->get('help_datasheet');
        $data['button_remove_file'] = $this->language->get('button_remove_file');
        $data['text_none'] = $this->language->get('text_none');
        $data['confirm_remove_file'] = $this->language->get('confirm_remove_file');
        $data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

        $data['energy_types'] = [
            'cooling' => $this->language->get('label_cooling'),
            'heating' => $this->language->get('label_heating'),
            'general' => $this->language->get('label_general'),
        ];

        $data['energy_classes'] = $this->model_extension_module_energy_label->getAllClasses();

        if (isset($this->request->post['energy_label'])) {
            $data['energy_label'] = $this->request->post['energy_label'];
        } elseif (isset($this->request->get['product_id'])) {
            $data['energy_label'] = $this->model_extension_module_energy_label->getProductLabels((int)$this->request->get['product_id']);
        } else {
            $data['energy_label'] = [];
        }
    }

    private function normalizeUploads($rawUploads)
    {
        $uploads = [];
        foreach (['cooling', 'heating', 'general'] as $type) {
            foreach (['label_file', 'datasheet_file'] as $field) {
                $uploads[$type][$field] = [
                    'name' => $rawUploads['name'][$type][$field] ?? '',
                    'type' => $rawUploads['type'][$type][$field] ?? '',
                    'tmp_name' => $rawUploads['tmp_name'][$type][$field] ?? '',
                    'error' => $rawUploads['error'][$type][$field] ?? 4,
                    'size' => $rawUploads['size'][$type][$field] ?? 0,
                ];
            }
        }
        return $uploads;
    }

    public function onAfterAddProduct(&$route, &$args, &$output)
    {
        $this->load->model('extension/module/energy_label');

        $rawUploads = isset($this->request->files['energy_label_upload'])
            ? $this->request->files['energy_label_upload']
            : [];

        $this->model_extension_module_energy_label->saveProductLabels(
            (int)$output,
            isset($this->request->post['energy_label']) ? $this->request->post['energy_label'] : [],
            $this->normalizeUploads($rawUploads)
        );
    }

    public function onAfterEditProduct(&$route, &$args, &$output)
    {
        $this->load->model('extension/module/energy_label');

        $rawUploads = isset($this->request->files['energy_label_upload'])
            ? $this->request->files['energy_label_upload']
            : [];

        $this->model_extension_module_energy_label->saveProductLabels(
            (int)$args[0],
            isset($this->request->post['energy_label']) ? $this->request->post['energy_label'] : [],
            $this->normalizeUploads($rawUploads)
        );
    }

}