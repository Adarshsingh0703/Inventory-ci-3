<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audit_api — JSON API for Audit Logs (with pagination)
 *
 * GET /api/audit supports:
 *   - action (contains)
 *   - user_id
 *   - item_id
 *   - page (1-based)
 *   - per_page
 *
 * Response: logs[] and pagination: { total, page, per_page, total_pages, offset }
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
        $this->load->database();
    }

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

        if ($method === 'POST' && $id !== null && $override === 'DELETE') {
            return $this->destroy((int)$id);
        }

        return $this->json(['error' => 'Method Not Allowed'], 405);
    }

    /**
     * GET /api/audit
     */
    private function index()
    {
        $filters = [
            'action'  => trim((string)$this->input->get('action', TRUE)),
            'user_id' => $this->input->get('user_id', TRUE),
            'item_id' => $this->input->get('item_id', TRUE),
        ];

        // pagination params
        $page = (int)$this->input->get('page', TRUE) ?: 1;
        $per_page = (int)$this->input->get('per_page', TRUE) ?: 20;
        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 20;
        if ($per_page > 500) $per_page = 500;

        $offset = ($page - 1) * $per_page;

        $logs = $this->Audit_log_model->list($filters, $per_page, $offset);
        $total = $this->Audit_log_model->count($filters);
        $total_pages = (int) ceil($total / $per_page);

        return $this->json([
            'logs' => $logs,
            'pagination' => [
                'total' => $total,
                'page'  => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'offset' => $offset,
            ],
        ], 200);
    }

    private function show($id)
    {
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

    private function create(array $data)
    {
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('action', 'Action', 'required|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('details', 'Details', 'max_length[65535]');
        $this->form_validation->set_rules('item_id', 'Item', 'integer');
        $this->form_validation->set_rules('user_id', 'User', 'integer');

        if (!$this->form_validation->run()) {
            return $this->json(['errors' => $this->form_validation->error_array()], 422);
        }

        $this->load->model('Audit_log_model');
        $id = $this->Audit_log_model->log(
            trim($data['action']),
            isset($data['details']) ? (string)$data['details'] : null,
            isset($data['item_id']) && $data['item_id'] !== '' ? (int)$data['item_id'] : null,
            isset($data['user_id']) && $data['user_id'] !== '' ? (int)$data['user_id'] : null
        );

        return $this->json(['message' => 'Created', 'id' => $id], 201);
    }

    private function destroy($id)
    {
        $ok = $this->db->delete('audit_log', ['id' => (int)$id]);
        if (!$ok) return $this->json(['error' => 'Delete failed or not found'], 400);
        return $this->json(['message' => 'Deleted'], 200);
    }

    private function json($data, $code = 200)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }

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
