<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Model is used in all actions
        $this->load->model('User_model');
    }

    /**
     * Login screen + POST handler
     */
    public function login()
    {
        // If already logged in, go to dashboard
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
            return;
        }

        if ($this->input->method() === 'post') {
            $username = trim($this->input->post('username', TRUE));
            $password = trim((string)$this->input->post('password'));

            // Basic validation
            $this->form_validation->set_rules('username', 'Username', 'required|min_length[3]|max_length[50]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[64]');

            if ($this->form_validation->run() === FALSE) {
                $data['errors'] = validation_errors();
                $this->load->view('auth/login', $data);
                return;
            }

            $user = $this->User_model->get_by_username($username);
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $data['errors'] = '<p>Invalid username or password.</p>';
                $this->load->view('auth/login', $data);
                return;
            }

            // Set session
            $this->session->set_userdata([
                'user_id'      => $user['id'],
                'username'     => $user['username'],
                'display_name' => $user['display_name'] ?: $user['username'],
                'logged_in'    => TRUE,
            ]);

            redirect('dashboard');
            return;
        }

        // GET: show form
        $this->load->view('auth/login');
    }

    /**
     * Registration screen + POST handler
     */
    public function register()
    {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
            return;
        }

        if ($this->input->method() === 'post') {
            $username     = trim($this->input->post('username', TRUE));
            $password     = trim((string)$this->input->post('password'));
            $display_name = trim($this->input->post('display_name', TRUE));

            // Username rules: 3–50, allowed chars only
            $this->form_validation->set_rules(
                'username',
                'Username',
                'required|min_length[3]|max_length[50]|regex_match[/^[A-Za-z0-9._-]+$/]',
                ['regex_match' => 'Username may include letters, numbers, dot, underscore, and hyphen.']
            );

            // Password rules: 8–12, at least 1 uppercase & 1 special char
            $this->form_validation->set_rules(
                'password',
                'Password',
                'required|min_length[8]|max_length[12]|regex_match[/(?=.*[A-Z])(?=.*\W)/]',
                ['regex_match' => 'Password must contain at least one uppercase letter and one special character.']
            );

            if ($this->form_validation->run() === FALSE) {
                $data['errors'] = validation_errors();
                $this->load->view('auth/register', $data);
                return;
            }

            // Uniqueness check
            if ($this->User_model->username_exists($username)) {
                $data['errors'] = '<p>Username already taken.</p>';
                $this->load->view('auth/register', $data);
                return;
            }

            // Create user
            $this->User_model->create_user($username, $password, $display_name ?: null);

            // Redirect to login
            redirect('login');
            return;
        }

        // GET: show form
        $this->load->view('auth/register');
    }

    /**
     * Logout and destroy session
     */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }
}
