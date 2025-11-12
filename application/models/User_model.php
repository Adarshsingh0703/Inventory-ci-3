<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function get_by_username($username)
    {
        return $this->db
            ->get_where($this->table, ['username' => $username])
            ->row_array();
    }

    public function username_exists($username)
    {
        return (bool) $this->db
            ->select('id')
            ->get_where($this->table, ['username' => $username])
            ->row_array();
    }

    public function create_user($username, $password, $display_name = null)
    {
        $data = [
            'username'      => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'display_name'  => $display_name,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
}
