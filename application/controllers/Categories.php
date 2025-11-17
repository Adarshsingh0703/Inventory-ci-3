<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('session');
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
            return;
        }

        $this->load->database();
        $this->load->helper(['url', 'form']);
    }

    /**
     * Categories page (AJAX UI)
     * Uses application/views/categories/index.php
     */
    public function index()
    {
        $data = [
            'flash_success' => $this->session->flashdata('success'),
            'flash_error'   => $this->session->flashdata('error'),
        ];
        $this->load->view('categories/index', $data);
    }

    /**
     * Download ALL categories as CSV
     * Route: /categories/export-csv
     */
    public function export_csv()
    {
        $filename = 'categories_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // CSV header row
        fputcsv($out, [
            'id',
            'name',
            'parent_id',
            'created_at',
            'updated_at',
        ]);

        $query = $this->db->order_by('id', 'ASC')->get('categories');
        foreach ($query->result_array() as $row) {
            fputcsv($out, [
                $row['id'],
                $row['name'],
                $row['parent_id'],
                $row['created_at'],
                $row['updated_at'],
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Bulk import categories from CSV
     * Route: /categories/import-csv (POST, file input name="csv_file")
     *
     * CSV format (header row):
     * name,parent_id
     *
     * Behavior:
     * - Use "name" as unique key:
     *   If name already exists => UPDATE parent_id
     *   Else => INSERT new category
     */
    public function import_csv()
    {
        if (empty($_FILES['csv_file']['name'])) {
            $this->session->set_flashdata('error', 'Please select a CSV file to upload.');
            redirect('categories');
            return;
        }

        $tmpName = $_FILES['csv_file']['tmp_name'];
        if (!is_uploaded_file($tmpName)) {
            $this->session->set_flashdata('error', 'Upload failed. Please try again.');
            redirect('categories');
            return;
        }

        $handle = fopen($tmpName, 'r');
        if ($handle === false) {
            $this->session->set_flashdata('error', 'Could not open uploaded file.');
            redirect('categories');
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
                if (isset($row[0]) && strtolower(trim($row[0])) === 'name') {
                    continue;
                }
            }

            // Expected columns: name,parent_id
            $name      = isset($row[0]) ? trim($row[0]) : '';
            $parent_id = isset($row[1]) && $row[1] !== '' ? (int)$row[1] : null;

            if ($name === '') {
                $skipped++;
                continue;
            }

            $data = [
                'name'       => $name,
                'parent_id'  => $parent_id,
                'updated_at' => $now,
            ];

            // unique by name
            $existing = $this->db
                ->get_where('categories', ['name' => $name])
                ->row_array();

            if ($existing) {
                $this->db->where('id', $existing['id'])->update('categories', $data);
                $updated++;
            } else {
                $data['created_at'] = $now;
                $this->db->insert('categories', $data);
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

        redirect('categories');
    }

    /* --- Placeholder methods for existing routes (not used by AJAX UI) --- */

    public function create()
    {
        redirect('categories');
    }

    public function edit($id = null)
    {
        redirect('categories');
    }

    public function delete($id = null)
    {
        redirect('categories');
    }
}
