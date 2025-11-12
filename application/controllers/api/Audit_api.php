<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audit_api — JSON API for Audit Logs
 *
 * Endpoints handled by a single router (GET/POST + _method override):
 *   GET    /index.php/api/audit                 -> list (with optional filters)
 *   GET    /index.php/api/audit/{id}            -> show single log
 *   POST   /index.php/api/audit                 -> create (optional/manual logging)
 *   POST   /index.php/api/audit/{id} + _method=DELETE -> delete (optional, usually not used)
 *
 * NOTE: Normally logs are written by other controllers (Items/Categories)
 *       via the Audit_log_model. This API lets you browse (and optionally
 *       create) logs over AJAX without page reloads.
 */
class Audit_api extends CI_Controller
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

        $this->load->model('Audit_log_model');
        $this->load->library('form_validation');
        $this->load->database(); // just in case
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

        // Create (manual log)
        if ($method === 'POST' && $id === null && !$override) {
            return $this->create($payload);
        }

        // Delete (rarely used)
        if ($method === 'POST' && $id !== null && $override === 'DELETE') {
            return $this->destroy((int)$id);
        }

        return $this->json(['error' => 'Method Not Allowed'], 405);
    }

    /** GET /api/audit — list with optional filters & pagination */
    private function index()
    {
        $filters = [
            'action'  => trim((string)$this->input->get('action', TRUE)),
            'user_id' => $this->input->get('user_id', TRUE),
            'item_id' => $this->input->get('item_id', TRUE),
        ];
        $limit  = (int)($this->input->get('limit', TRUE) ?? 100);
        $offset = (int)($this->input->get('offset', TRUE) ?? 0);
        if ($limit <= 0 || $limit > 500) $limit = 100;
        if ($offset < 0) $offset = 0;

        $rows = $this->Audit_log_model->list($filters, $limit, $offset);
        return $this->json([
            'logs'   => $rows,
            'limit'  => $limit,
            'offset' => $offset,
            'filters'=> $filters,
        ], 200);
    }

    /** GET /api/audit/{id} — single log entry (with joined names) */
    private function show($id)
    {
        // Reuse list() with filters to fetch just 1
        $this->db->select('al.*, u.username AS user_username, i.name AS item_name')
                 ->from('audit_log AS al')
                 ->join('users AS u', 'u.id = al.user_id', 'left')
                 ->join('items AS i', 'i.id = al.item_id', 'left')
                 ->where('al.id', (int)$id)
                 ->limit(1);
        $q = $this->db->get();
        $row = $q->row_array();

        if (!$row) return $this->json(['error' => 'Not found'], 404);
        return $this->json($row, 200);
    }

    /** POST /api/audit — create a log entry (optional/manual) */
    private function create(array $data)
    {
        // Validate minimal fields
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('action', 'Action', 'required|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('details', 'Details', 'max_length[65535]');
        $this->form_validation->set_rules('item_id', 'Item', 'integer');
        $this->form_validation->set_rules('user_id', 'User', 'integer');

        if (!$this->form_validation->run()) {
            return $this->json(['errors' => $this->form_validation->error_array()], 422);
        }

        $id = $this->Audit_log_model->log(
            trim($data['action']),
            isset($data['details']) ? (string)$data['details'] : null,
            isset($data['item_id']) && $data['item_id'] !== '' ? (int)$data['item_id'] : null,
            isset($data['user_id']) && $data['user_id'] !== '' ? (int)$data['user_id'] : null
        );

        return $this->json(['message' => 'Created', 'id' => $id], 201);
    }

    /** POST /api/audit/{id} + _method=DELETE — delete a log (optional) */
    private function destroy($id)
    {
        $ok = $this->db->delete('audit_log', ['id' => (int)$id]);
        if (!$ok) return $this->json(['error' => 'Delete failed or not found'], 400);
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
