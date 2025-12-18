<?php 
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Controller/PostController.php";
require_once __DIR__ . "/../Controller/ReactionController.php";

class StatisticsController {
    
    private $postC;
    private $reactionC;
    
    public function __construct() {
        $this->postC = new PostController();
        $this->reactionC = new ReactionController();
    }
    
   
    public function getAllStatistics() {
        $db = Config::getConnexion();
        
        try {
     
            $sql = "SELECT 
                        Posts.id,
                        Posts.content,
                        Posts.created_at,
                        Posts.author_id,
                        IFNULL(Users.username, 'Unknown') AS username,
                        (SELECT COUNT(*) FROM Reactions WHERE Reactions.post_id = Posts.id AND Reactions.type = 'heart') as like_count
                    FROM Posts
                    LEFT JOIN Users ON Users.id = Posts.author_id
                    ORDER BY Posts.created_at DESC";
            
            $query = $db->prepare($sql);
            $query->execute();
            $posts = $query->fetchAll();
            
           
            $stats = [
                'total_posts' => count($posts),
                'total_likes' => 0,
                'average_likes_per_post' => 0,
                'most_liked_post' => null,
                'posts_by_date' => [],
                'likes_by_date' => [],
                'posts_by_hour' => [],
                'engagement_rate' => 0,
                'posts_data' => []
            ];
            
            $maxLikes = 0;
            $datePosts = [];
            $dateLikes = [];
            $hourPosts = [];
            
            foreach ($posts as $post) {
                $likeCount = (int)$post['like_count'];
                $stats['total_likes'] += $likeCount;
                
                if ($likeCount > $maxLikes) {
                    $maxLikes = $likeCount;
                    $stats['most_liked_post'] = [
                        'id' => $post['id'],
                        'content' => $post['content'],
                        'likes' => $likeCount,
                        'author' => $post['username'],
                        'date' => $post['created_at']
                    ];
                }
                
                $date = date('Y-m-d', strtotime($post['created_at']));
                if (!isset($datePosts[$date])) {
                    $datePosts[$date] = 0;
                    $dateLikes[$date] = 0;
                }
                $datePosts[$date]++;
                $dateLikes[$date] += $likeCount;
                
                $hour = date('H', strtotime($post['created_at']));
                if (!isset($hourPosts[$hour])) {
                    $hourPosts[$hour] = 0;
                }
                $hourPosts[$hour]++;
                
                $stats['posts_data'][] = [
                    'id' => $post['id'],
                    'content' => substr($post['content'], 0, 100) . (strlen($post['content']) > 100 ? '...' : ''),
                    'likes' => $likeCount,
                    'date' => $post['created_at'],
                    'author' => $post['username']
                ];
            }
            
            if ($stats['total_posts'] > 0) {
                $stats['average_likes_per_post'] = round($stats['total_likes'] / $stats['total_posts'], 2);
                $stats['engagement_rate'] = $stats['total_posts'] > 0 ? round(($stats['total_likes'] / $stats['total_posts']) * 100, 2) : 0;
            }
            
            ksort($datePosts);
            ksort($dateLikes);
            $stats['posts_by_date'] = $datePosts;
            $stats['likes_by_date'] = $dateLikes;
            
            ksort($hourPosts);
            $stats['posts_by_hour'] = $hourPosts;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error in getAllStatistics: " . $e->getMessage());
            return null;
        }
    }
    
    public function getAIInsights($stats) {
        if (!$stats || $stats['total_posts'] == 0) {
            return "No data available for analysis. Start posting to see your statistics and AI-powered insights!";
        }
        
        $dataSummary = "Statistics Summary:
- Total Posts: {$stats['total_posts']}
- Total Likes: {$stats['total_likes']}
- Average Likes per Post: {$stats['average_likes_per_post']}
- Engagement Rate: {$stats['engagement_rate']}%
- Most Liked Post: " . ($stats['most_liked_post'] ? "{$stats['most_liked_post']['likes']} likes" : "N/A") . "
- Best Posting Hour: " . (count($stats['posts_by_hour']) > 0 ? array_search(max($stats['posts_by_hour']), $stats['posts_by_hour']) . ":00" : "N/A");
        
        try {
            $aiInsights = $this->callHuggingFaceAPI($dataSummary);
            if (!empty($aiInsights) && strlen($aiInsights) > 20) {
                return $aiInsights;
            }
        } catch (Exception $e) {
            error_log("Error calling Hugging Face API: " . $e->getMessage());
        }
        
        return $this->generateSimpleInsights($stats);
    }
    
