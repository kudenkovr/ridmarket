<?php

class ModelUpdateManufacturer extends Model {
	
	public function getManufacturers() {
		$sql = 'SELECT manufacturer_id, LCASE(name) AS name FROM ' . DB_PREFIX . 'manufacturer ORDER by name';
		$manufacturers = array_column($this->db->query($sql)->rows, 'manufacturer_id', 'name');
		$this->registry->set('manufacturers', new Stack());
		$this->manufacturers->setArray($manufacturers);
		return $manufacturers;
	}
	
	
	public function getManufacturer($name) {
		$lname = mb_strtolower($name);
		if (!$this->manufacturers->has($lname)) {
			return $this->addManufacturer($name);
		} else {
			return $this->manufacturers->get($lname);
		}
	}
	
	
	public function addManufacturer($name) {
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'manufacturer
							SET name = "' . $this->db->escape($name) . '",
							sort_order = "' . 5 . '"');
		
		$manufacturer_id = $this->db->getLastId();
		
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'manufacturer_to_store
							SET manufacturer_id = "' . (int)$manufacturer_id . '",
							store_id = "' . (int)$this->config->get('store_id') . '"');
		
		$this->manufacturers->set(mb_strtolower($name), $manufacturer_id);
		return $manufacturer_id;
	}
	
}