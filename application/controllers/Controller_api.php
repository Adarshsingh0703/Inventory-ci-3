<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_page extends CI_Controller
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
     * View: show items for a category
     * URL: /category/{id}
     */
    public function view($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        // fetch category
        $cat = $this->db->get_where('categories', ['id' => $id])->row_array();
        if (!$cat) {
            show_404();
            return;
        }

        // fetch items in this category (simple list, paginate client-side or server-side if needed)
        $items = $this->db->select('*')->from('items')->where('category_id', $id)->order_by('id','DESC')->get()->result_array();

        $data = [
            'category' => $cat,
            'items'    => $items,
            'display_name' => $this->session->userdata('display_name') ?: $this->session->userdata('username'),
        ];

        $this->load->view('category/view', $data);
    }
}
