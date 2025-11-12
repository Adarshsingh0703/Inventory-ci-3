<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }
    }

    public function index()
    {
        // Simple page shell; data loads via AJAX
        $this->load->view('audit/index');
    }
}
