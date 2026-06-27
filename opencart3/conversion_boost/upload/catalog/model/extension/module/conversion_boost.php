<?php

class ModelExtensionModuleConversionBoost extends Model
{
    public function getSpecialPrice($product_id) {
        $query = $this->db->query("
            SELECT date_end 
            FROM `" . DB_PREFIX . "product_special` 
            WHERE product_id = '" . (int)$product_id . "' 
            AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
            AND date_end >= NOW()
            AND date_end != '0000-00-00'
            AND (date_start = '0000-00-00' OR date_start <= NOW())
            ORDER BY priority ASC, price ASC 
            LIMIT 1
        ");

        return $query->row;
    }
}
