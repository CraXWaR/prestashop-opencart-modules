<?php

class ModelExtensionModuleEnergyLabel extends Model
{
    public function install()
    {
        $this->createTables();
        $this->installDefaultClasses();
        return $this->installAttributes();
    }

    /**
     * Uninstall — intentionally non-destructive.
     */
    public function uninstall()
    {
        // Non-destructive — does NOT drop tables, attribute groups or uploaded files.
        // OpenCart handles de-registration from oc_extension automatically.
//        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "energy_label_class`");
//        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "energy_label_product`");
    }

    /**
     * Creates the two module tables.
     * Uses CREATE TABLE IF NOT EXISTS — completely safe to call more than once.
     */
    public function createTables()
    {
        // --- Table 1: Global energy class definitions
        // Stores the configured classes (A+++, A++, … G) with their icons.
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "energy_label_class` (
                `energy_class_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name`            VARCHAR(20)  NOT NULL COMMENT 'Class symbol, e.g. A+++',
                `icon`            VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Path relative to OC root',
                `sort_order`      INT(3)  NOT NULL DEFAULT 0,
                PRIMARY KEY (`energy_class_id`),
                UNIQUE KEY `uq_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // --- Table 2: Per-product energy label data
        // One row per (product_id, type) combination.
        // type: 'cooling' | 'heating' | 'general'
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "energy_label_product` (
                `energy_label_id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `product_id`              INT(11) UNSIGNED NOT NULL,
                `type`                    ENUM('cooling','heating','general') NOT NULL,
                `energy_class_id`         INT(11) UNSIGNED DEFAULT NULL
                                              COMMENT 'FK → oc_energy_label_class',
                `label_file`              VARCHAR(255) NOT NULL DEFAULT ''
                                              COMMENT 'Stored path to EU label image/PDF',
                `label_file_original`     VARCHAR(255) NOT NULL DEFAULT ''
                                              COMMENT 'Original uploaded filename (display only)',
                `datasheet_file`          VARCHAR(255) NOT NULL DEFAULT ''
                                              COMMENT 'Stored path to product datasheet PDF',
                `datasheet_file_original` VARCHAR(255) NOT NULL DEFAULT ''
                                              COMMENT 'Original uploaded filename (display only)',
                PRIMARY KEY (`energy_label_id`),
                UNIQUE KEY `uq_product_type` (`product_id`, `type`),
                KEY `idx_product_id` (`product_id`),
                KEY `idx_class_id`   (`energy_class_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    /**
     * Creates three attribute groups (Cooling, Heating, General) each with
     * one "Class" attribute beneath it.
     */
    public function installAttributes()
    {
        $types = [
            'cooling' => 'Cooling',
            'heating' => 'Heating',
            'general' => 'General',
        ];

        $ids = [];

        foreach ($types as $key => $groupName) {
            $groupId = $this->getOrCreateAttributeGroup($groupName);
            $attributeId = $this->getOrCreateAttribute('Class', $groupId);

            $ids[$key] = [
                'group_id' => $groupId,
                'attribute_id' => $attributeId,
            ];
        }

        return $ids;
    }

    /**
     * Creates all energy classes in Europe standart.
     */
    private function installDefaultClasses()
    {
        $defaultClasses = [
            ['name' => 'A+++', 'sort_order' => 1, 'icon' => 'catalog/energy_labels/badges/A+++.png'],
            ['name' => 'A++', 'sort_order' => 2, 'icon' => 'catalog/energy_labels/badges/A++.png'],
            ['name' => 'A+', 'sort_order' => 3, 'icon' => 'catalog/energy_labels/badges/A+.png'],
            ['name' => 'A', 'sort_order' => 4, 'icon' => 'catalog/energy_labels/badges/A.png'],
            ['name' => 'B', 'sort_order' => 5, 'icon' => 'catalog/energy_labels/badges/B.png'],
            ['name' => 'C', 'sort_order' => 6, 'icon' => 'catalog/energy_labels/badges/C.png'],
            ['name' => 'D', 'sort_order' => 7, 'icon' => 'catalog/energy_labels/badges/D.png'],
            ['name' => 'E', 'sort_order' => 8, 'icon' => 'catalog/energy_labels/badges/E.png'],
            ['name' => 'F', 'sort_order' => 9, 'icon' => 'catalog/energy_labels/badges/F.png'],
            ['name' => 'G', 'sort_order' => 10, 'icon' => 'catalog/energy_labels/badges/G.png'],
        ];

        foreach ($defaultClasses as $class) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "energy_label_class`
                    (`name`, `icon`, `sort_order`)
                VALUES (
                    '" . $this->db->escape($class['name']) . "',
                    '" . $this->db->escape($class['icon']) . "',
                    " . (int)$class['sort_order'] . "
                )
                ON DUPLICATE KEY UPDATE
                    `icon`       = '" . $this->db->escape($class['icon']) . "',
                    `sort_order` = " . (int)$class['sort_order'] . "
            ");
        }
    }

    public function saveAttributeIds($attributeIds)
    {
        $this->db->query("
        INSERT INTO `" . DB_PREFIX . "setting` 
            (store_id, code, `key`, value, serialized)
        VALUES (
            0,
            'module_energy_label',
            'module_energy_label_attribute_ids',
            '" . $this->db->escape(json_encode($attributeIds)) . "',
            0
        )
        ON DUPLICATE KEY UPDATE
            value = '" . $this->db->escape(json_encode($attributeIds)) . "'
    ");
    }

    private function getOrCreateAttributeGroup(string $name)
    {
        $defaultLangId = (int)$this->config->get('config_language_id');

        $result = $this->db->query("
            SELECT agd.`attribute_group_id`
            FROM `" . DB_PREFIX . "attribute_group_description` agd
            WHERE agd.`name`        = '" . $this->db->escape($name) . "'
              AND agd.`language_id` = " . $defaultLangId . "
            LIMIT 1
        ");

        if ($result->num_rows) {
            return (int)$result->row['attribute_group_id'];
        }

        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "attribute_group`
                (`sort_order`)
            VALUES (0)
        ");
        $groupId = (int)$this->db->getLastId();

        foreach ($this->getInstalledLanguageIds() as $langId) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "attribute_group_description`
                    (`attribute_group_id`, `language_id`, `name`)
                VALUES (
                    " . $groupId . ",
                    " . $langId . ",
                    '" . $this->db->escape($name) . "'
                )
            ");
        }

        return $groupId;
    }

    private function getOrCreateAttribute(string $name, int $groupId)
    {
        $defaultLangId = (int)$this->config->get('config_language_id');

        $result = $this->db->query("
            SELECT ad.`attribute_id`
            FROM `" . DB_PREFIX . "attribute_description` ad
            INNER JOIN `" . DB_PREFIX . "attribute` a
                ON a.`attribute_id` = ad.`attribute_id`
            WHERE ad.`name`              = '" . $this->db->escape($name) . "'
              AND ad.`language_id`       = " . $defaultLangId . "
              AND a.`attribute_group_id` = " . (int)$groupId . "
            LIMIT 1
        ");

        if ($result->num_rows) {
            return (int)$result->row['attribute_id'];
        }

        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "attribute`
                (`attribute_group_id`, `sort_order`)
            VALUES (" . (int)$groupId . ", 0)
        ");
        $attributeId = (int)$this->db->getLastId();

        foreach ($this->getInstalledLanguageIds() as $langId) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "attribute_description`
                    (`attribute_id`, `language_id`, `name`)
                VALUES (
                    " . $attributeId . ",
                    " . $langId . ",
                    '" . $this->db->escape($name) . "'
                )
            ");
        }

        return $attributeId;
    }

    private function getInstalledLanguageIds()
    {
        $result = $this->db->query("
            SELECT `language_id`
            FROM `" . DB_PREFIX . "language`
            WHERE `status` = 1
        ");

        return array_column($result->rows, 'language_id');
    }

    public function getAllClasses()
    {
        $result = $this->db->query("
                SELECT *
                FROM `" . DB_PREFIX . "energy_label_class`
                ORDER BY `sort_order` ASC, `name` ASC
            ");

        return $result->rows;
    }

    public function getClassName(int $classId)
    {
        $result = $this->db->query("
            SELECT `name`
            FROM `" . DB_PREFIX . "energy_label_class`
            WHERE `energy_class_id` = " . (int)$classId . "
            LIMIT 1
        ");

        return $result->num_rows ? $result->row['name'] : '';
    }

    public function classNameExists(string $name, int $excludeId = 0): bool
    {
        $result = $this->db->query("
            SELECT `energy_class_id`
            FROM `" . DB_PREFIX . "energy_label_class`
            WHERE `name` = '" . $this->db->escape($name) . "'
            AND `energy_class_id` != " . (int)$excludeId . "
            LIMIT 1
        ");

        return (bool)$result->num_rows;
    }

    public function addClass(string $name, string $icon, int $sortOrder)
    {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "energy_label_class`
                (`name`, `icon`, `sort_order`)
            VALUES (
                '" . $this->db->escape($name) . "',
                '" . $this->db->escape($icon) . "',
                " . (int)$sortOrder . "
            )
        ");

        return (int)$this->db->getLastId();
    }

    public function updateClass(int $classId, string $name, string $icon, int $sortOrder)
    {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "energy_label_class`
            SET
                `name`       = '" . $this->db->escape($name) . "',
                `icon`       = '" . $this->db->escape($icon) . "',
                `sort_order` = " . (int)$sortOrder . "
            WHERE `energy_class_id` = " . (int)$classId . "
        ");
    }

    public function deleteClass(int $classId)
    {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "energy_label_class`
            WHERE `energy_class_id` = " . (int)$classId . "
        ");
    }

    public function countClassUsage(int $classId)
    {
        $result = $this->db->query("
            SELECT COUNT(*) AS `total`
            FROM `" . DB_PREFIX . "energy_label_product`
            WHERE `energy_class_id` = " . (int)$classId . "
        ");

        return (int)$result->row['total'];
    }

    public function renameAttributeValues(string $oldName, string $newName)
    {
        $attributeIds = json_decode($this->config->get('module_energy_label_attribute_ids'), true);

        if (empty($attributeIds)) {
            return;
        }

        $ids = [];
        foreach ($attributeIds as $type => $data) {
            if (!empty($data['attribute_id'])) {
                $ids[] = (int)$data['attribute_id'];
            }
        }

        if (empty($ids)) {
            return;
        }

        $this->db->query("
        UPDATE `" . DB_PREFIX . "product_attribute`
        SET `text` = '" . $this->db->escape($newName) . "'
        WHERE `text` = '" . $this->db->escape($oldName) . "'
        AND `attribute_id` IN (" . implode(',', $ids) . ")
    ");
    }

    public function getProductLabels($product_id)
    {
        $result = [];

        $query = $this->db->query("
        SELECT elp.*, elc.name AS class_name
        FROM `" . DB_PREFIX . "energy_label_product` elp
        LEFT JOIN `" . DB_PREFIX . "energy_label_class` elc
            ON elp.energy_class_id = elc.energy_class_id
        WHERE elp.product_id = " . (int)$product_id . "
    ");

        foreach ($query->rows as $row) {
            $result[$row['type']] = $row;
        }

        return $result;
    }

    public function saveProductLabels($product_id, $labels, $uploads)
    {
        $attributeIds = json_decode($this->config->get('module_energy_label_attribute_ids'), true);

        foreach (['cooling', 'heating', 'general'] as $type) {
            $classId = isset($labels[$type]['energy_class_id']) ? (int)$labels[$type]['energy_class_id'] : 0;

            $existingRow = $this->db->query("
                SELECT label_file, datasheet_file
                FROM `" . DB_PREFIX . "energy_label_product`
                WHERE product_id = " . (int)$product_id . "
                AND type = '" . $this->db->escape($type) . "'
                LIMIT 1
            ");
            $oldLabelFile     = $existingRow->num_rows ? $existingRow->row['label_file']     : '';
            $oldDatasheetFile = $existingRow->num_rows ? $existingRow->row['datasheet_file'] : '';

            if (!$classId) {
                $this->db->query("
                    DELETE FROM `" . DB_PREFIX . "energy_label_product`
                    WHERE product_id = " . (int)$product_id . "
                    AND type = '" . $this->db->escape($type) . "'
                ");

                $this->deleteStoredFile($oldLabelFile);
                $this->deleteStoredFile($oldDatasheetFile);

                if (!empty($attributeIds[$type]['attribute_id'])) {
                    $this->db->query("
                        DELETE FROM `" . DB_PREFIX . "product_attribute`
                        WHERE product_id = " . (int)$product_id . "
                        AND attribute_id = " . (int)$attributeIds[$type]['attribute_id'] . "
                    ");
                }

                continue;
            }

            $labelFile = $labels[$type]['label_file'] ?? '';
            $labelFileOriginal = $labels[$type]['label_file_original'] ?? '';
            $datasheetFile = $labels[$type]['datasheet_file'] ?? '';
            $datasheetOriginal = $labels[$type]['datasheet_file_original'] ?? '';

            // Handle label file upload
            if (!empty($uploads[$type]['label_file']['tmp_name'])) {
                $uploaded = $this->handleFileUpload(
                    $uploads[$type]['label_file'],
                    ['jpg', 'jpeg', 'png', 'webp', 'pdf']
                );
                if ($uploaded) {
                    $this->deleteStoredFile($oldLabelFile);
                    $labelFile = $uploaded['path'];
                    $labelFileOriginal = $uploaded['original'];
                }
            }

            // Handle datasheet upload
            if (!empty($uploads[$type]['datasheet_file']['tmp_name'])) {
                $uploaded = $this->handleFileUpload(
                    $uploads[$type]['datasheet_file'],
                    ['pdf']
                );
                if ($uploaded) {
                    $this->deleteStoredFile($oldDatasheetFile);
                    $datasheetFile = $uploaded['path'];
                    $datasheetOriginal = $uploaded['original'];
                }
            }

            $this->db->query("
            INSERT INTO `" . DB_PREFIX . "energy_label_product`
                (product_id, type, energy_class_id, label_file, label_file_original, datasheet_file, datasheet_file_original)
            VALUES (
                " . (int)$product_id . ",
                '" . $this->db->escape($type) . "',
                " . (int)$classId . ",
                '" . $this->db->escape($labelFile) . "',
                '" . $this->db->escape($labelFileOriginal) . "',
                '" . $this->db->escape($datasheetFile) . "',
                '" . $this->db->escape($datasheetOriginal) . "'
            )
            ON DUPLICATE KEY UPDATE
                energy_class_id         = " . (int)$classId . ",
                label_file              = '" . $this->db->escape($labelFile) . "',
                label_file_original     = '" . $this->db->escape($labelFileOriginal) . "',
                datasheet_file          = '" . $this->db->escape($datasheetFile) . "',
                datasheet_file_original = '" . $this->db->escape($datasheetOriginal) . "'
        ");

            if (!empty($attributeIds[$type]['attribute_id'])) {
                $attributeId = (int)$attributeIds[$type]['attribute_id'];

                $classResult = $this->db->query("
                SELECT name FROM `" . DB_PREFIX . "energy_label_class`
                WHERE energy_class_id = " . (int)$classId . "
                LIMIT 1
            ");

                if ($classResult->num_rows) {
                    $className = $classResult->row['name'];

                    $this->db->query("
                    DELETE FROM `" . DB_PREFIX . "product_attribute`
                    WHERE product_id = " . (int)$product_id . "
                    AND attribute_id = " . $attributeId . "
                ");

                    foreach ($this->getInstalledLanguageIds() as $langId) {
                        $this->db->query("
                        INSERT INTO `" . DB_PREFIX . "product_attribute`
                            (product_id, attribute_id, language_id, text)
                        VALUES (
                            " . (int)$product_id . ",
                            " . $attributeId . ",
                            " . (int)$langId . ",
                            '" . $this->db->escape($className) . "'
                        )
                    ");
                    }
                }
            }
        }
    }

    private function handleFileUpload($upload, $allowedTypes = ['pdf'])
    {
        if (empty($upload['tmp_name']) || (int)$upload['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $originalName = basename($upload['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedTypes)) {
            return null;
        }

        if ($upload['size'] > 10 * 1024 * 1024) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($upload['tmp_name']);

        $allowedMimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        if (!isset($allowedMimes[$ext]) || $allowedMimes[$ext] !== $mimeType) {
            return null;
        }

        if ($ext === 'pdf') {
            $handle = fopen($upload['tmp_name'], 'rb');
            $magic = fread($handle, 5);
            fclose($handle);
            if ($magic !== '%PDF-') {
                return null;
            }
        }

        $uploadDir = DIR_IMAGE . 'catalog/energy_labels/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $storedName = bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $storedName;

        if (!move_uploaded_file($upload['tmp_name'], $destPath)) {
            return null;
        }

        return [
            'path' => 'image/catalog/energy_labels/' . $storedName,
            'original' => $originalName,
        ];
    }

    public function removeProductFile($productId, $type, $field)
    {
        $allowedFields = ['label_file', 'datasheet_file'];
        if (!in_array($field, $allowedFields)) {
            return;
        }

        $existing = $this->db->query("
            SELECT `" . $field . "`
            FROM `" . DB_PREFIX . "energy_label_product`
            WHERE product_id = " . (int)$productId . "
            AND type = '" . $this->db->escape($type) . "'
            LIMIT 1
        ");

        $originalField = $field . '_original';

        $this->db->query("
            UPDATE `" . DB_PREFIX . "energy_label_product`
            SET `" . $field . "` = '',
                `" . $originalField . "` = ''
            WHERE product_id = " . (int)$productId . "
            AND type = '" . $this->db->escape($type) . "'
        ");

        if ($existing->num_rows) {
            $this->deleteStoredFile($existing->row[$field]);
        }
    }

    private function deleteStoredFile(string $path): void
    {
        if (!$path || strpos($path, 'catalog/energy_labels/') === false) {
            return;
        }
        $fullPath = DIR_IMAGE . substr($path, strlen('image/'));
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    public function getAllProductsWithLabels(): array
    {
        $languageId = (int)$this->config->get('config_language_id');

        $query = $this->db->query("
            SELECT
                p.product_id,
                pd.name AS product_name,
                p.model,
 
                ec_cool.name AS cooling_class,
                elp_cool.label_file_original AS cooling_label_file,
                elp_cool.datasheet_file_original AS cooling_datasheet_file,
 
                ec_heat.name AS heating_class,
                elp_heat.label_file_original AS heating_label_file,
                elp_heat.datasheet_file_original AS heating_datasheet_file,
 
                ec_gen.name AS general_class,
                elp_gen.label_file_original AS general_label_file,
                elp_gen.datasheet_file_original AS general_datasheet_file
 
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON pd.product_id = p.product_id
                AND pd.language_id = '" . $languageId . "'
 
            LEFT JOIN `" . DB_PREFIX . "energy_label_product` elp_cool
                ON elp_cool.product_id = p.product_id AND elp_cool.type = 'cooling'
            LEFT JOIN `" . DB_PREFIX . "energy_label_class` ec_cool
                ON ec_cool.energy_class_id = elp_cool.energy_class_id
 
            LEFT JOIN `" . DB_PREFIX . "energy_label_product` elp_heat
                ON elp_heat.product_id = p.product_id AND elp_heat.type = 'heating'
            LEFT JOIN `" . DB_PREFIX . "energy_label_class` ec_heat
                ON ec_heat.energy_class_id = elp_heat.energy_class_id
 
            LEFT JOIN `" . DB_PREFIX . "energy_label_product` elp_gen
                ON elp_gen.product_id = p.product_id AND elp_gen.type = 'general'
            LEFT JOIN `" . DB_PREFIX . "energy_label_class` ec_gen
                ON ec_gen.energy_class_id = elp_gen.energy_class_id
 
            ORDER BY pd.name ASC
        ");

        return $query->rows;
    }

    /**
     * Bulk-update energy label class assignments and/or files from import rows.
     *
     * @param array $rows Associative rows from Excel/CSV.
     * @param array $extractedFiles Map of "type/filename" => "/tmp/path"
     *                                 from the uploaded ZIP.
     * @return array  ['updated' => int, 'skipped' => int]
     */
    public function bulkUpdateLabels(array $rows, array $extractedFiles = []): array
    {
        // Build class name → id lookup (case-insensitive)
        $classMap = [];
        $result = $this->db->query("SELECT energy_class_id, name FROM `" . DB_PREFIX . "energy_label_class`");
        foreach ($result->rows as $r) {
            $classMap[strtolower(trim($r['name']))] = (int)$r['energy_class_id'];
        }

        $uploadDir = DIR_IMAGE . 'catalog/energy_labels/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $typeMap = [
            'cooling' => [
                'class' => 'cooling_class',
                'label' => 'cooling_label_file',
                'datasheet' => 'cooling_datasheet_file',
            ],
            'heating' => [
                'class' => 'heating_class',
                'label' => 'heating_label_file',
                'datasheet' => 'heating_datasheet_file',
            ],
            'general' => [
                'class' => 'general_class',
                'label' => 'general_label_file',
                'datasheet' => 'general_datasheet_file',
            ],
        ];

        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $productId = (int)($row['product_id'] ?? 0);
            if ($productId < 1) {
                $skipped++;
                continue;
            }

            // Verify product exists
            $exists = $this->db->query("
                SELECT product_id FROM `" . DB_PREFIX . "product`
                WHERE product_id = '" . $productId . "' LIMIT 1
            ");
            if (!$exists->num_rows) {
                $skipped++;
                continue;
            }

            $rowUpdated = false;

            foreach ($typeMap as $type => $cols) {
                // ---- Resolve class
                $classValue = trim((string)($row[$cols['class']] ?? ''));
                $newClassId = null;

                if ($classValue !== '') {
                    $normalised = strtolower($classValue);
                    if (isset($classMap[$normalised])) {
                        $newClassId = $classMap[$normalised];
                    }
                    // Unknown class name → skip class update for this cell
                }

                // ---- Resolve label_file
                $labelResult = $this->resolveFileFromZip(
                    trim((string)($row[$cols['label']] ?? '')),
                    $type,
                    $extractedFiles,
                    $uploadDir
                );

                // ---- Resolve datasheet_file
                $datasheetResult = $this->resolveFileFromZip(
                    trim((string)($row[$cols['datasheet']] ?? '')),
                    $type,
                    $extractedFiles,
                    $uploadDir
                );

                // Nothing to do for this type → skip
                if ($newClassId === null && $labelResult === null && $datasheetResult === null) {
                    continue;
                }

                // ---- Check existing row
                $existing = $this->db->query("
                    SELECT energy_label_id, energy_class_id,
                           label_file, label_file_original,
                           datasheet_file, datasheet_file_original
                    FROM `" . DB_PREFIX . "energy_label_product`
                    WHERE product_id = '" . $productId . "'
                    AND   type       = '" . $this->db->escape($type) . "'
                    LIMIT 1
                ");

                if ($existing->num_rows) {
                    // Build UPDATE — only touch columns that have new values
                    $setParts = [];

                    if ($newClassId !== null) {
                        $setParts[] = "energy_class_id = '" . $newClassId . "'";
                    }
                    if ($labelResult !== null) {
                        $setParts[] = "label_file = '" . $this->db->escape($labelResult['path']) . "'";
                        $setParts[] = "label_file_original = '" . $this->db->escape($labelResult['original']) . "'";
                    }
                    if ($datasheetResult !== null) {
                        $setParts[] = "datasheet_file = '" . $this->db->escape($datasheetResult['path']) . "'";
                        $setParts[] = "datasheet_file_original = '" . $this->db->escape($datasheetResult['original']) . "'";
                    }

                    if ($setParts) {
                        $this->db->query("
                            UPDATE `" . DB_PREFIX . "energy_label_product`
                            SET " . implode(', ', $setParts) . "
                            WHERE product_id = '" . $productId . "'
                            AND   type       = '" . $this->db->escape($type) . "'
                        ");
                        $rowUpdated = true;
                    }

                } else {
                    // INSERT — only if we at least have a class
                    if ($newClassId === null) {
                        continue;
                    }

                    $lf = $labelResult ? $labelResult['path'] : '';
                    $lfo = $labelResult ? $labelResult['original'] : '';
                    $df = $datasheetResult ? $datasheetResult['path'] : '';
                    $dfo = $datasheetResult ? $datasheetResult['original'] : '';

                    $this->db->query("
                        INSERT INTO `" . DB_PREFIX . "energy_label_product`
                        (product_id, type, energy_class_id,
                         label_file, label_file_original,
                         datasheet_file, datasheet_file_original)
                        VALUES (
                            '" . $productId . "',
                            '" . $this->db->escape($type) . "',
                            '" . $newClassId . "',
                            '" . $this->db->escape($lf) . "',
                            '" . $this->db->escape($lfo) . "',
                            '" . $this->db->escape($df) . "',
                            '" . $this->db->escape($dfo) . "'
                        )
                    ");
                    $rowUpdated = true;
                }
            }

            if ($rowUpdated) {
                $updated++;
            } else {
                $skipped++;
            }
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    private function resolveFileFromZip(string $filename, string $type, array $extractedFiles, string $uploadDir): ?array
    {
        if ($filename === '') {
            return null;
        }

        // Sanitize filename the same way the controller did
        $basename = preg_replace('/[^a-zA-Z0-9._\-]/', '_', basename($filename));
        $key = strtolower($type) . '/' . $basename;

        if (!isset($extractedFiles[$key])) {
            return null; // File mentioned in Excel but not found in ZIP → skip
        }

        $tmpPath = $extractedFiles[$key];
        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $stored = bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $stored;

        if (!copy($tmpPath, $destPath)) {
            return null;
        }

        return [
            'path' => 'image/catalog/energy_labels/' . $stored,
            'original' => $basename,
        ];
    }
}