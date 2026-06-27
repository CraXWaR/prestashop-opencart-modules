<?php

class ModelExtensionModuleCategoryFaq extends Model
{
    public function install()
    {
        $this->createTables();
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "category_faq`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "category_faq_description`");
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "category_faq` (
            `category_faq_id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) NOT NULL,
            `sort_order` int(3) NOT NULL,
            PRIMARY KEY (`category_faq_id`),
            KEY `category_id` (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "category_faq_description` (
            `category_faq_id` int(11) NOT NULL,
            `language_id` int(11) NOT NULL,
            `question` text NOT NULL,
            `answer` text NOT NULL,
            PRIMARY KEY (`category_faq_id`,`language_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }

    public function saveFaqs($category_id, $faqs)
    {
        $this->deleteFaqsByCategoryId($category_id);

        foreach ($faqs as $faq) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_faq` SET `category_id` = '" . (int) $category_id . "', `sort_order` = '" . (int) $faq['sort_order'] . "'");
            $category_faq_id = $this->db->getLastId();

            foreach ($faq['description'] as $language_id => $value) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_faq_description` SET `category_faq_id` = '" . (int) $category_faq_id . "', `language_id` = '" . (int) $language_id . "', `question` = '" . $this->db->escape($value['question']) . "', `answer` = '" . $this->db->escape($value['answer']) . "'");
            }
        }
    }

    public function deleteFaqsByCategoryId($category_id)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "category_faq_description` WHERE `category_faq_id` IN (SELECT `category_faq_id` FROM `" . DB_PREFIX . "category_faq` WHERE `category_id` = '" . (int) $category_id . "')");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "category_faq` WHERE `category_id` = '" . (int) $category_id . "'");
    }

    public function getFaqs($category_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_faq` LEFT JOIN `" . DB_PREFIX . "category_faq_description` USING(`category_faq_id`) WHERE `category_id` = '" . (int) $category_id . "' ORDER BY `sort_order` ASC");

        $faqs = [];
        foreach ($query->rows as $row) {
            if (!isset($faqs[$row['category_faq_id']])) {
                $faqs[$row['category_faq_id']] = [
                    'category_faq_id' => $row['category_faq_id'],
                    'sort_order' => $row['sort_order'],
                    'description' => []
                ];
            }
            $faqs[$row['category_faq_id']]['description'][$row['language_id']] = [
                'question' => $row['question'],
                'answer' => $row['answer']
            ];
        }

        return array_values($faqs);
    }

}
