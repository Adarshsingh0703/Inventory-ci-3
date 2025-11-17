<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // require login
        $this->load->library('session');
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }

        $this->load->database();
        $this->load->helper(['url', 'form']);
    }

    /**
     * Items page (AJAX + pagination UI)
     * Uses application/views/items/index.php
     */
    public function index()
    {
        $data = [
            'flash_success' => $this->session->flashdata('success'),
            'flash_error'   => $this->session->flashdata('error'),
        ];
        $this->load->view('items/index', $data);
    }

    /**
     * Download ALL items as CSV
     * Route: /items/export-csv
     */
    public function export_csv()
    {
        $filename = 'items_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // CSV header row (you can add/remove columns if needed)
        fputcsv($out, [
            'id',
            'name',
            'sku',
            'description',
            'quantity',
            'price',
            'category_id',
            'created_at',
            'updated_at',
        ]);

        $query = $this->db->order_by('id', 'ASC')->get('items');
        foreach ($query->result_array() as $row) {
            fputcsv($out, [
                $row['id'],
                $row['name'],
                $row['sku'],
                $row['description'],
                $row['quantity'],
                $row['price'],
                $row['category_id'],
                $row['created_at'],
                $row['updated_at'],
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Bulk import items from CSV
     * Route: /items/import-csv  (POST, file input name="csv_file")
     *
     * CSV format (header row):
     * name,sku,description,quantity,price,category_id
     *
     * Behavior:
     * - If SKU is non-empty and already exists => UPDATE that item
     * - Else => INSERT new item
     */
    public function import_csv()
    {
        if (empty($_FILES['csv_file']['name'])) {
            $this->session->set_flashdata('error', 'Please select a CSV file to upload.');
            redirect('items');
            return;
        }

        $tmpName = $_FILES['csv_file']['tmp_name'];
        if (!is_uploaded_file($tmpName)) {
            $this->session->set_flashdata('error', 'Upload failed. Please try again.');
            redirect('items');
            return;
        }

        $handle = fopen($tmpName, 'r');
        if ($handle === false) {
            $this->session->set_flashdata('error', 'Could not open uploaded file.');
            redirect('items');
            return;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowNum  = 0;
        $now     = date('Y-m-d H:i:s');

        $this->db->trans_start();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip header row if present
            if ($rowNum === 1) {
                // If first row has "name" as first column, treat as header
                if (isset($row[0]) && strtolower(trim($row[0])) === 'name') {
                    continue;
                }
            }

            // Expected columns: name,sku,description,quantity,price,category_id
            $name        = isset($row[0]) ? trim($row[0]) : '';
            $sku         = isset($row[1]) ? trim($row[1]) : '';
            $description = isset($row[2]) ? trim($row[2]) : '';
            $quantity    = isset($row[3]) ? (int)$row[3] : 0;
            $price       = isset($row[4]) ? (float)$row[4] : 0.00;
            $category_id = isset($row[5]) && $row[5] !== '' ? (int)$row[5] : null;

            // Ignore completely empty rows
            if ($name === '' && $sku === '') {
                $skipped++;
                continue;
            }

            $data = [
                'name'        => $name,
                'sku'         => $sku,
                'description' => $description,
                'quantity'    => $quantity,
                'price'       => $price,
                'category_id' => $category_id,
                'updated_at'  => $now,
            ];

            // If SKU is provided, try UPSERT by SKU
            $existing = null;
            if ($sku !== '') {
                $existing = $this->db
                    ->get_where('items', ['sku' => $sku])
                    ->row_array();
            }

            if ($existing) {
                // UPDATE existing item
                $this->db->where('id', $existing['id'])->update('items', $data);
                $updated++;
            } else {
                // INSERT new item
                $data['created_at'] = $now;
                $this->db->insert('items', $data);
                $created++;
            }
        }

        fclose($handle);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Import failed. Transaction rolled back.');
        } else {
            $msg = "Import completed. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.";
            $this->session->set_flashdata('success', $msg);
        }

        redirect('items');
    }

    /* --- Placeholder methods so routes don't 404 (not used by AJAX UI) --- */

    public function create()
    {
        redirect('items');
    }

    public function edit($id = null)
    {
        redirect('items');
    }

    public function delete($id = null)
    {
        redirect('items');
    }
}
