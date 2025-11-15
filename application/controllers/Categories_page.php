<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories_page extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // require login
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }

        $this->load->database();
        $this->load->helper('url');
    }

    /**
     * Show all categories (simple table). Route: /all-categories
     */
    public function index()
    {
        // simple fetch (you can add pagination later)
        $query = $this->db->order_by('id', 'DESC')->get('categories');
        $categories = $query->result_array();

        $data = [
            'categories' => $categories,
            'display_name' => $this->session->userdata('display_name') ?: $this->session->userdata('username'),
        ];

        $this->load->view('categories/index', $data);
    }
}
