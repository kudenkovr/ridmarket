<?

class ControllerUpdateFora extends Controller {
	
	public function index() {
		$data = array();
		
		$data['action'] = $this->url->link('update/fora/import', 'user_token=' . $this->session->data['user_token'], true);
		$this->document->setTitle('Импорт');
		$data['heading_title'] = 'Импорт из Memo Fora 4x4';
		$data['text_form'] = 'Импорт';
		
		$data['header'] = $this->load->controller('common/header', $data);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('update/import', $data));
	}
	
	
	public function import() {
		$this->load->model('update/fora');
		$this->config->load('fora4s');
		
		$file = $_FILES['json']['tmp_name'];
		$output = $this->model_update_fora->import($file);
		
		$data = array();
		
		$data['action'] = $this->url->link('update/fora/import', 'user_token=' . $this->session->data['user_token'], true);
		$this->document->setTitle('Импорт');
		$data['heading_title'] = 'Импорт из Memo Fora 4x4';
		$data['text_form'] = 'Импорт';
		
		$data['success'] = 'Импорт успешно завершён. Товаров добавлено: '.$output['add'].'. Товаров обновлено: '.$output['update'].'.';
		$data['header'] = $this->load->controller('common/header', $data);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('update/import', $data));
	}
	
	
	public function autoimport() {
		$this->load->model('update/fora');
		$this->config->load('fora4s');
		
		$file = $this->config->get('dir_fora_import') . $this->config->get('file_fora_import');
		
		$output = $this->model_update_fora->import($file);
		if (isset($output['success'])) {
			// unlink($file);
		}
		
		ksort($output);
		$this->response->setOutput(json_encode($output));
	}
	
	
	public function clear() {
		$this->load->model('update/fora');
		$this->model_update_fora->clear();
		return $this->index();
	}
	
}