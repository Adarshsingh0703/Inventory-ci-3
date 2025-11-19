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
 * CSV format (header row recommended):
 * name,sku,description,quantity,price,category_id
 *
 * - Supports optional `id` column (ignored)
 * - Supports header or no-header CSVs (will infer)
 * - UPSERTs by SKU (update if SKU exists, else insert)
 * - Logs audit entries for create/update
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
    $now     = date('Y-m-d H:i:s');

    // who performed this import? (nullable)
    $userId = $this->session->userdata('user_id') ? (int)$this->session->userdata('user_id') : null;

    // Read first row to detect header/columns
    $firstRow = fgetcsv($handle);
    if ($firstRow === false) {
        fclose($handle);
        $this->session->set_flashdata('error', 'CSV file is empty or unreadable.');
        redirect('items');
        return;
    }

    // Normalize first row for detection
    $normalized = array_map(function($c){ return strtolower(trim((string)$c)); }, $firstRow);

    // helper to find header index by name or synonyms
    $matchCol = function($name) use ($normalized) {
        $name = strtolower($name);
        foreach ($normalized as $i => $h) {
            if ($h === $name) return $i;
        }
        // synonyms
        $synonyms = [
            'name' => ['name','title','product_name','product'],
            'sku' => ['sku','s k u'],
            'description' => ['description','desc'],
            'quantity' => ['quantity','qty','count'],
            'price' => ['price','cost'],
            'category_id' => ['category_id','category','cat_id','categoryid'],
            'id' => ['id']
        ];
        if (isset($synonyms[$name])) {
            foreach ($synonyms[$name] as $syn) {
                foreach ($normalized as $i => $h) {
                    if ($h === $syn) return $i;
                }
            }
        }
        return null;
    };

    // detect if first row looks like a header
    $isHeader = false;
    foreach ($normalized as $h) {
        if (in_array($h, ['name','sku','description','quantity','price','category_id','id','title','product_name'])) {
            $isHeader = true;
            break;
        }
    }

    // prepare column index map
    $colIndex = [
        'id' => null,
        'name' => null,
        'sku' => null,
        'description' => null,
        'quantity' => null,
        'price' => null,
        'category_id' => null
    ];

    if ($isHeader) {
        // map known columns from header
        foreach (array_keys($colIndex) as $col) {
            $idx = $matchCol($col);
            if ($idx !== null) $colIndex[$col] = $idx;
        }
    } else {
        // no header -> infer by column count in first row
        $colCount = count($firstRow);
        if ($colCount >= 7) {
            // assume: id, name, sku, description, quantity, price, category_id
            $colIndex['id'] = 0;
            $colIndex['name'] = 1;
            $colIndex['sku'] = 2;
            $colIndex['description'] = 3;
            $colIndex['quantity'] = 4;
            $colIndex['price'] = 5;
            $colIndex['category_id'] = 6;
        } else {
            // assume: name, sku, description, quantity, price, category_id
            $colIndex['name'] = 0;
            $colIndex['sku'] = 1;
            $colIndex['description'] = 2;
            $colIndex['quantity'] = 3;
            $colIndex['price'] = 4;
            $colIndex['category_id'] = 5;
        }
        // rewind so first row is processed as data
        rewind($handle);
    }

    $this->db->trans_start();

    $rowNum = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $rowNum++;

        // skip entirely empty rows
        if (count(array_filter($row, function($v){ return $v !== null && trim((string)$v) !== ''; })) === 0) {
            $skipped++;
            continue;
        }

        // if header detected, skip the header row itself (defensive)
        if ($isHeader && $rowNum === 1) {
            $possibleHeader = array_map(function($c){ return strtolower(trim((string)$c)); }, $row);
            if ($possibleHeader === $normalized) {
                continue;
            }
        }

        // helper to pick a column value safely
        $get = function($name) use ($row, $colIndex) {
            $i = $colIndex[$name] ?? null;
            if ($i === null) return '';
            return isset($row[$i]) ? trim((string)$row[$i]) : '';
        };

        $name        = $get('name');
        $sku         = $get('sku');
        $description = $get('description');
        $quantityRaw = $get('quantity');
        $priceRaw    = $get('price');
        $categoryRaw = $get('category_id');

        // ignore rows missing both name and sku
        if ($name === '' && $sku === '') {
            $skipped++;
            continue;
        }

        // normalize numbers
        $quantity = is_numeric($quantityRaw) ? (int)$quantityRaw : 0;
        $price = is_numeric($priceRaw) ? (float)$priceRaw : 0.00;

        // category_id: empty / 0 / non-positive => NULL (to avoid FK error)
        $category_id = null;
        if ($categoryRaw !== '') {
            $catInt = (int)$categoryRaw;
            if ($catInt > 0) $category_id = $catInt;
            else $category_id = null;
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

        // UPSERT by SKU if provided
        $existing = null;
        if ($sku !== '') {
            $existing = $this->db->get_where('items', ['sku' => $sku])->row_array();
        }

        if ($existing) {
            // audit old vs new
            $old = [
                'name' => $existing['name'],
                'sku' => $existing['sku'],
                'description' => $existing['description'],
                'quantity' => (int)$existing['quantity'],
                'price' => (float)$existing['price'],
                'category_id' => $existing['category_id'],
            ];
            $new = [
                'name' => $data['name'],
                'sku' => $data['sku'],
                'description' => $data['description'],
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'category_id' => $data['category_id'],
            ];

            $this->db->where('id', $existing['id'])->update('items', $data);
            $updated++;

            // audit log
            $this->db->insert('audit_log', [
                'user_id'    => $userId,
                'action'     => 'update_item',
                'item_id'    => $existing['id'],
                'details'    => json_encode(['old' => $old, 'new' => $new]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            // insert
            $data['created_at'] = $now;
            $this->db->insert('items', $data);
            $newId = (int)$this->db->insert_id();
            $created++;

            // audit log
            $this->db->insert('audit_log', [
                'user_id'    => $userId,
                'action'     => 'create_item',
                'item_id'    => $newId,
                'details'    => json_encode(['new' => $data]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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
