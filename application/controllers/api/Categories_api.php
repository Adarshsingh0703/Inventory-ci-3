<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Categories_api — JSON API for Categories + Audit Logs
 *
 * Endpoints (same pattern as Items_api):
 *   GET    /index.php/api/categories
 *   GET    /index.php/api/categories/{id}
 *   POST   /index.php/api/categories
 *   POST   /index.php/api/categories/{id} + _method=PUT
 *   POST   /index.php/api/categories/{id} + _method=DELETE
 *
 * NOTE: audit_log.item_id references the items table; for category
 *       actions we log with item_id = NULL and include category data
 *       inside "details".
 */
class Categories_api extends CI_Controller
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

        $this->load->model('Category_model');
        $this->load->model('Audit_log_model');   // <-- for audit logs
        $this->load->library('form_validation');
    }

    /** Central router using GET/POST + _method override */
    public function router($id = null)
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        if ($method === 'GET') {
            if ($id === null) return $this->index();
            return $this->show((int)$id);
        }

        $payload  = $this->readRequestData();
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

        return $this->json(['error' => 'Method Not Allowed'], 405);
    }

    /** GET /api/categories — list all */
    private function index()
    {
        $rows = $this->Category_model->all('id', 'DESC');
        return $this->json(['categories' => $rows], 200);
    }

    /** GET /api/categories/{id} — single */
    private function show($id)
    {
        $row = $this->Category_model->find((int)$id);
        if (!$row) return $this->json(['error' => 'Not found'], 404);
        return $this->json($row, 200);
    }

    /** POST /api/categories — create (logs category.create) */
    private function create(array $data)
    {
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
        $this->form_validation->set_rules('parent_id', 'Parent', 'integer');

        if (!$this->form_validation->run()) {
            return $this->json(['errors' => $this->form_validation->error_array()], 422);
        }

        $id = $this->Category_model->create([
            'name'      => trim($data['name']),
            'parent_id' => isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null,
        ]);

        // --- AUDIT: category.create (item_id is NULL)
        $this->Audit_log_model->log(
            'category.create',
            json_encode(['payload' => ['id' => $id] + $data], JSON_UNESCAPED_UNICODE),
            null
        );

        return $this->json(['message' => 'Created', 'id' => $id], 201);
    }

    /** POST /api/categories/{id} + _method=PUT — update (logs category.update) */
    private function update($id, array $data)
    {
        $old = $this->Category_model->find((int)$id);
        if (!$old) return $this->json(['error' => 'Not found'], 404);

        // Prevent self-parenting
        if (isset($data['parent_id']) && (string)$data['parent_id'] !== '' && (int)$data['parent_id'] === (int)$id) {
            return $this->json(['errors' => ['parent_id' => 'Parent cannot be the same as the category.']], 422);
        }

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[255]');
        $this->form_validation->set_rules('parent_id', 'Parent', 'integer');

        if (!$this->form_validation->run()) {
            return $this->json(['errors' => $this->form_validation->error_array()], 422);
        }

        $ok = $this->Category_model->update_by_id((int)$id, [
            'name'      => trim($data['name']),
            'parent_id' => isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null,
        ]);

        if (!$ok) return $this->json(['error' => 'Update failed'], 400);

        // --- AUDIT: category.update (item_id is NULL)
        $this->Audit_log_model->log(
            'category.update',
            json_encode(['before' => $old, 'after' => $data, 'category_id' => (int)$id], JSON_UNESCAPED_UNICODE),
            null
        );

        return $this->json(['message' => 'Updated'], 200);
    }

    /** POST /api/categories/{id} + _method=DELETE — delete (logs category.delete) */
    private function destroy($id)
    {
        $old = $this->Category_model->find((int)$id);
        if (!$old) return $this->json(['error' => 'Not found'], 404);

        $ok = $this->Category_model->delete_by_id((int)$id);
        if (!$ok) return $this->json(['error' => 'Delete failed'], 400);

        // --- AUDIT: category.delete (item_id is NULL)
        $this->Audit_log_model->log(
            'category.delete',
            json_encode(['deleted' => $old, 'category_id' => (int)$id], JSON_UNESCAPED_UNICODE),
            null
        );

        return $this->json(['message' => 'Deleted'], 200);
    }

    /** JSON helper */
    private function json($data, $code = 200)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }

    /** Read JSON or form data */
    private function readRequestData()
    {
        $ct = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
        if (strpos($ct, 'application/json') !== false) {
            $raw  = $this->input->raw_input_stream;
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }
        return $this->input->post(NULL, TRUE) ?: [];
    }
}
