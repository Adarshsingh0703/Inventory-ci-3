<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Require login for all actions in this controller
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }
        $this->load->model('Category_model');
        $this->load->library('form_validation');
    }

    /** List all categories */
    public function index()
    {
        $data['categories'] = $this->Category_model->all('id', 'DESC');
        $this->load->view('categories/index', $data);
    }

    /** Show create form & handle POST */
    public function create()
    {
        if ($this->input->method() === 'post') {
            // CI3: to allow empty, just DO NOT use 'required'
            $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
            $this->form_validation->set_rules('parent_id', 'Parent', 'integer'); // no 'required' here

            if ($this->form_validation->run() === TRUE) {
                $payload = [
                    'name'      => trim($this->input->post('name', TRUE)),
                    'parent_id' => $this->input->post('parent_id') !== '' ? (int)$this->input->post('parent_id') : null,
                ];
                $this->Category_model->create($payload);
                redirect('categories');
                return;
            }
            // else: fall through and show errors
        }

        $data['parents'] = $this->Category_model->parent_options(); // all categories as potential parents
        $this->load->view('categories/form', $data);
    }

    /** Show edit form & handle POST */
    public function edit($id = null)
    {
        $id = (int) $id;
        if (!$id) { redirect('categories'); return; }

        $category = $this->Category_model->find($id);
        if (!$category) { redirect('categories'); return; }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
            $this->form_validation->set_rules('parent_id', 'Parent', 'integer'); // allow empty

            if ($this->form_validation->run() === TRUE) {
                $payload = [
                    'name'      => trim($this->input->post('name', TRUE)),
                    'parent_id' => $this->input->post('parent_id') !== '' ? (int)$this->input->post('parent_id') : null,
                ];
                $this->Category_model->update_by_id($id, $payload);
                redirect('categories');
                return;
            }
            // else: fall through and show errors with existing data
        }

        $data['category'] = $category;
        $data['parents']  = $this->Category_model->parent_options($id);
        $this->load->view('categories/form', $data);
    }

    /** Delete category */
    public function delete($id = null)
    {
        $id = (int) $id;
        if ($id) {
            $this->Category_model->delete_by_id($id);
        }
        redirect('categories');
    }
}
