<?php
namespace Opencart\Application\Model\Localisation;
class Language extends \Opencart\System\Engine\Model {
	public function getLanguage($language_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . (int)$language_id . "'");

		return $query->row;
	}

	public function getLanguages() {
		$language_data = $this->cache->get('catalog.language');

		if (!$language_data) {
			$language_data = [];

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `status` = '1' ORDER BY `sort_order`, `name`");

			foreach ($query->rows as $result) {
				$language_data[] = [
					'language_id' => $result['language_id'],
					'name'        => $result['name'],
					'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
				];
			}

			$this->cache->set('catalog.language', $language_data);
		}

		return $language_data;
	}
}
