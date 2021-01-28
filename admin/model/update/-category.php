<?php

class ModelUpdateCategory extends Model {
	public $delimeter = '>';
/* 	public function getCategories($named = false) {
		$categories = $this->registry->get('categories');
		if (!empty($categories)) return $categories;
		else {
			$categories = array();
		}

		$query = $this->db->query('SELECT c.category_id AS id, cd.name AS name, (
											SELECT GROUP_CONCAT(cp.path_id) FROM '.DB_PREFIX.'category_path AS cp
											WHERE cp.category_id=c.category_id
											ORDER BY cp.path_id
										) AS category_ids
									FROM ' . DB_PREFIX . 'category AS c, ' . DB_PREFIX . 'category_description AS cd
									WHERE c.category_id = cd.category_id
									ORDER BY cd.name');
		$rows = $query->rows;

		foreach ($rows as $category) {
			if ($named) $categories[$category['name']] = $category['category_ids'];
			else $categories[$category['id']] = $category['category_ids'];
		}

		$this->registry->set('categories', $categories);
		return $categories;
	} */
	
	
	public function getCategories() {
		$this->registry->set('categories', new Stack);
		
		$query = $this->db->query('SELECT IF(c.parent_id = 0, cd.name, CONCAT((
											SELECT cd2.name FROM ' . DB_PREFIX . 'category_description AS cd2
											WHERE cd2.category_id = c.parent_id
										), "'.$this->delimeter.'", cd.name)) AS name, (
											SELECT GROUP_CONCAT(cp.path_id) FROM ' . DB_PREFIX . 'category_path cp
											WHERE cp.category_id=c.category_id
											ORDER BY cp.path_id
										) AS category_ids
										FROM ' . DB_PREFIX . 'category c, ' . DB_PREFIX . 'category_description cd
										WHERE c.category_id = cd.category_id
										ORDER BY c.parent_id');
		$categories = array_column($query->rows, 'category_ids', 'name');
		$this->categories->setArray($categories);
		
		return $this->categories;
	}
	
	
	
	
	
	public function getIDsByNamedPath($name, $parent_name = null, $insert = true) {
		$concat = empty($parent_name) ? $name : $parent_name.$this->delimeter.$name;
		
		if ($this->categories->has($concat)) {
			return $this->categories->get($concat);
		} else if ($insert) {
			if (empty($parent_name)) {
				$id = $this->addCategory($name);
				$this->categories->set($concat, $id);
				return (string)$id;
			} else {
				$parent_id = $this->getIDsByName($parent_name);
				$id = $this->addCategory($name, $parent_id);
				$ids = $parent_id . ',' . $id;
				$this->categories->set($concat, $ids);
				return $ids;
			}
		}
	}
	
	
	
	
	
	public function getIDsByName($name, $parent_name = null, $insert = true) {
		$concat = empty($parent_name) ? $name : $parent_name.$this->delimeter.$name;
		
		if ($this->categories->has($concat)) {
			return $this->categories->get($concat);
		} else if ($insert) {
			if (empty($parent_name)) {
				$id = $this->addCategory($name);
				$this->categories->set($concat, $id);
				return (string)$id;
			} else {
				$parent_id = $this->getIDsByName($parent_name);
				$id = $this->addCategory($name, $parent_id);
				$ids = $parent_id . ',' . $id;
				$this->categories->set($concat, $ids);
				return $ids;
			}
		}
	}
	
	/* 
	 * $data = array(
	 * 		'name',
	 *      'category_id'
	 *      'parent_id'
	 *      'status'
	 *      'store_id'
	 * )
	 */
	public function add($data = array) {
		$sql = 'INSERT INTO ' . DB_PREFIX . 'category
							SET category_id = "' . (int)$data['category_id'] . '",
								parent_id = "' . (int)$data['parent_id'] . '",
								top = "' . (($data['parent_id']>0) ? '0' : '1') . '",
								column = "0",
								sort_order = "' . (($data['name']=='Другое') ? 10 : 5) . '",
								status = "' . (isset($data['status']) ? (int)$data['status'] : 0) . '",
								date_modified = NOW(),
								date_added = NOW()';
		if (isset($data['category_id'])) {
			$sql .= ',
								category_id = "' . (int)$category_id . '"';
		}
		$this->db->query($sql);
		
		$category_id = $this->db->get->getLastId();
		
		// Category Description
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_description
							SET category_id = "' . (int)$category_id . '",
								language_id = "1", name = "' . $this->db->escape($data['name']) . '",
								description = "",
								meta_title = "' . $this->db->escape($value['name']) . '",
								meta_description = "",
								meta_keyword = ""');

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;
		$path = array();
		$query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'category_path
									WHERE category_id = "' . (int)$data['parent_id'] . '"
									ORDER BY level ASC');
		foreach ($query->rows as $result) {
			if ((int)$result['path_id'] > 0) $path[] = (int)$result['path_id'];
			$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_path
								SET category_id = "' . (int)$category_id . '",
									path_id = "' . (int)$result['path_id'] . '",
									level = "' . (int)$level . '"');
			$level++;
		}
		$path[] = (int)$category_id;
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_path
							SET category_id = "' . (int)$category_id . '",
								path_id = "' . (int)$category_id . '",
								level = "' . (int)$level . '"');
		
		// Category to Store
		$store_id = isset($data['store_id']) ? $data['store_id'] : 0;
		$this->db->query('INSERT INTO ' . DB_PREFIX . 'category_to_store
							SET category_id = "' . (int)$category_id . '",
								store_id = "' . (int)$store_id . '"');
		
		$query = $this->db->query('SELECT GROUP_CONCAT(cd.name SEPARATOR "'.$this->delimeter.'") AS named_path
									FROM ' . DB_PREFIX . 'category_path cp
									LEFT JOIN ' . DB_PREFIX . 'category_description cd ON (cd.category_id = cp.path_id)
									WHERE cp.category_id = "'.(int)$category_id.'"
									GROUP BY cp.category_id
									ORDER BY cp.level ASC');
		return array(
			'category_id' => $category_id,
			'name' => $data['name'],
			'parent_id' => $data['parent_id'],
			'path' => implode(',', $path),
			'named_path' => $query->row
		)
	}
	
	// Устаревшая функция
	public function addCategory($name, $parent_id=0, $store_id=0) {
		$sort_order = ($name=='Другое') ? '10' : '5';
		$this->load->model('catalog/category');
		$data = array(
			'image'					=> '',
			'parent_id'				=> (string)$parent_id,
			'top'					=> (($parent_id>0) ? '0' : '1'),
			'column'				=> '0',
			'sort_order'			=> $sort_order,
			'status'				=> '1',
			'date_added'			=> date("Y-m-d H:i:s"),
			'date_modified'			=> date("Y-m-d H:i:s"),

			'category_description'	=> array(1 => array(
				'language_id'			=> '1',
				'name'					=> $name,
				'description'			=> '',
				'meta_title'			=> $name,
				'meta_description'		=> '',
				'meta_keyword'			=> ''
			)),
			'category_store'		=> array((string)$store_id),
		);
		$id = $this->model_catalog_category->addCategory($data);
		$this->registry->set('categories', null);
		$this->getCategories();
		return $id;
	}
}