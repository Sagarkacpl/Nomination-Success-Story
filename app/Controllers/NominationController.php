<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Nomination;
use App\Models\NominationAchievement;
use Exception;

class NominationController extends Controller
{
    private Nomination $nominationModel;
    private NominationAchievement $achievementModel;
    public function __construct()
    {
        $this->nominationModel = new Nomination();
        $this->achievementModel = new NominationAchievement();
    }

    /**
     * AJAX endpoint: ek field save karta hai. Response JSON.
     */
    public function saveField(): void
    {
        header('Content-Type: application/json');

        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in.']);
            return;
        }

        $this->requireMethod('POST');

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrf($csrfToken)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            return;
        }

        $field = $_POST['field'] ?? '';
        $value = $_POST['value'] ?? '';

        // Checkbox arrays (engagement[]) frontend se comma-joined string bhejegi
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $userId = (int) Session::get('user_id');

        try {
            $result = $this->nominationModel->saveField($userId, $this->sanitizeField($field), $value);
            echo json_encode([
                'success' => true,
                'registration_number' => $result['registration_number'],
            ]);
        } catch (Exception $e) {
            error_log('Nomination saveField error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Could not save field.']);
        }
    }

    /**
     * Final submit — sab required fields already saved hain (autosave se),
     * ye sirf final_submission flag set karta hai.
     */
    public function finalSubmit(): void
    {
        header('Content-Type: application/json');

        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in.']);
            return;
        }

        $this->requireMethod('POST');
        $this->verifyCsrfOrFail();

        $userId = (int) Session::get('user_id');
        $existing = $this->nominationModel->findByUserId($userId);

        if (!$existing) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No nomination data found to submit.']);
            return;
        }

        $this->nominationModel->markFinalSubmitted($userId);

        echo json_encode([
            'success' => true,
            'registration_number' => $existing['registration_number'],
        ]);
    }

    /**
     * AJAX endpoint: ek achievement row save/update karta hai (naya row banata hai,
     * kyunki har row apni jagah independent hai). File optional hai.
     */
    public function saveAchievement(): void
    {
        header('Content-Type: application/json');

        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in.']);
            return;
        }

        $this->requireMethod('POST');

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrf($csrfToken)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            return;
        }

        $userId = (int) Session::get('user_id');
        $nomination = $this->nominationModel->findByUserId($userId);

        if (!$nomination) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Please fill personal details first.']);
            return;
        }

        if (!empty($nomination['final_submission'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'This nomination is already final.']);
            return;
        }

        $text = trim($_POST['achievement_text'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        // Existing row ka id agar update ho raha hai (client se aayega, naya row hone par blank/0)
        $achievementId = (int) ($_POST['achievement_id'] ?? 0);

        if (strlen($text) < 10) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Achievement must be at least 10 characters.']);
            return;
        }

        $documentFilename = null;

        // Existing document filename ko preserve karo agar naya file upload nahi hui
        if ($achievementId > 0) {
            $existingRow = $this->achievementModel->findByIdAndNominationId($achievementId, $nomination['id']);
            $documentFilename = $existingRow['document_filename'] ?? null;
        }

        if (!empty($_FILES['achievement_doc']['name'])) {
            try {
                $uploader = new \App\Helpers\FileUploader('nomination_docs/');
                $documentFilename = $uploader->upload(
                    $_FILES['achievement_doc'],
                    ['application/pdf'],
                    ['pdf'],
                    5 * 1024 * 1024
                );
            } catch (Exception $e) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
        }

        if ($achievementId > 0 && $this->achievementModel->findByIdAndNominationId($achievementId, $nomination['id'])) {
            // Update existing row
            $this->achievementModel->updateById($achievementId, $nomination['id'], $text, $documentFilename, $sortOrder);
            $newId = $achievementId;
        } else {
            // Naya row insert karo
            $newId = $this->achievementModel->create($nomination['id'], $text, $documentFilename, $sortOrder);
        }

        echo json_encode([
            'success' => true,
            'achievement_id' => $newId,
            'document_filename' => $documentFilename,
        ]);
    }

    /**
     * AJAX endpoint: ek achievement row delete karta hai.
     */
    public function deleteAchievement(): void
    {
        header('Content-Type: application/json');

        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in.']);
            return;
        }

        $this->requireMethod('POST');

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrf($csrfToken)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            return;
        }

        $userId = (int) Session::get('user_id');
        $nomination = $this->nominationModel->findByUserId($userId);

        if (!$nomination || !empty($nomination['final_submission'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Cannot delete.']);
            return;
        }

        $achievementId = (int) ($_POST['achievement_id'] ?? 0);
        $this->achievementModel->deleteById($achievementId, $nomination['id']);

        echo json_encode(['success' => true]);
    }

    private function sanitizeField(string $field): string
    {
        return preg_replace('/[^a-z_]/', '', strtolower($field));
    }
}