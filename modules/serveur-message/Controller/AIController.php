<?php
require_once __DIR__ . "/../Model/config.php";

class AIController {
    
    private $apiUrl = "https://api-inference.huggingface.co/models/gpt2";
    private $apiToken = "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";
    
    /**
     * Get AI response for a chat message
     */
    public function chat(string $userMessage): string {
        if (empty(trim($userMessage))) {
            return "Please send me a message and I'll do my best to help!";
        }
        
        try {
            $response = $this->callHuggingFaceAPI($userMessage);
            if (!empty($response)) {
                return $response;
            }
        } catch (Exception $e) {
            error_log("AI Chat Error: " . $e->getMessage());
        }
        
        // Fallback to simple responses if API fails
        return $this->generateFallbackResponse($userMessage);
    }
    
    /**
     * Call Hugging Face API for text generation
     */
    private function callHuggingFaceAPI(string $userMessage): ?string {
        if (empty($this->apiToken)) {
            throw new Exception("API token not configured");
        }
        
        // Create a conversational prompt
        $prompt = "Human: " . $userMessage . "\nAI Assistant:";
        
        $data = [
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 150,
                'temperature' => 0.8,
                'return_full_text' => false,
                'do_sample' => true,
                'top_p' => 0.9,
                'repetition_penalty' => 1.3
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiToken
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("CURL Error: " . $curlError);
        }
        
        if ($httpCode == 200 && $response) {
            $result = json_decode($response, true);
            
            if (isset($result[0]['generated_text'])) {
                $generatedText = $result[0]['generated_text'];
                // Clean up the response
                $generatedText = trim($generatedText);
                // Remove any "Human:" or "AI:" prefixes that might appear
                $generatedText = preg_replace('/^(Human:|AI Assistant:|AI:)\s*/i', '', $generatedText);
                // Take only the first sentence or paragraph if too long
                if (strlen($generatedText) > 500) {
                    $generatedText = substr($generatedText, 0, 500) . '...';
                }
                return !empty($generatedText) ? $generatedText : null;
            } elseif (isset($result['error'])) {
                if (strpos($result['error'], 'loading') !== false) {
                    // Model is loading, wait and retry
                    sleep(3);
                    return $this->callHuggingFaceAPI($userMessage);
                }
                throw new Exception("API Error: " . $result['error']);
            }
        } elseif ($httpCode == 503) {
            // Service unavailable, retry once
            sleep(3);
            return $this->callHuggingFaceAPI($userMessage);
        }
        
        return null;
    }
    
    /**
     * Generate fallback responses when API is unavailable
     */
    private function generateFallbackResponse(string $userMessage): string {
        $message = strtolower($userMessage);
        
        // Greeting responses
        if (preg_match('/\b(hello|hi|hey|bonjour|salut)\b/i', $message)) {
            $greetings = [
                "Hello! ­ƒæï I'm your AI assistant. How can I help you today?",
                "Hey there! What would you like to chat about?",
                "Hi! Nice to meet you. Feel free to ask me anything!",
                "Hello! I'm here to help. What's on your mind?"
            ];
            return $greetings[array_rand($greetings)];
        }
        
        // How are you responses
        if (preg_match('/how are you|how\'s it going|what\'s up/i', $message)) {
            $responses = [
                "I'm doing great, thanks for asking! ­ƒÿè How can I assist you?",
                "I'm running smoothly! What can I help you with today?",
                "All systems operational! Ready to help you out."
            ];
            return $responses[array_rand($responses)];
        }
        
        // Help responses
        if (preg_match('/\b(help|assist|support)\b/i', $message)) {
            return "I'm here to help! ­ƒñû You can ask me questions, chat about anything, or just say hi. What would you like to know?";
        }
        
        // Thank you responses
        if (preg_match('/\b(thank|thanks|merci)\b/i', $message)) {
            $thanks = [
                "You're welcome! ­ƒÿè Is there anything else I can help with?",
                "Happy to help! Let me know if you need anything else.",
                "Anytime! Feel free to ask more questions."
            ];
            return $thanks[array_rand($thanks)];
        }
        
        // Goodbye responses
        if (preg_match('/\b(bye|goodbye|see you|ciao)\b/i', $message)) {
            $goodbyes = [
                "Goodbye! ­ƒæï Have a great day!",
                "See you later! Come back anytime.",
                "Bye for now! Take care!"
            ];
            return $goodbyes[array_rand($goodbyes)];
        }
        
        // Question about capabilities
        if (preg_match('/what can you do|what are you|who are you/i', $message)) {
            return "I'm an AI assistant powered by GPT-2! ­ƒñû I can chat with you, answer questions, and try to help with various topics. I'm still learning, so I might not always have the perfect answer, but I'll do my best!";
        }
        
        // Default responses for other messages
        $defaults = [
            "That's interesting! Tell me more about it. ­ƒñö",
            "I see! Is there anything specific you'd like to know?",
            "Thanks for sharing! How can I help you further?",
            "Interesting thought! Feel free to ask me any questions.",
            "I'm processing that... Is there something specific you'd like me to help with?"
        ];
        return $defaults[array_rand($defaults)];
    }
    
    /**
     * Store AI conversation in database (optional feature)
     */
    public function saveConversation(string $userMessage, string $aiResponse): bool {
        $db = Config::getConnexion();
        
        try {
            // Store user message
            $sql = "INSERT INTO message (server_id, content, created_at) VALUES (:server_id, :content, NOW())";
            $query = $db->prepare($sql);
            $query->execute([
                'server_id' => 0, // AI server ID
                'content' => "User: " . $userMessage
            ]);
            
            // Store AI response
            $query->execute([
                'server_id' => 0,
                'content' => "AI: " . $aiResponse
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error saving AI conversation: " . $e->getMessage());
            return false;
        }
    }
}
?>

