<?php

class ModelExtensionModuleGoogleReviews extends Model
{
    public function getReviews()
    {
        $limit = (int)$this->config->get('module_google_reviews_limit') ?: 5;
        $min_rating = (int)$this->config->get('module_google_reviews_min_rating') ?: 1;

        $query = $this->db->query("
            SELECT *
            FROM `" . DB_PREFIX . "google_reviews`
            WHERE status = 1
            AND rating >= '" . $min_rating . "'
            ORDER BY review_date DESC
            LIMIT " . $limit . "
        ");

        return $query->rows;
    }
}