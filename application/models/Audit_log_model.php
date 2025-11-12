<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_model extends CI_Model
{
    protected $table = 'audit_log';

    /**
     * Write an audit entry.
     *
     * @param string      $action   Short verb phrase, e.g. "item.create", "item.update", "category.delete"
     * @param string|null $details  Optional free-text details (JSON/text)
     * @param int|null    $item_id  Optional related item id
     * @param int|null    $user_id  Optional user id (defaults to session user)
     * @return int insert id
     */
    public function log($action, $details = null, $item_id = null, $user_id = null)
    {
        if ($user_id === null) {
            $user_id = $this->session->userdata('user_id') ?: null;
        }

        $row = [
            'user_id'    => $user_id ? (int)$user_id : null,
            'action'     => (string)$action,
            'item_id'    => $item_id !== null ? (int)$item_id : null,
            'details'    => $details !== null ? (string)$details : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $row);
        return $this->db->insert_id();
    }

    /**
     * Recent logs with joined usernames and item names (when available).
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function recent($limit = 100, $offset = 0)
    {
        $this->db->select('al.*, u.username AS user_username, i.name AS item_name');
        $this->db->from($this->table.' AS al');
        $this->db->join('users AS u', 'u.id = al.user_id', 'left');
        $this->db->join('items AS i', 'i.id = al.item_id', 'left');
        $this->db->order_by('al.id', 'DESC');
        $this->db->limit((int)$limit, (int)$offset);
        $q = $this->db->get();
        return $q->result_array();
    }

    /**
     * Filtered list (optional) by action or user or item.
     *
     * @param array $filters keys: action (like), user_id, item_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function list(array $filters = [], $limit = 100, $offset = 0)
    {
        $this->db->select('al.*, u.username AS user_username, i.name AS item_name');
        $this->db->from($this->table.' AS al');
        $this->db->join('users AS u', 'u.id = al.user_id', 'left');
        $this->db->join('items AS i', 'i.id = al.item_id', 'left');

        if (!empty($filters['action'])) {
            $this->db->like('al.action', $filters['action']);
        }
        if (!empty($filters['user_id'])) {
            $this->db->where('al.user_id', (int)$filters['user_id']);
        }
        if (!empty($filters['item_id'])) {
            $this->db->where('al.item_id', (int)$filters['item_id']);
        }

        $this->db->order_by('al.id', 'DESC');
        $this->db->limit((int)$limit, (int)$offset);
        $q = $this->db->get();
        return $q->result_array();
    }
}
