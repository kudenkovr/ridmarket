<?

class ControllerUpdateB2B extends Controller {
	
	public function auth() {
		if (!$this->config->has('b2b_session')) {
			$this->config->load('b2b');
			$this->load->model('update/b2b');
			$this->model_update_b2b->auth();
		}
	}
	
	public function index() {
		$this->importCategories();
	}
	
	public function importCategories() {
		$this->load->model('update/category');
		$this->auth();
		$this->model_update_b2b->importCategories();
	}
	
	public function importProducts() {
		$this->auth();
	}
	
	public function importPrices() {
		$this->auth();
	}
	
}