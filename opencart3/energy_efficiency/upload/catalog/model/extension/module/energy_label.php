<?php

class ModelExtensionModuleEnergyLabel extends Model
{
    public function getProductLabels($product_id)
    {
        $result = [];

        $query = $this->db->query("
            SELECT elp.*, elc.name AS class_name, elc.icon AS class_icon
            FROM `" . DB_PREFIX . "energy_label_product` elp
            LEFT JOIN `" . DB_PREFIX . "energy_label_class` elc
                ON elp.energy_class_id = elc.energy_class_id
            WHERE elp.product_id = " . (int)$product_id . "
            AND elp.energy_class_id IS NOT NULL
            AND elp.energy_class_id > 0
        ");

        foreach ($query->rows as $row) {
            $result[$row['type']] = $row;
        }

        return $result;
    }

    public function getAllClassesIndexed()
    {
        $result = [];

        $query = $this->db->query("
            SELECT *
            FROM `" . DB_PREFIX . "energy_label_class`
            ORDER BY `sort_order` ASC, `name` ASC
        ");

        foreach ($query->rows as $row) {
            $result[$row['energy_class_id']] = $row;
        }

        return $result;
    }

    public function getProductsLabels(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        $ids = array_map('intval', $productIds);

        $result = [];

        $query = $this->db->query("
        SELECT elp.*, elc.name AS class_name
        FROM `" . DB_PREFIX . "energy_label_product` elp
        LEFT JOIN `" . DB_PREFIX . "energy_label_class` elc
            ON elp.energy_class_id = elc.energy_class_id
        WHERE elp.product_id IN (" . implode(',', $ids) . ")
        AND elp.energy_class_id IS NOT NULL
        AND elp.energy_class_id > 0
    ");

        foreach ($query->rows as $row) {
            $result[$row['product_id']][$row['type']] = $row;
        }

        return $result;
    }
}