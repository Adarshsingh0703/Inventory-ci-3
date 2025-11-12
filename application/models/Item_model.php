<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_model extends CI_Model
{
    protected $table = 'items';

    /** List items with optional search by name/SKU */
    public function list($q = null, $orderBy = 'id', $direction = 'DESC')
    {
        if ($q !== null && $q !== '') {
            $this->db->group_start()
                     ->like('name', $q)
                     ->or_like('sku', $q)
                     ->group_end();
        }
        return $this->db->order_by($orderBy, $direction)->get($this->table)->result_array();
    }

    /** Get single item by id */
    public function find($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row_array();
    }

    /** Create an item; returns insert id */
    public function create(array $data)
    {
        $row = [
            'name'        => $data['name'],
            'sku'         => !empty($data['sku']) ? $data['sku'] : null,
            'description' => !empty($data['description']) ? $data['description'] : null,
            'quantity'    => isset($data['quantity']) ? (int) $data['quantity'] : 0,
            'price'       => isset($data['price']) ? (float) $data['price'] : 0.00,
            'image'       => !empty($data['image']) ? $data['image'] : null,
            'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert($this->table, $row);
        return $this->db->insert_id();
    }

    /** Update an item by id; returns bool */
    public function update_by_id($id, array $data)
    {
        $row = [
            'name'        => $data['name'],
            'sku'         => !empty($data['sku']) ? $data['sku'] : null,
            'description' => !empty($data['description']) ? $data['description'] : null,
            'quantity'    => isset($data['quantity']) ? (int) $data['quantity'] : 0,
            'price'       => isset($data['price']) ? (float) $data['price'] : 0.00,
            'image'       => !empty($data['image']) ? $data['image'] : null,
            'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        return $this->db->where('id', (int) $id)->update($this->table, $row);
    }

    /** Delete an item by id; returns bool */
    public function delete_by_id($id)
    {
        return $this->db->delete($this->table, ['id' => (int) $id]);
    }
}
