<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Require login for all actions in this controller
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }
        $this->load->model('Item_model');
        $this->load->model('Category_model');
        $this->load->library('form_validation');
    }

    /** List items with optional search (?q=) */
    public function index()
    {
        $q = trim((string) $this->input->get('q', TRUE));
        $data['q']     = $q;
        $data['items'] = $this->Item_model->list($q, 'id', 'DESC');

        // Map category id => name for display
        $cats = $this->Category_model->all('name', 'ASC');
        $map  = [];
        foreach ($cats as $c) { $map[(int)$c['id']] = $c['name']; }
        $data['category_map'] = $map;

        $this->load->view('items/index', $data);
    }

    /** Create item (GET form + POST handler) */
    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
            $this->form_validation->set_rules('sku', 'SKU', 'max_length[100]');              // allow empty
            $this->form_validation->set_rules('description', 'Description', 'max_length[65535]'); // allow empty
            $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('price', 'Price', 'required|decimal');
            $this->form_validation->set_rules('category_id', 'Category', 'integer');        // allow empty

            if ($this->form_validation->run() === TRUE) {
                $payload = [
                    'name'        => trim($this->input->post('name', TRUE)),
                    'sku'         => ($this->input->post('sku') !== '') ? trim((string)$this->input->post('sku', TRUE)) : null,
                    'description' => ($this->input->post('description') !== '') ? trim((string)$this->input->post('description', TRUE)) : null,
                    'quantity'    => (int)$this->input->post('quantity'),
                    'price'       => (float)$this->input->post('price'),
                    'category_id' => $this->input->post('category_id') !== '' ? (int)$this->input->post('category_id') : null,
                ];
                $this->Item_model->create($payload);
                redirect('items');
                return;
            }
            // else fall through to show errors
        }

        $data['categories'] = $this->Category_model->all('name', 'ASC');
        $this->load->view('items/form', $data);
    }

    /** Edit item (GET form + POST handler) */
    public function edit($id = null)
    {
        $id = (int) $id;
        if (!$id) { redirect('items'); return; }

        $item = $this->Item_model->find($id);
        if (!$item) { redirect('items'); return; }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
            $this->form_validation->set_rules('sku', 'SKU', 'max_length[100]');              // allow empty
            $this->form_validation->set_rules('description', 'Description', 'max_length[65535]'); // allow empty
            $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than_equal_to[0]');
            $this->form_validation->set_rules('price', 'Price', 'required|decimal');
            $this->form_validation->set_rules('category_id', 'Category', 'integer');        // allow empty

            if ($this->form_validation->run() === TRUE) {
                $payload = [
                    'name'        => trim($this->input->post('name', TRUE)),
                    'sku'         => ($this->input->post('sku') !== '') ? trim((string)$this->input->post('sku', TRUE)) : null,
                    'description' => ($this->input->post('description') !== '') ? trim((string)$this->input->post('description', TRUE)) : null,
                    'quantity'    => (int)$this->input->post('quantity'),
                    'price'       => (float)$this->input->post('price'),
                    'category_id' => $this->input->post('category_id') !== '' ? (int)$this->input->post('category_id') : null,
                ];
                $this->Item_model->update_by_id($id, $payload);
                redirect('items');
                return;
            }
            // else fall through to show form with errors
        }

        $data['item']       = $item;
        $data['categories'] = $this->Category_model->all('name', 'ASC');
        $this->load->view('items/form', $data);
    }

    /** Delete item */
    public function delete($id = null)
    {
        $id = (int) $id;
        if ($id) {
            $this->Item_model->delete_by_id($id);
        }
        redirect('items');
    }
}
