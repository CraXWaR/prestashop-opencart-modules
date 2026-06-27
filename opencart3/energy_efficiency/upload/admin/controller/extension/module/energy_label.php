<?php

class ControllerExtensionModuleEnergyLabel extends Controller
{

    /** Prefix used for all settings stored in oc_setting */
    const SETTING_PREFIX = 'module_energy_label_';

    /** Key under which attribute IDs are stored */
    const ATTR_SETTING_KEY = 'module_energy_label_attribute_ids';

    /** Attribute type definitions */
    const ATTR_TYPES = [
        'cooling' => 'Cooling',
        'heating' => 'Heating',
        'general' => 'General',
    ];

    /** Listing routes that get individual event codes (energy_label_default_N) */
    const LISTING_ROUTES = [
        'catalog/view/product/category/before',
        'catalog/view/product/search/before',
        'catalog/view/product/special/before',
        'catalog/view/product/manufacturer/before',
        'catalog/view/journal3/products/before',
    ];

    /** Error bag */
    private $error = [];

    public function install()
    {
        $this->load->model('setting/setting');
        $this->load->model('setting/event');
        $this->load->model('extension/module/energy_label');

        // Delegate to model - creates tables + attribute groups/attributes
        // Returns the attribute IDs needed for oc_setting persistence
        $attributeIds = $this->model_extension_module_energy_label->install();
        $this->model_extension_module_energy_label->saveAttributeIds($attributeIds);

        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/module/energy_label'
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/energy_label'
        );

//        Admin Events
        $this->model_setting_event->addEvent(
            'energy_label',
            'admin/view/catalog/product_form/before',
            'extension/module/energy_label_event/onProductFormBefore'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'admin/model/catalog/product/addProduct/after',
            'extension/module/energy_label_event/onAfterAddProduct'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'admin/model/catalog/product/editProduct/after',
            'extension/module/energy_label_event/onAfterEditProduct'
        );

//        Catalog Events
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/product/before',
            'extension/module/energy_label_event/onProductPageBefore'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/category/before',
            'extension/module/energy_label_event/onCategoryPageBefore'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/category/after',
            'extension/module/energy_label_event/onCategoryPageAfter'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/search/after',
            'extension/module/energy_label_event/onSearchPageAfter'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/special/after',
            'extension/module/energy_label_event/onSpecialPageAfter'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/manufacturer/after',
            'extension/module/energy_label_event/onManufacturerPageAfter'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/product/product/after',
            'extension/module/energy_label_event/onProductPageAfter'
        );
        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/common/header/before',
            'extension/module/energy_label_event/onHeaderBefore'
        );

        foreach (self::LISTING_ROUTES as $i => $eventRoute) {
            $this->model_setting_event->addEvent(
                'energy_label_default_' . $i,
                $eventRoute,
                'extension/module/energy_label_event/onProductListBefore'
            );
        }

        $this->model_setting_event->addEvent(
            'energy_label',
            'catalog/view/journal3/products/after',
            'extension/module/energy_label_event/onJournal3ProductsViewAfter'
        );

        $this->model_setting_event->addEvent(
            'energy_label_modal',
            'catalog/view/common/footer/after',
            'extension/module/energy_label_event/onInjectModal'
        );
    }

    public function uninstall()
    {
        $this->load->model('extension/module/energy_label');
        $this->load->model('setting/event');

        $this->model_extension_module_energy_label->uninstall();

        $this->model_setting_event->deleteEventByCode('energy_label');
        $this->model_setting_event->deleteEventByCode('energy_label_modal');

        for ($i = 0; $i < count(self::LISTING_ROUTES); $i++) {
            $this->model_setting_event->deleteEventByCode('energy_label_default_' . $i);
        }
    }

    public function index(): void
    {
        $this->load->language('extension/module/energy_label');
        $this->load->model('extension/module/energy_label');
        $this->load->model('setting/setting');

        $this->document->addStyle('view/stylesheet/energy_label.css');
        $this->document->setTitle($this->language->get('heading_title'));

        // ---- Handle POST (save) --------------------------------------------
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if ($this->validateSettingsForm()) {
                if (isset($this->request->post['energy_classes'])) {
                    foreach ($this->request->post['energy_classes'] as $class) {

                        $name = trim($class['name'] ?? '');
                        $icon = trim($class['icon'] ?? '');

                        // skip empty rows
                        if ($name === '' && $icon === '') {
                            continue;
                        }

                        if ($icon === '') {
                            $this->error['icon'] = $this->language->get('error_icon');
                            break;
                        }

                        $realPath = realpath(DIR_IMAGE . $icon);
                        $realBase = realpath(DIR_IMAGE);

                        if (
                            !$realPath ||
                            strpos($realPath, $realBase) !== 0 ||
                            !is_file($realPath)
                        ) {
                            $this->error['icon'] = $this->language->get('error_icon');
                            break;
                        }
                    }
                }

                if (!$this->error) {
                    $allowedKeys = [
                        'module_energy_label_status',
                        'module_energy_label_show_product',
                        'module_energy_label_show_category',
                        'module_energy_label_show_search',
                        'module_energy_label_show_special',
                        'module_energy_label_show_manufacturer',
                        'module_energy_label_show_journal3',
                        'module_energy_label_custom_css',
                    ];

                    $settingData = [];

                    foreach ($allowedKeys as $key) {
                        $settingData[$key] = $this->request->post[$key] ?? '';
                    }

                    $this->model_setting_setting->editSetting(
                        'module_energy_label',
                        $settingData
                    );

                    $attributeIds = json_decode($this->config->get('module_energy_label_attribute_ids'), true);

                    if (empty($attributeIds)) {
                        $attributeIds = $this->model_extension_module_energy_label->installAttributes();
                    }

                    $this->model_extension_module_energy_label->saveAttributeIds($attributeIds);

                    if (isset($this->request->post['energy_classes'])) {
                        foreach ($this->request->post['energy_classes'] as $class) {
                            $class_id = (int)($class['energy_class_id'] ?? 0);
                            $name = trim($class['name'] ?? '');
                            $icon = trim($class['icon'] ?? '');
                            $sortOrder = (int)($class['sort_order'] ?? 0);

                            // skip empty rows
                            if ($name === '' && $icon === '') {
                                continue;
                            }

                            if ($this->model_extension_module_energy_label->classNameExists($name, $class_id)) {
                                $this->error['warning'] = $this->language->get('error_class_name_duplicate');
                                break;
                            }

                            if ($class_id > 0) {
                                $oldName = $this->model_extension_module_energy_label->getClassName($class_id);
                                $this->model_extension_module_energy_label->updateClass($class_id, $name, $icon, $sortOrder);

                                if ($oldName !== $name) {
                                    $this->model_extension_module_energy_label->renameAttributeValues($oldName, $name);
                                }
                            } else {
                                $this->model_extension_module_energy_label->addClass($name, $icon, $sortOrder);
                            }
                        }
                    }
                    $this->session->data['success'] = $this->language->get('text_success');

                    if (isset($this->request->post['stay'])) {
                        $this->response->redirect(
                            $this->url->link(
                                'extension/module/energy_label',
                                'user_token=' . $this->session->data['user_token'],
                                true
                            )
                        );
                    } else {
                        $this->response->redirect(
                            $this->url->link(
                                'marketplace/extension',
                                'user_token=' . $this->session->data['user_token'] . '&type=module',
                                true
                            )
                        );
                    }
                }
            }
        }

        // ---- Build data for the view
        $data = $this->buildCommonData();

        // On a failed POST, restore the user's unsaved edits instead of reloading from DB
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->error && isset($this->request->post['energy_classes'])) {
            $classes = [];
            foreach ($this->request->post['energy_classes'] as $posted) {
                $icon = trim($posted['icon'] ?? '');
                $thumb = $data['placeholder'];
                if ($icon && is_file(DIR_IMAGE . $icon)) {
                    $thumb = $this->model_tool_image->resize($icon, 40, 40);
                }
                $classes[] = [
                    'energy_class_id' => (int)($posted['energy_class_id'] ?? 0),
                    'name'            => trim($posted['name'] ?? ''),
                    'icon'            => $icon,
                    'sort_order'      => (int)($posted['sort_order'] ?? 0),
                    'thumb'           => $thumb,
                ];
            }
            $data['energy_classes'] = $classes;
        }

        // Settings
        $data['module_energy_label_status'] = $this->getSettingOrPost('module_energy_label_status', '0');
        $data['module_energy_label_show_product'] = $this->getSettingOrPost('module_energy_label_show_product', '1');
        $data['module_energy_label_show_category'] = $this->getSettingOrPost('module_energy_label_show_category', '1');
        $data['module_energy_label_show_search'] = $this->getSettingOrPost('module_energy_label_show_search', '1');
        $data['module_energy_label_show_special'] = $this->getSettingOrPost('module_energy_label_show_special', '1');
        $data['module_energy_label_show_manufacturer'] = $this->getSettingOrPost('module_energy_label_show_manufacturer', '1');
        $data['module_energy_label_show_journal3'] = $this->getSettingOrPost('module_energy_label_show_journal3', '1');
        $data['module_energy_label_custom_css'] = $this->getSettingOrPost('module_energy_label_custom_css', '');

        // Error strings
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_icon'] = $this->error['icon'] ?? '';

        // Success strings
        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->response->setOutput(
            $this->load->view('extension/module/energy_label', $data)
        );
    }

    /** Add or update an energy class */
    public function saveClass()
    {
        $this->load->language('extension/module/energy_label');
        $this->load->model('extension/module/energy_label');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/module/energy_label')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $classId = (int)($this->request->post['energy_class_id'] ?? 0);
            $name = trim($this->request->post['name'] ?? '');
            $icon = trim($this->request->post['icon'] ?? '');
            $sortOrder = (int)($this->request->post['sort_order'] ?? 0);

            if ($name === '') {
                $json['error'] = $this->language->get('error_class_name_required');
            } elseif ($this->model_extension_module_energy_label->classNameExists($name, $classId)) {
                $json['error'] = $this->language->get('error_class_name_duplicate');
            } else {
                if ($classId > 0) {
                    // UPDATE — also cascade-rename attribute values if name changed
                    $oldName = $this->model_extension_module_energy_label->getClassName($classId);
                    $this->model_extension_module_energy_label->updateClass($classId, $name, $icon, $sortOrder);
                    if ($oldName !== $name) {
                        $this->model_extension_module_energy_label->renameAttributeValues($oldName, $name);
                    }
                    $json['success'] = $this->language->get('text_class_updated');
                    $json['class_id'] = $classId;
                } else {
                    // INSERT
                    $newId = $this->model_extension_module_energy_label->addClass($name, $icon, $sortOrder);
                    $json['success'] = $this->language->get('text_class_added');
                    $json['class_id'] = $newId;
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /** Delete an energy class — only if no product uses it */
    public function deleteClass()
    {
        $this->load->language('extension/module/energy_label');
        $this->load->model('extension/module/energy_label');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/module/energy_label')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $classId = (int)($this->request->post['energy_class_id'] ?? 0);

            if ($classId < 1) {
                $json['error'] = $this->language->get('error_invalid_class');
            } else {
                $usageCount = $this->model_extension_module_energy_label->countClassUsage($classId);

                if ($usageCount > 0) {
                    // Tell the admin how many products are affected
                    $json['error'] = sprintf(
                        $this->language->get('error_class_in_use'),
                        $usageCount
                    );
                } else {
                    $this->model_extension_module_energy_label->deleteClass($classId);
                    $json['success'] = $this->language->get('text_class_deleted');
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Returns POST value if present, otherwise the value from oc_setting,
     * or $default if neither exists.
     */
    private function getSettingOrPost($key, $default = '')
    {
        if (isset($this->request->post[$key])) {
            return $this->request->post[$key];
        }

        $value = $this->config->get($key);
        return $value !== null ? $value : $default;
    }

    /**
     * Common breadcrumb / action data shared by all views.
     */
    private function buildCommonData(): array
    {
        $token = $this->session->data['user_token'];
        $data['user_token'] = $token;

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $token, true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $token . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/energy_label', 'user_token=' . $token, true),
            ],
        ];

        $data['action'] = $this->url->link('extension/module/energy_label', 'user_token=' . $token, true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $token . '&type=module', true);
        $data['save_class'] = $this->url->link('extension/module/energy_label/saveClass', 'user_token=' . $token, false);
        $data['delete_class'] = $this->url->link('extension/module/energy_label/deleteClass', 'user_token=' . $token, false);
        $data['button_save_stay'] = $this->language->get('button_save_stay');

//        file import
        $data['tab_bulk'] = $this->language->get('tab_bulk');
        $data['export_url'] = $this->url->link('extension/module/energy_label/exportExcel', 'user_token=' . $token, true);
        $data['import_url'] = $this->url->link('extension/module/energy_label/importExcel', 'user_token=' . $token, true);

        $data['label_export'] = $this->language->get('label_export');
        $data['label_import'] = $this->language->get('label_import');
        $data['label_import_excel'] = $this->language->get('label_import_excel');
        $data['label_import_zip'] = $this->language->get('label_import_zip');
        $data['button_export'] = $this->language->get('button_export');
        $data['button_import'] = $this->language->get('button_import');
        $data['help_export'] = $this->language->get('help_export');
        $data['help_import'] = $this->language->get('help_import');
        $data['help_import_zip'] = $this->language->get('help_import_zip');
        $data['text_importing'] = $this->language->get('text_importing');
        $data['text_no_file_selected'] = $this->language->get('text_no_file_selected');
        $data['error_import_file_type'] = $this->language->get('error_import_file_type');
        $data['error_import_zip_type'] = $this->language->get('error_import_zip_type');

//        Disable badge show
        $data['label_show_search'] = $this->language->get('label_show_search');
        $data['label_show_special'] = $this->language->get('label_show_special');
        $data['label_show_manufacturer'] = $this->language->get('label_show_manufacturer');
        $data['label_show_journal3'] = $this->language->get('label_show_journal3');

        $this->load->model('tool/image');
        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 40, 40);

        $classes = $this->model_extension_module_energy_label->getAllClasses();

        foreach ($classes as &$class) {
            if ($class['icon'] && is_file(DIR_IMAGE . $class['icon'])) {
                $class['thumb'] = $this->model_tool_image->resize($class['icon'], 40, 40);
            } else {
                $class['thumb'] = $data['placeholder'];
            }
        }

        $data['energy_classes'] = $classes;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        return $data;
    }

    /**
     * Validates the settings form submission.
     * Returns true if valid, false otherwise (populates $this->error).
     */
    private function validateSettingsForm()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/energy_label')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return empty($this->error);
    }

    /**
     * Removes files from efficiency tab
     */
    public function removeProductFile()
    {
        $this->load->language('extension/module/energy_label');
        $this->load->model('extension/module/energy_label');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/module/energy_label')) {
            $json['error'] = $this->language->get('error_permission');
            $this->jsonOutput($json);
            return;
        }

        $productId = (int)($this->request->post['product_id'] ?? 0);
        $type = $this->request->post['type'] ?? '';
        $field = $this->request->post['field'] ?? '';

        if (!$productId || !in_array($type, ['cooling', 'heating', 'general']) || !in_array($field, ['label_file', 'datasheet_file'])) {
            $json['error'] = 'Invalid request';
        } else {
            $this->model_extension_module_energy_label->removeProductFile($productId, $type, $field);
            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function exportExcel(): void
    {
        if (!$this->user->isLogged()) {
            return;
        }

        $this->load->model('extension/module/energy_label');
        $rows = $this->model_extension_module_energy_label->getAllProductsWithLabels();

        $filename = 'energy_labels_' . date('Ymd_His') . '.xlsx';

        $this->response->addHeader('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->response->addHeader('Cache-Control: max-age=0');
        $this->response->setOutput($this->buildXlsx($rows));
    }

    /**
     * Builds a real .xlsx binary using only ZipArchive + PHP strings.
     * No external libraries required.
     *
     * Columns:
     *   A  product_id          (readonly, grey)
     *   B  product_name        (readonly, grey)
     *   C  model               (readonly, grey)
     *   D  cooling_class       (editable)
     *   E  heating_class       (editable)
     *   F  general_class       (editable)
     *   G  cooling_label_file  (editable — filename only, e.g. label.pdf)
     *   H  cooling_datasheet_file
     *   I  heating_label_file
     *   J  heating_datasheet_file
     *   K  general_label_file
     *   L  general_datasheet_file
     */
    private function buildXlsx(array $rows): string
    {
        $esc = function (string $v): string {
            return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        };

        $strings = [];
        $strMap  = [];
        $strIndex = function (string $v) use (&$strings, &$strMap): int {
            if (!isset($strMap[$v])) {
                $strMap[$v] = count($strings);
                $strings[]  = $v;
            }
            return $strMap[$v];
        };

        $headers = [
            'product_id',
            'product_name',
            'model',
            'cooling_class',
            'heating_class',
            'general_class',
            'cooling_label_file',
            'cooling_datasheet_file',
            'heating_label_file',
            'heating_datasheet_file',
            'general_label_file',
            'general_datasheet_file',
        ];

        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        // Column widths:
        $colWidths = [12, 40, 20, 18, 18, 18, 28, 28, 28, 28, 28, 28];

        $sheetRows = '';

        // Header row — style 1 (bold blue)
        $sheetRows .= '<row r="1">';
        foreach ($headers as $i => $h) {
            $si = $strIndex($h);
            $sheetRows .= '<c r="' . $cols[$i] . '1" t="s" s="1"><v>' . $si . '</v></c>';
        }
        $sheetRows .= '</row>';

        // Data rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $cells = [
                (string)(int)$row['product_id'],
                (string)($row['product_name'] ?? ''),
                (string)($row['model'] ?? ''),
                (string)($row['cooling_class'] ?? ''),
                (string)($row['heating_class'] ?? ''),
                (string)($row['general_class'] ?? ''),
                (string)($row['cooling_label_file'] ?? ''),
                (string)($row['cooling_datasheet_file'] ?? ''),
                (string)($row['heating_label_file'] ?? ''),
                (string)($row['heating_datasheet_file'] ?? ''),
                (string)($row['general_label_file'] ?? ''),
                (string)($row['general_datasheet_file'] ?? ''),
            ];

            $sheetRows .= '<row r="' . $rowNum . '">';
            foreach ($cells as $i => $val) {
                $col = $cols[$i];
                if ($i === 0) {
                    // product_id — numeric, grey
                    $sheetRows .= '<c r="' . $col . $rowNum . '" s="2"><v>' . (int)$val . '</v></c>';
                } else {
                    $si = $strIndex($val);
                    // columns 1,2 (B,C) are readonly grey; rest are editable (default style)
                    $style = ($i < 3) ? ' s="2"' : '';
                    $sheetRows .= '<c r="' . $col . $rowNum . '" t="s"' . $style . '><v>' . $si . '</v></c>';
                }
            }
            $sheetRows .= '</row>';
            $rowNum++;
        }

        // ---- Shared strings XML
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"';
        $ssXml .= ' count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
        foreach ($strings as $s) {
            $ssXml .= '<si><t xml:space="preserve">' . $esc($s) . '</t></si>';
        }
        $ssXml .= '</sst>';

        // ---- Styles XML
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
  </fonts>
  <fills count="4">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF2E75B6"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF2F2F2"/></patternFill></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/>
  </cellXfs>
</styleSheet>';

        // ---- Column widths XML
        $colsXml = '<cols>';
        foreach ($colWidths as $i => $w) {
            $n = $i + 1;
            $colsXml .= '<col min="' . $n . '" max="' . $n . '" width="' . $w . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        // ---- Sheet XML
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetViews>
    <sheetView workbookViewId="0">
      <pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>
    </sheetView>
  </sheetViews>
  ' . $colsXml . '
  <sheetData>' . $sheetRows . '</sheetData>
</worksheet>';

        // ---- Workbook XML
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Energy Labels" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

        $pkgRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"   ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml"       ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml"              ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $pkgRels);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->close();

        $binary = file_get_contents($tmp);
        unlink($tmp);

        return $binary;
    }

    /**
     * Import energy labels from uploaded Excel/CSV + optional ZIP of files.
     *
     * POST files expected:
     *   import_file  — .xlsx or .csv
     *   import_zip   — .zip with folders: cooling/, heating/, general/  (optional)
     *
     * Rules:
     *   - Class cells: overwrite only if non-empty, skip blank
     *   - File cells:  overwrite only if non-empty AND file exists in ZIP, skip blank
     */
    public function importExcel(): void
    {
        $this->load->language('extension/module/energy_label');
        $this->load->model('extension/module/energy_label');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/module/energy_label')) {
            $json['error'] = $this->language->get('error_permission');
            $this->jsonOutput($json);
            return;
        }

        if (empty($_FILES['import_file']['tmp_name'])) {
            $json['error'] = $this->language->get('error_import_no_file');
            $this->jsonOutput($json);
            return;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'csv'])) {
            $json['error'] = $this->language->get('error_import_file_type');
            $this->jsonOutput($json);
            return;
        }

        // ---- Extract ZIP files into a temp directory
        $extractedFiles = [];
        $tmpExtractDir = null;

        if (!empty($_FILES['import_zip']['tmp_name']) && $_FILES['import_zip']['error'] === UPLOAD_ERR_OK) {
            $zipExt = strtolower(pathinfo($_FILES['import_zip']['name'], PATHINFO_EXTENSION));
            if ($zipExt !== 'zip') {
                $json['error'] = $this->language->get('error_import_zip_type');
                $this->jsonOutput($json);
                return;
            }

            $tmpExtractDir = sys_get_temp_dir() . '/el_import_' . uniqid();
            mkdir($tmpExtractDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($_FILES['import_zip']['tmp_name']) === true) {
                $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
                $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $zipName = $zip->getNameIndex($i);
                    $zipLower = strtolower($zipName);

                    // Only extract files in cooling/, heating/, general/ folders
                    if (!preg_match('#^(cooling|heating|general)/[^/]+$#i', $zipName)) {
                        continue;
                    }

                    $fileExt = strtolower(pathinfo($zipName, PATHINFO_EXTENSION));
                    if (!in_array($fileExt, $allowedExts)) {
                        continue;
                    }

                    // Sanitize filename — no path traversal
                    $parts = explode('/', $zipName);
                    $folder = strtolower($parts[0]);
                    $basename = basename($parts[1]);
                    $basename = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $basename);

                    $destDir = $tmpExtractDir . '/' . $folder;
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    $destPath = $destDir . '/' . $basename;
                    file_put_contents($destPath, $zip->getFromIndex($i));

                    // Verify MIME after extraction
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($destPath);
                    if (!in_array($mime, $allowedMimes)) {
                        unlink($destPath);
                        continue;
                    }

                    // Key: "cooling/label.pdf"
                    $extractedFiles[$folder . '/' . $basename] = $destPath;
                }
                $zip->close();
            }
        }

        try {
            $rows = ($ext === 'csv')
                ? $this->parseImportCsv($file['tmp_name'])
                : $this->parseImportXlsx($file['tmp_name']);

            if (empty($rows)) {
                $json['error'] = $this->language->get('error_import_empty');
                $this->jsonOutput($json);
                $this->cleanupExtractDir($tmpExtractDir);
                return;
            }

            $result = $this->model_extension_module_energy_label->bulkUpdateLabels($rows, $extractedFiles);

            $json['success'] = sprintf(
                $this->language->get('text_import_success'),
                $result['updated'],
                $result['skipped']
            );

        } catch (\Exception $e) {
            $json['error'] = $this->language->get('error_import_parse') . ' ' . $e->getMessage();
        }

        $this->cleanupExtractDir($tmpExtractDir);
        $this->jsonOutput($json);
    }

    private function jsonOutput(array $json): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function cleanupExtractDir(?string $dir): void
    {
        if (!$dir || !is_dir($dir)) {
            return;
        }
        foreach (new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                     RecursiveIteratorIterator::CHILD_FIRST
                 ) as $f) {
            $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
        }
        rmdir($dir);
    }

    private function parseImportCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        $headers = array_map('trim', $headers);

        while (($line = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, array_pad($line, count($headers), ''));
            if ($row) {
                $rows[] = $row;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Parse .xlsx using only ZipArchive + SimpleXML.
     * Strips namespaces to avoid XPath prefix issues across PHP versions.
     */
    private function parseImportXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception('Could not open xlsx file.');
        }

        $strings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $ssXml = $this->stripXmlNamespaces($ssXml);
            $ss = new SimpleXMLElement($ssXml);
            foreach ($ss->si as $si) {
                $text = '';
                foreach ($si->xpath('.//t') as $t) {
                    $text .= (string)$t;
                }
                $strings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new \Exception('Could not read sheet data from xlsx.');
        }

        $sheetXml = $this->stripXmlNamespaces($sheetXml);
        $sheet = new SimpleXMLElement($sheetXml);

        $data = [];
        foreach ($sheet->xpath('//row') as $row) {
            $rowData = [];
            foreach ($row->xpath('c') as $cell) {
                $type = (string)($cell['t'] ?? '');
                $value = (string)($cell->v ?? '');

                if ($type === 's') {
                    $value = $strings[(int)$value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string)($cell->is->t ?? '');
                }

                $ref = (string)($cell['r'] ?? '');
                $colLetter = preg_replace('/[0-9]/', '', $ref);
                $colIndex = 0;
                foreach (str_split($colLetter) as $char) {
                    $colIndex = $colIndex * 26 + (ord($char) - ord('A') + 1);
                }
                $colIndex--;

                $rowData[$colIndex] = $value;
            }
            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }

        if (empty($data)) {
            return [];
        }

        $headers = array_map('trim', array_shift($data));
        $rows = [];

        foreach ($data as $line) {
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = trim((string)($line[$i] ?? ''));
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function stripXmlNamespaces(string $xml): string
    {
        $xml = preg_replace('/\s+xmlns(?::\w+)?="[^"]*"/', '', $xml);
        $xml = preg_replace('/<(\/?)\w+:/', '<$1', $xml);
        return $xml;
    }
}