<?php
namespace Opencart\Application\Model\Localisation;
class Currency extends \Opencart\System\Engine\Model {
	public function addCurrency($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "currency` SET `code` = '" . $this->db->escape((string)$data['code']) . "', `decimal_place` = '" . $this->db->escape((string)$data['decimal_place']) . "', `value` = '" . (float)$data['value'] . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW()");

		$currency_id = $this->db->getLastId();
		
		if (isset($data['currency_description'])) {
			foreach ($data['currency_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "currency_description` SET `currency_id` = '" . (int)$currency_id . "', `symbol_left` = '" . $this->db->escape((string)$value['symbol_left']) . "', `symbol_right` = '" . $this->db->escape((string)$value['symbol_right']) . "', `title` = '" . $this->db->escape((string)$value['title']) . "', `language_id` = '" . (int)$language_id . "'");
				
				if (isset($value['country'])) {
					foreach ($value['country'] as $country) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "currency_to_country` SET `currency_id` = '" . (int)$currency_id . "', `country_id` = '" . (int)$country['country_id'] . "'");
					}
				}
				
				if (!empty($value['push'])) {
					foreach ($value['push'] as $country) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "currency_to_country_push` SET `currency_id` = '" . (int)$currency_id . "', `country_id` = '" . (int)$country['country_id'] . "'");
					}
				}
			}
		}

		$this->cache->delete('currency.' . (int)$this->config->get('config_language_id'));
		
		return $currency_id;
	}

	public function editCurrency($currency_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `code` = '" . $this->db->escape((string)$data['code']) . "', `decimal_place` = '" . $this->db->escape((string)$data['decimal_place']) . "', `value` = '" . (float)$data['value'] . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `currency_id` = '" . (int)$currency_id . "'");		
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency_description` WHERE `currency_id` = '" . (int)$currency_id . "'");		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency_to_country`  WHERE `currency_id` = '" . (int)$currency_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency_to_country_push`  WHERE `currency_id` = '" . (int)$currency_id . "'");
		
		if (isset($data['currency_description'])) {
			foreach ($data['currency_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "currency_description` SET `currency_id` = '" . (int)$currency_id . "', `symbol_left` = '" . $this->db->escape((string)$value['symbol_left']) . "', `symbol_right` = '" . $this->db->escape((string)$value['symbol_right']) . "', `title` = '" . $this->db->escape((string)$value['title']) . "', `language_id` = '" . (int)$language_id . "'");
				
				if (isset($value['country'])) {
					foreach ($value['country'] as $country) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "currency_to_country` SET `currency_id` = '" . (int)$currency_id . "', `country_id` = '" . (int)$country['country_id'] . "'");
					}
				}
				
				if (!empty($value['push'])) {
					foreach ($value['push'] as $country) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "currency_to_country_push` SET `currency_id` = '" . (int)$currency_id . "', `country_id` = '" . (int)$country['country_id'] . "'");
					}
				}
			}
		}

		$this->cache->delete('currency.' . (int)$this->config->get('config_language_id'));
	}

	public function editValueByCode($code, $value) {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");

		$this->cache->delete('currency.' . (int)$this->config->get('config_language_id'));
	}

	public function deleteCurrency($currency_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency` WHERE `currency_id` = '" . (int)$currency_id . "'");		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency_description` WHERE `currency_id` = '" . (int)$currency_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency_to_country` WHERE `currency_id` = '" . (int)$currency_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency_to_country_push` WHERE `currency_id` = '" . (int)$currency_id . "'");
		
		$this->cache->delete('currency.' . (int)$this->config->get('config_language_id'));
	}

	public function getCurrency($currency_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `currency_id` = '" . (int)$currency_id . "'");

		return $query->row;
	}
	
	public function getCurrencyByCode($currency) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape($currency) . "'");

		return $query->row;
	}

	public function getCurrencies($data = []) {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "currency` c INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (c.`currency_id` = cd.`currency_id`) INNER JOIN `" . DB_PREFIX . "currency_to_country` c2c ON (c.`currency_id` = c2c.`currency_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2c.`country_id` = '" . (int)$this->config->get('config_country_id') . "'";

			$sort_data = [
				'title',
				'code',
				'value',
				'date_modified'
			];
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY cd.`title`";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$currency_data = $this->cache->get('currency.' . (int)$this->config->get('config_language_id'));

			if (!$currency_data) {
				$currency_data = [];

				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` c INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (c.`currency_id` = cd.`currency_id`) INNER JOIN `" . DB_PREFIX . "currency_to_country` c2c ON (c.`currency_id` = c2c.`currency_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2c.`country_id` = '" . (int)$this->config->get('config_country_id') . "' ORDER cd.`title` ASC");

				foreach ($query->rows as $result) {
					$currency_data[$result['code']] = [
						'currency_id'   => $result['currency_id'],
						'country_id'	=> $result['country_id'],						
						'title'         => $result['title'],
						'code'          => $result['code'],
						'symbol_left'   => $result['symbol_left'],
						'symbol_right'  => $result['symbol_right'],
						'decimal_place' => $result['decimal_place'],
						'value'         => $result['value'],
						'status'        => $result['status'],
						'date_modified' => $result['date_modified']
					];
				}

				$this->cache->set('currency.' . (int)$this->config->get('config_language_id'), $currency_data);
			}

			return $currency_data;
		}
	}
	
	public function getDescriptions($currency_id) {
		$currency_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency_description` WHERE `currency_id` = '" . (int)$currency_id . "'");

		foreach ($query->rows as $result) {
			$currency_description_data[$result['language_id']] = [
				'title'             => $result['title'],
				'symbol_left'	    => $result['symbol_left'],
				'symbol_right'	    => $result['symbol_right']
			];
		}

		return $currency_description_data;
	}
	
	public function getCountriesByCurrencyId($currency_id) {
		$country_currency_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency_to_country` c2c INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (c2c.`currency_id` = cd.`currency_id`) WHERE c2c.`currency_id` = '" . (int)$currency_id . "'");

		foreach ($query->rows as $result) {
			$country_currency_data[$result['language_id']] = [
				'country_id'             => $result['country_id']
			];
		}

		return $country_currency_data;
	}
	
	public function getCountriesPushByCurrencyId($currency_id) {
		$country_push_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency_to_country_push` c2cp INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (c2cp.`currency_id` = cd.`currency_id`) WHERE c2cp.`currency_id` = '" . (int)$currency_id . "'");

		foreach ($query->rows as $result) {
			$country_push_data[$result['language_id']] = [
				'country_id'             => $result['country_id']
			];
		}

		return $country_push_data;
	}

	public function getTotalCurrencies() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "currency` c INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (c.`currency_id` = cd.`currency_id`) INNER JOIN `" . DB_PREFIX . "currency_to_country` c2c ON (c.`currency_id` = c2c.`currency_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2c.`country_id` = '" . (int)$this->config->get('config_country_id') . "'");

		return $query->row['total'];
	}
	
	public function getTotalCountriesByCurrencyId($currency_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "currency_to_country` c2c INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (cd.`currency_id` = c2c.`currency_id`) WHERE c2c.`currency_id` = '" . (int)$currency_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
	
	public function getTotalCountriesPushByCurrencyId($currency_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "currency_to_country_push` c2cp INNER JOIN `" . DB_PREFIX . "currency_description` cd ON (cd.`currency_id` = c2cp.`currency_id`) WHERE c2cp.`currency_id` = '" . (int)$currency_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}