    private function callHuggingFaceAPI($dataSummary) {     
        $apiUrl = "https://api-inference.huggingface.co/models/gpt2";
        $apiToken = "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"; 
        
        if (empty($apiToken)) {
            throw new Exception("API token not configured");
        }
        
        $prompt = "Social Media Statistics Analysis:\n\n" . 
                  $dataSummary . 
                  "\n\nKey Insights:";
        
        $data = [
            'inputs' => $prompt,
            'parameters' => [
                'max_length' => 400,
                'temperature' => 0.9,
                'return_full_text' => false,
                'do_sample' => true,
                'top_p' => 0.9,
                'repetition_penalty' => 1.2
            ]
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiToken
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
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
                $generatedText = str_replace($prompt, '', $generatedText);
                $generatedText = trim($generatedText);
                return !empty($generatedText) ? $generatedText : null;
            } elseif (isset($result['generated_text'])) {
                $generatedText = $result['generated_text'];
                $generatedText = str_replace($prompt, '', $generatedText);
                $generatedText = trim($generatedText);
                return !empty($generatedText) ? $generatedText : null;
            } elseif (isset($result['error'])) {
                if (strpos($result['error'], 'loading') !== false) {
                    sleep(5); 
                    return $this->callHuggingFaceAPI($dataSummary); // Retry once
                }
                throw new Exception("API Error: " . $result['error']);
            }
        } elseif ($httpCode == 503) {
           
            sleep(5);
            return $this->callHuggingFaceAPI($dataSummary); 
        } else {
            throw new Exception("API call failed with HTTP code: " . $httpCode);
        }
        
        throw new Exception("Unexpected API response format");
    }
    
    private function generateSimpleInsights($stats) {
        $insights = [];
        
        if ($stats['total_posts'] > 0) {
            $insights[] = "­ƒôê You've created {$stats['total_posts']} posts and received {$stats['total_likes']} total likes!";
            
            if ($stats['average_likes_per_post'] >= 10) {
                $insights[] = "­ƒîƒ Excellent engagement! Your posts average {$stats['average_likes_per_post']} likes each - you're creating highly engaging content!";
            } elseif ($stats['average_likes_per_post'] >= 5) {
                $insights[] = "Ô£¿ Great engagement! Your posts average {$stats['average_likes_per_post']} likes each. Keep up the good work!";
            } elseif ($stats['average_likes_per_post'] >= 2) {
                $insights[] = "­ƒæì Good engagement with an average of {$stats['average_likes_per_post']} likes per post. Try experimenting with different content types!";
            } else {
                $insights[] = "­ƒÆí Your posts average {$stats['average_likes_per_post']} likes. Consider posting at different times or trying new content formats to boost engagement.";
            }
            
            if ($stats['most_liked_post'] && $stats['most_liked_post']['likes'] > 0) {
                $insights[] = "­ƒÅå Your most popular post received {$stats['most_liked_post']['likes']} likes! Analyze what made it successful and create similar content.";
            }
            
            if (count($stats['posts_by_hour']) > 0) {
                $bestHour = array_search(max($stats['posts_by_hour']), $stats['posts_by_hour']);
                $bestHourCount = $stats['posts_by_hour'][$bestHour];
                $insights[] = "ÔÅ░ Your most active posting time is around {$bestHour}:00 ({$bestHourCount} posts). Your audience seems most engaged during this time!";
            }
            
            if ($stats['engagement_rate'] > 50) {
                $insights[] = "­ƒÄ» Your engagement rate is {$stats['engagement_rate']}% - this is outstanding! Your content resonates well with your audience.";
            } elseif ($stats['engagement_rate'] > 20) {
                $insights[] = "­ƒôè Your engagement rate is {$stats['engagement_rate']}% - good performance! There's room to grow even more.";
            } else {
                $insights[] = "­ƒôè Your engagement rate is {$stats['engagement_rate']}%. Try posting more consistently and engaging with your audience to improve this metric.";
            }
        } else {
            $insights[] = "­ƒÜÇ Start posting to see your statistics and AI-powered insights!";
        }
        
        return implode("\n\n", $insights);
    }
}

?>

