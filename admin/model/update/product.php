<?php

class ModelUpdateProduct extends Model {
	public function getInsertedSku() {
		$sql = 'SELECT p.sku, p.product_id AS id FROM ' . DB_PREFIX . 'product p
					LEFT JOIN ' . DB_PREFIX . 'product_to_store p2c ON (p.product_id = p2c.product_id)
					WHERE p2c.store_id = ' . (int)$this->config->get('store_id');
		$this->registry->set('inserted_sku', new Stack());
		$this->inserted_sku->setArray(array_column($this->db->query($sql)->rows, 'id', 'sku'));
	}
	
	
	public function getInsertedUpc() {
		$sql = 'SELECT p.upc AS upc, p.product_id AS id FROM '.DB_PREFIX.'product p
					LEFT JOIN ' . DB_PREFIX . 'product_to_store p2c ON (p.product_id = p2c.product_id)
					WHERE p2c.store_id = "' . (int)$this->config->get('store_id') . '"';
		$this->registry->set('inserted_upc', new Stack());
		$this->inserted_upc->setArray(array_column($this->db->query($sql)->rows, 'id', 'upc'));
	}
	
	
	public function add($data = array()) {
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'product
			SET model = "' . $this->db->escape($data['model']) . '",
				sku = "' . $this->db->escape($data['sku']) . '",
				upc = "' . (isset($data['upc']) ? $this->db->escape($data['upc']) : '') . '",
				ean = "",
				jan = "",
				isbn = "",
				mpn = "",
				location = "",
				price = "' . (float)$data['price'] . '",
				quantity = "' . (int)$data['quantity'] . '",
				stock_status_id = "7",
				date_available = NOW(),
				manufacturer_id = "' . (int)$data['manufacturer_id'] . '",
				shipping = "0",
				weight_class_id = "1",
				length_class_id = "1",
				status = "' . (int)$data['status'] . '",
				image = "' . $this->db->escape($data['image']) . '",
				tax_class_id = "0",
				sort_order = "10",
				date_added = NOW(),
				date_modified = NOW()');
		
		$product_id = $this->db->getLastId();
		
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'product_description
							SET product_id = "' . (int)$product_id . '",
								language_id = "1",
								name = "' . $this->db->escape($data['name']) . '",
								description = "' . $this->db->escape($data['description']) . '",
								tag = "",
								meta_title = "' . $this->db->escape($data['name']) . '",
								meta_description = "' . $this->db->escape($data['description']) . '",
								meta_keyword = ""');
		
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'product_to_store
							SET product_id = "' . (int)$product_id . '",
								store_id = "' . (int)$data['store_id'] . '"');
		
		foreach (explode(',', $data['categories']) as $category_id) {
			$this->db->query('INSERT INTO ' . DB_PREFIX . 'product_to_category
								SET product_id = "' . (int)$product_id . '",
									category_id = "' . (int)$category_id . '"');
		}
		
		if ($this->registry->has('inserted_sku')) $this->inserted_sku->set($data['sku'], $product_id);
		if ($this->registry->has('inserted_upc')) $this->inserted_upc->set($data['upc'], $product_id);
		return $product_id;
	}
	
	
	public function update($data = array()) {
		$this->db->query('UPDATE ' . DB_PREFIX . 'product
							SET price = "' . (float)$data['price'] . '",
								quantity = "' . (int)$data['quantity'] . '",
								status = "' . (int)$data['status'] . '",
								image = "' . $this->db->escape($data['image']) . '"
							WHERE product_id = "' . (int)$data['product_id'] . '"');
	}
}