<?php

class ModelExtensionModuleCategoryFaq extends Model
{
    public function getCategoryFaqs($category_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_faq LEFT JOIN " .
            DB_PREFIX . "category_faq_description ON " . DB_PREFIX . "category_faq.category_faq_id = " .
            DB_PREFIX . "category_faq_description.category_faq_id WHERE " . DB_PREFIX . "category_faq.category_id = '" .
            (int) $category_id . "' AND " . DB_PREFIX . "category_faq_description.language_id = '" .
            (int) $this->config->get('config_language_id') . "' ORDER BY " . DB_PREFIX . "category_faq.sort_order ASC");

        return $query->rows;
    }
}