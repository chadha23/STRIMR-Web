<?php
class PointsController extends Controller {

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('');
            return;
        }

        $points = UserPoints::getPoints($_SESSION['user_id']);
        $transactions = UserPoints::getTransactionHistory($_SESSION['user_id'], 20);
        
        $this->view('points/profile', compact('points', 'transactions'));
    }

    public function purchase() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('');
            return;
        }

        $error = $success = '';
        
        if ($_POST) {
            // Sécurisation des inputs
            $amount = intval($_POST['amount'] ?? 0);
            $cardNumber = filter_var($_POST['card_number'] ?? '', FILTER_SANITIZE_STRING);
            $cardExpiry = filter_var($_POST['card_expiry'] ?? '', FILTER_SANITIZE_STRING);
            $cardCVC = filter_var($_POST['card_cvc'] ?? '', FILTER_SANITIZE_STRING);
            
            // Validation supplémentaire côté serveur
            if ($amount < 100) {
                $error = "Montant minimum : 100 points";
            } elseif (!preg_match('/^[0-9\s]{13,19}$/', str_replace(' ', '', $cardNumber))) {
                $error = "Numéro de carte invalide";
            } elseif (!preg_match('/^[0-9]{2}\/[0-9]{2}$/', $cardExpiry)) {
                $error = "Date d'expiration invalide";
            } elseif (!preg_match('/^[0-9]{3,4}$/', $cardCVC)) {
                $error = "Code CVC invalide";
            } else {
                // Simulation du paiement
                if (UserPoints::purchasePoints($_SESSION['user_id'], $amount)) {
                    // Redirection vers le profil avec message de succès
                    $_SESSION['purchase_success'] = "$amount points ajoutés à votre compte ! 🎉";
                    header('Location: /STRIMR/STRIMR-Web/profile');
                    exit;
                } else {
                    $error = "Erreur lors de l'achat";
                }
            }
        }

        $currentPoints = UserPoints::getPoints($_SESSION['user_id']);
        $this->view('points/purchase', compact('error', 'success', 'currentPoints'));
    }

    public function donate() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            return;
        }

        $toUserId = intval($_POST['to_user_id'] ?? 0);
        $amount = intval($_POST['amount'] ?? 0);
        $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Validation de sécurité
        if ($toUserId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Destinataire invalide']);
            return;
        }

        if ($amount < 10) {
            echo json_encode(['success' => false, 'error' => 'Donation minimum : 10 points']);
            return;
        }
        
        if ($amount > 10000) {
            echo json_encode(['success' => false, 'error' => 'Donation maximum : 10 000 points']);
            return;
        }
        
        if (strlen($message) > 500) {
            echo json_encode(['success' => false, 'error' => 'Message trop long']);
            return;
        }

        if (UserPoints::transferPoints($_SESSION['user_id'], $toUserId, $amount, $message)) {
            echo json_encode(['success' => true, 'message' => 'Donation envoyée !']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Points insuffisants']);
        }
    }
}
?>