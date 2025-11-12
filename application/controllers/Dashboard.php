<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function index()
    {
        // Protect: only logged-in users can see dashboard
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }

        $data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username');
        $this->load->view('dashboard/index', $data);
    }
}
