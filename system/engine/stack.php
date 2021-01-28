<?php

class Stack {
	private $data = array();

	/**
     * 
     *
     * @param	string	$key
	 * 
	 * @return	mixed
     */
	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

    /**
     * 
     *
     * @param	string	$key
	 * @param	string	$value
     */	
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
    /**
     * 
     *
	 * @param	mixed	$value
     */	
	public function add($value) {
		$this->data[] = $value;
	}
	
    /**
     * 
     *
     * @param	array	$array
     */
	public function setArray($data) {
		$data = array_replace($this->data, $data);
		$this->data = $data;
	}
	
    /**
     * 
	 *
	 * @return	array
     */
	public function toArray() {
		return $this->data;
	}
	
    /**
     * 
     *
     * @param	string	$key
	 *
	 * @return	bool
     */
	public function has($key) {
		return array_key_exists($key, $this->data);
	}
	
    /**
     * 
	 *
	 * @return	bool
     */
	public function isEmpty() {
		return empty($this->data);
	}
	
    /**
     * 
     *
     * @param	string	$key
	 *
	 * @return	bool
     */
	public function hasValue($value) {
		return in_array($value, $this->data);
	}
}