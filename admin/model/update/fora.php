<?php

class ModelUpdateFora extends Model {
	public function clear() {
		$this->db->query('TRUNCATE TABLE oc_category');
		$this->db->query('TRUNCATE TABLE oc_category_description');
		$this->db->query('TRUNCATE TABLE oc_category_path');
		$this->db->query('TRUNCATE TABLE oc_category_to_store');
		$this->db->query('TRUNCATE TABLE oc_category_to_layout');
		$this->db->query('TRUNCATE TABLE oc_manufacturer');
		$this->db->query('TRUNCATE TABLE oc_manufacturer_to_store');
		$this->db->query('TRUNCATE TABLE oc_product');
		$this->db->query('TRUNCATE TABLE oc_product_description');
		$this->db->query('TRUNCATE TABLE oc_product_to_category');
		$this->db->query('TRUNCATE TABLE oc_product_to_store');
		$this->db->query('TRUNCATE TABLE oc_product_to_layout');
	}
/* 	{
		"Name":"10-11кл География Атлас РГО (Дрофа)",
		"Country":"Россия",
		"Ediniza":"Штука",
		"Articul":"978-5-358-22128-4",
		"SCod":"9785358221284",
		"Color":"0",
		"Usluga":"0",
		"Remark":"",
		"Rest":"4",
		"Garant":"Нет",
		"Price":"150.00",
		"TVid":"Канцтовары",
		"Made":"Производитель",
		"Category":"Канцтовары",
		"ImageName":""
	} */
	public function import($file) {
		if (!file_exists($file)) return array('error'=>'File ' . $file . ' not found');
		$json = file_get_contents($file);
		$json = str_replace('', '', $json);
		$products = json_decode($json, true);
		$products = $products['TOVAR'];


		$this->load->model('update/category');
		$this->load->model('update/manufacturer');
		$this->load->model('update/product');


		$this->model_update_category->getCategories();
		$this->model_update_manufacturer->getManufacturers();
		$this->model_update_product->getInsertedUpc();

		$output = array(
			'update' => 0,
			'add' => 0
		);

		// Disable all products
		$this->db->query('UPDATE ' . DB_PREFIX . 'product p
							LEFT JOIN ' . DB_PREFIX . 'product_to_store p2s ON (p.product_id = p2s.product_id)
							SET quantity="0", status="0"
							WHERE p2s.store_id="' . $this->config->get('store_id') . '"');

		// Add or Update products
		foreach ($products as $product) {
			$data = array(
						'name' => $product['Name'],
						'description' => $product['Remark'],
						'model' => $product['Name'],
						'sku' => (string)$product['Articul'],
						'upc' => (string)$product['SCod'],
						'quantity' => $product['Rest'],
						'status' => ((int)$product['Rest'] > 0) ? 1 : 0,
						'manufacturer_id' => $this->model_update_manufacturer->getManufacturer($product['Made']),
						'price' => $product['Price'],
						'image' => (!empty($product['ImageName'])) ? $this->config->get('dir_fora_image') . $product['ImageName'] : '',
						'store_id' => $this->config->get('store_id'),
						'categories' => $this->model_update_category->getIDsByNamedPath($product['TVid'] . $this->config->get('category_separator') . $product['Category'])
					);
			if ($this->inserted_upc->has($data['upc'])) {
				$data['product_id'] = (int)$this->inserted_upc->get($data['upc']);
				$this->model_update_product->update($data);
				$output['update']++;
			} else {
				$this->model_update_product->add($data);
				$output['add']++;
			}
		}
		
		$output['success'] = true;
		return $output;
	}

}