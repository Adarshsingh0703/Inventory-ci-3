<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Items_page extends CI_Controller
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
     * Show all items (simple table). Route: /all-items
     */
    public function index()
    {
        // simple fetch (you can add pagination later)
        $query = $this->db->order_by('id', 'DESC')->get('items');
        $items = $query->result_array();

        $data = [
            'items' => $items,
            'display_name' => $this->session->userdata('display_name') ?: $this->session->userdata('username'),
        ];

        $this->load->view('items/index', $data);
    }
}
