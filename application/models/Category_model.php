<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{
    protected $table = 'categories';

    /** List categories (optionally paginated later in controller) */
    public function all($orderBy = 'id', $direction = 'DESC')
    {
        return $this->db->order_by($orderBy, $direction)->get($this->table)->result_array();
    }

    /** Get single category by id */
    public function find($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row_array();
    }

    /** Create a category; returns insert id */
    public function create(array $data)
    {
        $row = [
            'name'       => $data['name'],
            'parent_id'  => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($this->table, $row);
        return $this->db->insert_id();
    }

    /** Update category by id; returns bool */
    public function update_by_id($id, array $data)
    {
        $row = [
            'name'       => $data['name'],
            'parent_id'  => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return $this->db->where('id', (int) $id)->update($this->table, $row);
    }

    /** Delete category by id; returns bool */
    public function delete_by_id($id)
    {
        return $this->db->delete($this->table, ['id' => (int) $id]);
    }

    /** List potential parents (exclude current id when editing) */
    public function parent_options($excludeId = null)
    {
        if ($excludeId) {
            $this->db->where('id !=', (int) $excludeId);
        }
        return $this->db->order_by('name', 'ASC')->get($this->table)->result_array();
    }
}
