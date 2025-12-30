<?php
/**
 * =====================================================
 * CARDAPIO CONTROLLER - Admin do Cardápio Web
 * Arquivo: app/Controllers/Admin/CardapioController.php
 * 
 * EXEMPLO DE IMPLEMENTAÇÃO
 * O técnico deve expandir este controller
 * =====================================================
 */

namespace App\Controllers\Admin;

use App\Core\Database;
use PDO;

class CardapioController {

    /**
     * Exibe a tela de configuração do cardápio
     */
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        // Busca configuração atual (ou cria padrão)
        $stmt = $conn->prepare("SELECT * FROM cardapio_config WHERE restaurant_id = :rid");
        $stmt->execute(['rid' => $restaurantId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não existe, cria registro padrão
        if (!$config) {
            $conn->prepare("INSERT INTO cardapio_config (restaurant_id) VALUES (:rid)")
                 ->execute(['rid' => $restaurantId]);
            
            $stmt->execute(['rid' => $restaurantId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Renderiza a view
        require __DIR__ . '/../../../views/admin/cardapio/index.php';
    }

    /**
     * Salva as configurações do cardápio
     */
    public function update() {
        $this->checkSession();
        $conn = Database::connect();
        $restaurantId = $_SESSION['loja_ativa_id'];

        // Coleta dados do formulário
        $data = [
            'primary_color' => $_POST['primary_color'] ?? '#2563eb',
            'secondary_color' => $_POST['secondary_color'] ?? '#f59e0b',
            'opening_time' => $_POST['opening_time'] ?? '08:00',
            'closing_time' => $_POST['closing_time'] ?? '22:00',
            'is_open' => isset($_POST['is_open']) ? 1 : 0,
            'closed_message' => $_POST['closed_message'] ?? '',
            
            'delivery_enabled' => isset($_POST['delivery_enabled']) ? 1 : 0,
            'delivery_fee' => floatval(str_replace(',', '.', $_POST['delivery_fee'] ?? 0)),
            'min_order_value' => floatval(str_replace(',', '.', $_POST['min_order_value'] ?? 0)),
            'delivery_time_min' => intval($_POST['delivery_time_min'] ?? 30),
            'delivery_time_max' => intval($_POST['delivery_time_max'] ?? 45),
            
            'pickup_enabled' => isset($_POST['pickup_enabled']) ? 1 : 0,
            'pickup_discount_percent' => floatval($_POST['pickup_discount_percent'] ?? 0),
            
            'dine_in_enabled' => isset($_POST['dine_in_enabled']) ? 1 : 0,
            
            'whatsapp_number' => preg_replace('/\D/', '', $_POST['whatsapp_number'] ?? ''),
            
            'accept_cash' => isset($_POST['accept_cash']) ? 1 : 0,
            'accept_credit' => isset($_POST['accept_credit']) ? 1 : 0,
            'accept_debit' => isset($_POST['accept_debit']) ? 1 : 0,
            'accept_pix' => isset($_POST['accept_pix']) ? 1 : 0,
            'pix_key' => $_POST['pix_key'] ?? '',
            'pix_key_type' => $_POST['pix_key_type'] ?? 'telefone',
        ];

        // Atualiza no banco
        $sql = "UPDATE cardapio_config SET 
                    primary_color = :primary_color,
                    secondary_color = :secondary_color,
                    opening_time = :opening_time,
                    closing_time = :closing_time,
                    is_open = :is_open,
                    closed_message = :closed_message,
                    delivery_enabled = :delivery_enabled,
                    delivery_fee = :delivery_fee,
                    min_order_value = :min_order_value,
                    delivery_time_min = :delivery_time_min,
                    delivery_time_max = :delivery_time_max,
                    pickup_enabled = :pickup_enabled,
                    pickup_discount_percent = :pickup_discount_percent,
                    dine_in_enabled = :dine_in_enabled,
                    whatsapp_number = :whatsapp_number,
                    accept_cash = :accept_cash,
                    accept_credit = :accept_credit,
                    accept_debit = :accept_debit,
                    accept_pix = :accept_pix,
                    pix_key = :pix_key,
                    pix_key_type = :pix_key_type
                WHERE restaurant_id = :rid";

        $data['rid'] = $restaurantId;
        $conn->prepare($sql)->execute($data);

        header('Location: ' . BASE_URL . '/admin/loja/cardapio?success=1');
        exit;
    }

    /**
     * Verifica sessão
     */
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
