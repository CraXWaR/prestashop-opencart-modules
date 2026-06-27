<?php

class ModelExtensionModuleGoogleReviews extends Model
{
    public function install()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "google_reviews` (
            `review_id` VARCHAR(64) NOT NULL,
            `author_name` VARCHAR(255) NOT NULL,
            `author_avatar` TEXT NULL,
            `rating` TINYINT(1) NOT NULL DEFAULT 5,
            `review_text` TEXT NULL,
            `review_date` DATETIME NULL,
            `review_url` TEXT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`review_id`),
            KEY `status` (`status`),
            KEY `review_date` (`review_date`),
            KEY `rating` (`rating`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function uninstall()
    {
        $this->db->query("
            DROP TABLE IF EXISTS `" . DB_PREFIX . "google_reviews`
        ");
    }

    public function syncReviews()
    {
        try {
            $place_id = $this->config->get('module_google_reviews_place_id');
            $api_key = $this->config->get('module_google_reviews_api_key');

            if (!$place_id || !$api_key) {
                return array(
                    'success' => false,
                    'message' => 'error_missing_key'
                );
            }

            $url = 'https://maps.googleapis.com/maps/api/place/details/json?' .
                http_build_query(array(
                    'place_id' => $place_id,
                    'fields' => 'reviews',
                    'key' => $api_key
                ));

            $response = $this->makeRequest($url);

            if (!$response) {
                $this->logError('Empty API response');

                return array(
                    'success' => false,
                    'message' => 'error_empty_response'
                );
            }

            $json = json_decode($response, true);

            if (!isset($json['result']['reviews']) || !is_array($json['result']['reviews'])) {

                $this->logError('Google Reviews API invalid response: ' . print_r($json, true));

                return array(
                    'success' => false,
                    'message' => 'error_sync_failed'
                );
            }

            foreach ($json['result']['reviews'] as $review) {
                $review_id = md5(
                    (isset($review['author_name']) ? $review['author_name'] : '') .
                    (isset($review['time']) ? $review['time'] : '') .
                    (isset($review['text']) ? $review['text'] : '')
                );

                $author_name = isset($review['author_name']) ? $this->db->escape($review['author_name']) : '';
                $author_avatar = isset($review['profile_photo_url']) ? $this->db->escape($review['profile_photo_url']) : '';
                $rating = isset($review['rating']) ? (int)$review['rating'] : 5;
                $review_text = isset($review['text']) ? $this->db->escape($review['text']) : '';
                $review_date = isset($review['time']) ? date('Y-m-d H:i:s', (int)$review['time']) : null;
                $review_url = isset($review['author_url']) ? $this->db->escape($review['author_url']) : '';

                $this->db->query("
                INSERT INTO `" . DB_PREFIX . "google_reviews`
                SET
                    review_id     = '" . $this->db->escape($review_id) . "',
                    author_name   = '" . $author_name . "',
                    author_avatar = '" . $author_avatar . "',
                    rating        = '" . (int)$rating . "',
                    review_text   = '" . $review_text . "',
                    review_date   = " . ($review_date ? "'" . $review_date . "'" : "NULL") . ",
                    review_url    = '" . $review_url . "',
                    status        = 1,
                    date_added    = NOW(),
                    date_modified = NOW()
                ON DUPLICATE KEY UPDATE
                    author_name   = '" . $author_name . "',
                    author_avatar = '" . $author_avatar . "',
                    rating        = '" . (int)$rating . "',
                    review_text   = '" . $review_text . "',
                    review_date   = " . ($review_date ? "'" . $review_date . "'" : "NULL") . ",
                    review_url    = '" . $review_url . "',
                    date_modified = NOW()
            ");
            }

            $this->db->query("
            DELETE FROM `" . DB_PREFIX . "setting`
            WHERE code = 'module_google_reviews_sync'
            AND `key` = 'module_google_reviews_last_sync'
        ");

            $this->db->query("
            INSERT INTO `" . DB_PREFIX . "setting`
            SET store_id = 0,
                code = 'module_google_reviews_sync',
                `key` = 'module_google_reviews_last_sync',
                value = NOW(),
                serialized = 0
        ");

            $this->db->query("
            DELETE FROM `" . DB_PREFIX . "setting`
            WHERE code = 'module_google_reviews_sync'
            AND `key` = 'module_google_reviews_last_error'
        ");

            $this->db->query("
            INSERT INTO `" . DB_PREFIX . "setting`
            SET store_id = 0,
                code = 'module_google_reviews_sync',
                `key` = 'module_google_reviews_last_error',
                value = '',
                serialized = 0
        ");

            $last_sync_query = $this->db->query("
            SELECT value FROM `" . DB_PREFIX . "setting`
            WHERE code = 'module_google_reviews_sync'
            AND `key` = 'module_google_reviews_last_sync'
            LIMIT 1
        ");

            return array(
                'success' => true,
                'message' => 'text_success_sync',
                'last_sync' => $last_sync_query->num_rows ? date('d.m.Y H:i:s', strtotime($last_sync_query->row['value'])) : ''
            );

        } catch (Exception $e) {
            $this->logError($e->getMessage());

            $this->db->query("
            DELETE FROM `" . DB_PREFIX . "setting`
            WHERE code = 'module_google_reviews_sync'
            AND `key` = 'module_google_reviews_last_error'
        ");

            $this->db->query("
            INSERT INTO `" . DB_PREFIX . "setting`
            SET store_id = 0,
                code = 'module_google_reviews_sync',
                `key` = 'module_google_reviews_last_error',
                value = '" . $this->db->escape($e->getMessage()) . "',
                serialized = 0
        ");

            $this->logError('Google Reviews Exception: ' . $e->getMessage());

            return array(
                'success' => false,
                'message' => 'error_sync_failed'
            );
        }
    }

    private function makeRequest($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    private function logError($message)
    {
        $log = new Log('google_reviews_error.log');
        $log->write($message);
    }
}