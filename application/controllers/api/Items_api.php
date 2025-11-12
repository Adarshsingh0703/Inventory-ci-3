<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Items_api — JSON API for Items (no page reload)
 *
 * Routing approach:
 *   - We use a single 'router' method and inspect HTTP verb + optional `_method`
 *     so we don't rely on verb-specific routes (simpler on CI3).
 *
 * Endpoints (same URL, different method/_method):
 *   GET    /index.php/api/items            -> list
 *   GET    /index.php/api/items/{id}       -> show
 *   POST   /index.php/api/items            -> create
 *   POST   /index.php/api/items/{id} with _method=PUT    -> update
 *   POST   /index.php/api/items/{id} with _method=DELETE -> delete
 *
 * Notes:
 *   - Requires session login (same-origin).
 *   - Accepts JSON body or standard form-encoded.
 */
class Items_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Require login for all API access
        if (!$this->session->userdata('user_id')) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json','utf-8')
                ->set_output(json_encode(['error' => 'Unauthorized']));
            exit;
        }

        $this->load->model('Item_model');
        $this->load->model('Category_model');
        $this->load->library('form_validation');
    }

    /** Central router to support GET/POST + _method override for PUT/DELETE */
    public function router($id = null)
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        if ($method === 'GET') {
            if ($id === null) return $this->index();
            return $this->show((int)$id);
        }

        // For non-GET, support JSON body and _method override
        $payload = $this->readRequestData();
        $override = isset($payload['_method']) ? strtoupper($payload['_method']) : null;

        if ($method === 'POST' && $id === null && !$override) {
            return $this->create($payload);
        }

        if ($method === 'POST' && $id !== null && $override === 'PUT') {
            return $this->update((int)$id, $payload);
        }

        if ($method === 'POST' && $id !== null && $override === 'DELETE') {
            return $this->destroy((int)$id);
        }

        // Fallback: method not allowed
        return $this->json(['error' => 'Method Not Allowed'], 405);
    }

    /** GET /api/items — list items (optional search ?q=) */
    private function index()
    {
        $q = trim((string)$this->input->get('q', TRUE));
        $items = $this->Item_model->list($q, 'id', 'DESC');
        return $this->json(['items' => $items], 200);
    }

    /** GET /api/items/{id} — get single item */
    private function show($id)
    {
        $item = $this->Item_model->find((int)$id);
        if (!$item) return $this->json(['error' => 'Not found'], 404);
        return $this->json($item, 200);
    }

    /** POST /api/items — create item */
    private function create(array $data)
    {
        // Validate (set_data allows validating arrays, not only $_POST)
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
        $this->form_validation->set_rules('sku', 'SKU', 'max_length[100]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[65535]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('price', 'Price', 'required|decimal');
        $this->form_validation->set_rules('category_id', 'Category', 'integer');

        if (!$this->form_validation->run()) {
            return $this->json(['errors' => $this->form_validation->error_array()], 422);
        }

        $id = $this->Item_model->create([
            'name'        => trim($data['name']),
            'sku'         => isset($data['sku']) && $data['sku'] !== '' ? trim($data['sku']) : null,
            'description' => isset($data['description']) && $data['description'] !== '' ? trim($data['description']) : null,
            'quantity'    => (int)$data['quantity'],
            'price'       => (float)$data['price'],
            'category_id' => isset($data['category_id']) && $data['category_id'] !== '' ? (int)$data['category_id'] : null,
        ]);

        return $this->json(['message' => 'Created', 'id' => $id], 201);
    }

    /** POST /api/items/{id} with _method=PUT — update item */
    private function update($id, array $data)
    {
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
        $this->form_validation->set_rules('sku', 'SKU', 'max_length[100]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[65535]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('price', 'Price', 'required|decimal');
        $this->form_validation->set_rules('category_id', 'Category', 'integer');

        if (!$this->form_validation->run()) {
            return $this->json(['errors' => $this->form_validation->error_array()], 422);
        }

        $ok = $this->Item_model->update_by_id((int)$id, [
            'name'        => trim($data['name']),
            'sku'         => isset($data['sku']) && $data['sku'] !== '' ? trim($data['sku']) : null,
            'description' => isset($data['description']) && $data['description'] !== '' ? trim($data['description']) : null,
            'quantity'    => (int)$data['quantity'],
            'price'       => (float)$data['price'],
            'category_id' => isset($data['category_id']) && $data['category_id'] !== '' ? (int)$data['category_id'] : null,
        ]);

        if (!$ok) return $this->json(['error' => 'Update failed or not found'], 400);
        return $this->json(['message' => 'Updated'], 200);
    }

    /** POST /api/items/{id} with _method=DELETE — delete item */
    private function destroy($id)
    {
        $ok = $this->Item_model->delete_by_id((int)$id);
        if (!$ok) return $this->json(['error' => 'Delete failed or not found'], 400);
        return $this->json(['message' => 'Deleted'], 200);
    }

    /** Utility: unified JSON output */
    private function json($data, $code = 200)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }

    /**
     * Utility: read JSON or form data gracefully.
     * - If Content-Type is application/json, parse raw_input_stream
     * - Else, fall back to standard POST
     */
    private function readRequestData()
    {
        $ct = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
        if (strpos($ct, 'application/json') !== false) {
            $raw = $this->input->raw_input_stream;
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }
        // form-encoded
        return $this->input->post(NULL, TRUE) ?: [];
    }
}
