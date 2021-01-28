<?php
class ModelUpdateB2B extends Model {
	
	public function auth() {
		$ch = curl_init("https://b2b.i-t-p.pro/api/2");

		$dataAuth = array("request" => array(
							"method" => "login",
							"model" => "auth" ,
							"module" => "quickfox"
							),
					  "data" => array(
							"login" => $this->config->get('b2b_login'),
							"password" => $this->config->get('b2b_password')
							)
						);
		$dataAuthString = json_encode($dataAuth);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataAuthString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Length: ' . strlen($dataAuthString)
		));
		$result = curl_exec($ch);
		curl_close ($ch);

		$resAuth = json_decode($result, true);
		if (!empty($resAuth) && isset($resAuth['success']) && $resAuth['success']) {
			$this->config->set('b2b_session', $resAuth['session']);
		}
	}
	
	
	public function importCategories() {
		$session = $this->config->get('b2b_session');
		
		$ch = curl_init("https://b2b.i-t-p.pro/download/catalog/json/catalog_tree.json");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Cookie: session=' . $session )
		);
		$result = curl_exec($ch);
		curl_close ($ch);
		$resCatalogTree = json_decode($result, true);
		// print_r($resCatalogTree);exit;
		
		$this->load->model('update/category');
		$this->model_update_category->getInsertedCategoriesID();
		
		$this->_updateCategories($resCatalogTree);
	}
	
	
	protected function _updateCategories($data = array(), $parent=0) {
		foreach ($data as $category) {
			$id = $category['id'] + $this->config->get('b2b_category_id_start');
			// mb_internal_encoding('utf-8');
			$name = mb_strtoupper(mb_substr($category['name'], 0, 1)) . mb_strtolower(mb_substr($category['name'], 1));
			if (!$this->insertedCategories->hasValue($id)) {
				$this->model_update_category->add(array(
					'category_id' => $id,
					'parent_id' => $parent,
					'name' => $name,
					'status' => 0,
					'store_id' => $this->config->get('store_id')
				));
			}
			if (!empty($category['childrens'])) {
				$this->_updateCategories($category['childrens'], $id);
			}
		}
	}
	
}