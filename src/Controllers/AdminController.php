<?php
namespace MeuSegredo\Controllers;

use MeuSegredo\Core\Controller;
use MeuSegredo\Core\Security;
use MeuSegredo\Models\User;
use MeuSegredo\Models\Secret;
use MeuSegredo\Models\Comment;
use MeuSegredo\Models\Report;

class AdminController extends Controller {

    private function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['admin_user'])) {
            $this->redirect('/admin/login');
        }
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['admin_user'])) {
            $this->redirect('/admin/dashboard');
        }
        $this->render('admin/login', [
            'title' => 'Login Administrativo - MeuSegredo.Shop'
        ], null);
    }

    public function loginPost() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'] ?? 'admin';
            $this->redirect('/admin/dashboard');
        } else {
            $this->setFlash('Usuário ou senha inválidos.', 'error');
            $this->redirect('/admin/login');
        }
    }

    public function dashboard() {
        $this->requireAuth();

        $secretModel = new Secret();
        $commentModel = new Comment();
        $reportModel = new Report();

        $totalSecrets = $secretModel->count();
        $totalComments = $commentModel->count();
        $totalReports = $reportModel->count(['status' => 'pending']);

        $reports = $reportModel->getPendingReports();

        $this->render('admin/dashboard', [
            'title' => 'Painel Admin - MeuSegredo.Shop',
            'totalSecrets' => $totalSecrets,
            'totalComments' => $totalComments,
            'totalReports' => $totalReports,
            'reports' => $reports
        ], 'admin');
    }

    public function reportAction() {
        $this->requireAuth();

        $reportId = $_POST['report_id'] ?? 0;
        $action = $_POST['action'] ?? '';

        if (!$reportId || !in_array($action, ['approve', 'reject'])) {
            $this->setFlash('Ação inválida.', 'error');
            $this->redirect('/admin/dashboard');
        }

        $reportModel = new Report();
        $report = $reportModel->findById($reportId);

        if (!$report) {
            $this->setFlash('Denúncia não encontrada.', 'error');
            $this->redirect('/admin/dashboard');
        }

        if ($action === 'approve') {
            $reportModel->update($reportId, [
                'status' => 'rejected',
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolved_by' => $_SESSION['admin_user']
            ]);
            $this->setFlash('Denúncia rejeitada. O conteúdo foi mantido.', 'success');
        } else {
            $reportModel->update($reportId, [
                'status' => 'approved',
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolved_by' => $_SESSION['admin_user']
            ]);

            if ($report['secret_id']) {
                $secretModel = new Secret();
                $secretModel->update($report['secret_id'], ['status' => 'removed']);
            } elseif ($report['comment_id']) {
                $commentModel = new Comment();
                $commentModel->update($report['comment_id'], ['status' => 'removed']);
            }

            $this->setFlash('Conteúdo removido e denúncia arquivada com sucesso.', 'success');
        }

        $this->redirect('/admin/dashboard');
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['admin_user'], $_SESSION['admin_role']);
        session_destroy();
        $this->redirect('/admin/login');
    }
}
