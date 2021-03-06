<?php

class ModelUpdateCategory extends Model {
	public function getCategories() {
		$this->registry->set('categories', new Stack());
		$query = $this->db->query('SELECT cp.category_id, cp.level, cd1.name,
										GROUP_CONCAT(cp.path_id SEPARATOR ",") AS path,
										GROUP_CONCAT(cd2.name SEPARATOR " >> ") AS named_path
									FROM ' . DB_PREFIX . 'category_to_store c2s
									LEFT JOIN ' . DB_PREFIX . 'category_path cp ON (cp.category_id = c2s.category_id)
									LEFT JOIN ' . DB_PREFIX . 'category_description cd1 ON (cp.category_id = cd1.category_id)
									LEFT JOIN ' . DB_PREFIX . 'category_description cd2 ON (cp.path_id = cd2.category_id)
									WHERE c2s.store_id = ' . (int)$this->config->get('store_id') . '
									GROUP BY c2s.category_id
									ORDER BY cp.level ASC');
		$this->categories->setArray($query->rows);
		return $this->categories;
	}
	
	
	public function getInsertedCategoriesID() {
		$this->registry->set('insertedCategories', new Stack());
		$query = $this->db->query('SELECT c.category_id FROM oc_category c
									LEFT JOIN ' . DB_PREFIX . 'category_to_store c2s ON (c.category_id = c2s.category_id)
									WHERE c2s.store_id = ' . (int)$this->config->get('store_id') . '');
		$this->insertedCategories->setArray(array_column($query->rows, 'category_id'));
	}
	
	
	public function getIDsById($id) {
		foreach ($this->categories->toArray() as $category) {
			if ($category['category_id'] == $id) {
				return $category['path'];
			}
		}
	}
	
	
	public function getIDsByNamedPath($named_path, $insert = true, $status = 1) {
		foreach ($this->categories->toArray() as $category) {
			if ($category['named_path'] == $named_path) {
				return $category['path'];
			}
		}
		
		if ($insert) {
			$named_path_arr = explode($this->config->get('category_separator'), $named_path);
			$name = array_pop($named_path_arr);
			$parent_name = implode($this->config->get('category_separator'), $named_path_arr);
			$parent_id = empty($parent_name) ? 0 : $this->getIDsByNamedPath($parent_name, true, 1);
			$category = $this->add(array(
				'name' => $name,
				'parent_id' => $parent_id,
				'status' => (bool)$status,
				'store_id' => (int)$this->config->get('store_id')
			));
			return (string)$category['path'];
		}
	}
	
	/* 
	 * $data = array(
	 * 		'name',
	 *      'category_id',
	 *      'parent_id',
	 *      'status',
	 *      'store_id',
	 * )
	 */
	public function add($data = array()) {
		$sql = 'INSERT INTO ' . DB_PREFIX . 'category
							SET parent_id = "' . (int)$data['parent_id'] . '",
								top = "' . (($data['parent_id']>0) ? '0' : '1') . '",
								`column` = "0",
								sort_order = "' . (($data['name']=='Другое') ? 10 : 5) . '",
								status = "' . (isset($data['status']) ? (int)$data['status'] : 0) . '",
								date_modified = NOW(),
								date_added = NOW()';
		if (isset($data['category_id'])) {
			$sql .= ',
								category_id = "' . (int)$data['category_id'] . '"';
		}
		$this->db->query($sql);
		
		$category_id = $this->db->getLastId();
		
		// Category Description
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_description
							SET category_id = "' . (int)$category_id . '",
								language_id = "1", name = "' . $this->db->escape($data['name']) . '",
								description = "",
								meta_title = "' . $this->db->escape($data['name']) . '",
								meta_description = "",
								meta_keyword = ""');

		// MySQL Hierarchical Data Closure Table Pattern
		// FIX IT
		$level = 0;
		$path = '';
		$query = $this->db->query('SELECT path_id, level FROM ' . DB_PREFIX . 'category_path
									WHERE category_id = "' . (int)$data['parent_id'] . '"
									ORDER BY level ASC');
		foreach ($query->rows as $result) {
			if (!empty($path)) $path .= ',';
			if ((int)$result['path_id'] > 0) $path .= $result['path_id'];
			$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_path
								SET category_id = "' . (int)$category_id . '",
									path_id = "' . (int)$result['path_id'] . '",
									level = "' . (int)$level . '"');
			$level++;
		}
		if (!empty($path)) $path .= ',';
		$path .= (int)$category_id;
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_path
							SET category_id = "' . (int)$category_id . '",
								path_id = "' . (int)$category_id . '",
								level = "' . (int)$level . '"');
		
		// Category to Store
		$store_id = isset($data['store_id']) ? $data['store_id'] : 0;
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_to_store
							SET category_id = "' . (int)$category_id . '",
								store_id = "' . (int)$store_id . '"');
		
		// Named path
		$result = $this->db->query('SELECT
											cp.category_id,
											cd1.name, cp.level,
											GROUP_CONCAT(cp.path_id SEPARATOR ",") AS path,
											GROUP_CONCAT(cd2.name SEPARATOR "' . $this->config->get('category_separator') . '") AS named_path
										FROM ' . DB_PREFIX . 'category_path cp
										LEFT JOIN oc_category_description cd1 ON (cd1.category_id = cp.category_id)
										LEFT JOIN oc_category_description cd2 ON (cd2.category_id = cp.path_id)
										WHERE cp.category_id = "' . (int)$category_id . '"
										GROUP BY cp.category_id
										ORDER BY cp.category_id ASC, cp.level ASC');
		$this->categories->add($result->row);
		return $result->row;
	}
}