<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_model extends CI_Model
{
    protected $table = 'items';

    /**
     * List items with optional search, ordering and pagination.
     *
     * @param string|null $q       search query (name or sku)
     * @param string      $orderBy column to order by
     * @param string      $orderDir ASC|DESC
     * @param int|null    $limit   number of rows to return (nullable for no limit)
     * @param int|null    $offset  offset for pagination
     * @return array rows
     */
    public function list($q = null, $orderBy = 'id', $orderDir = 'DESC', $limit = null, $offset = null)
    {
        $this->db->select('*')->from($this->table);

        if ($q !== null && $q !== '') {
            $q = trim((string)$q);
            $this->db->group_start();
            $this->db->like('name', $q);
            $this->db->or_like('sku', $q);
            $this->db->group_end();
        }

        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
        $this->db->order_by($orderBy, $orderDir);

        if ($limit !== null) {
            $this->db->limit((int)$limit, (int)$offset);
        }

        $qres = $this->db->get();
        return $qres->result_array();
    }

    /**
     * Count total items for a given optional search filter.
     *
     * @param string|null $q
     * @return int
     */
    public function count($q = null)
    {
        $this->db->from($this->table);
        if ($q !== null && $q !== '') {
            $q = trim((string)$q);
            $this->db->group_start();
            $this->db->like('name', $q);
            $this->db->or_like('sku', $q);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results();
    }

    /**
     * Find one item by id
     */
    public function find($id)
    {
        return $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
    }

    /**
     * Create a new item
     */
    public function create(array $data)
    {
        $now = date('Y-m-d H:i:s');
        $row = [
            'name'        => $data['name'] ?? null,
            'sku'         => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'quantity'    => isset($data['quantity']) ? (int)$data['quantity'] : 0,
            'price'       => isset($data['price']) ? (float)$data['price'] : 0.00,
            'category_id' => isset($data['category_id']) && $data['category_id'] !== '' ? (int)$data['category_id'] : null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
        $this->db->insert($this->table, $row);
        return $this->db->insert_id();
    }

    /**
     * Update item by id
     */
    public function update_by_id($id, array $data)
    {
        $row = [];
        if (isset($data['name'])) $row['name'] = $data['name'];
        if (array_key_exists('sku', $data)) $row['sku'] = $data['sku'];
        if (array_key_exists('description', $data)) $row['description'] = $data['description'];
        if (isset($data['quantity'])) $row['quantity'] = (int)$data['quantity'];
        if (isset($data['price'])) $row['price'] = (float)$data['price'];
        if (array_key_exists('category_id', $data)) $row['category_id'] = $data['category_id'] !== '' ? (int)$data['category_id'] : null;

        if (empty($row)) return false;

        $row['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, $row);
    }

    /**
     * Delete item by id
     */
    public function delete_by_id($id)
    {
        return (bool)$this->db->delete($this->table, ['id' => (int)$id]);
    }
}
