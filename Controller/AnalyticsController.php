<?php 
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Service/AnalyticsService.php";

class AnalyticsController {
    private $db;
    private $analyticsService;
    
    public function __construct() {
        $this->db = Config::getConnexion();
        $this->analyticsService = new \Service\AnalyticsService();
    }
    
    /**
     * Get post performance statistics
     */
    public function getPostAnalytics($authorId = null) {
        $params = [];
        $authorFilter = '';
        
        if ($authorId) {
            $authorFilter = 'WHERE p.author_id = :author_id ';
            $params['author_id'] = $authorId;
        }
        
        // Get basic post statistics
        $sql = "SELECT 
                    COUNT(*) as total_posts,
                    AVG(like_count) as avg_likes,
                    MAX(like_count) as max_likes,
                    MIN(like_count) as min_likes,
                    SUM(CASE WHEN like_count > 0 THEN 1 ELSE 0 END) as posts_with_likes,
                    HOUR(p.created_at) as hour_of_day,
                    DAYNAME(p.created_at) as day_of_week,
                    COUNT(*) as posts_count
                FROM Posts p
                LEFT JOIN (
                    SELECT post_id, COUNT(*) as like_count 
                    FROM Reactions 
                    WHERE type = 'heart' 
                    GROUP BY post_id
                ) r ON p.id = r.post_id
                $authorFilter
                GROUP BY HOUR(p.created_at), DAYOFWEEK(p.created_at)
                ORDER BY avg_likes DESC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            $timeStats = $query->fetchAll();
            
            // Get top performing posts
            $topPosts = $this->getTopPerformingPosts($authorId);
            
            // Get theme analysis
            $themeAnalysis = $this->analyzePostThemes($authorId);
            
            // Get engagement trends
            $engagementTrends = $this->getEngagementTrends($authorId);
            
            return [
                'time_analysis' => $timeStats,
                'top_posts' => $topPosts,
                'theme_analysis' => $themeAnalysis,
                'engagement_trends' => $engagementTrends
            ];
            
        } catch (Exception $e) {
            error_log("Error in getPostAnalytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top performing posts
     */
    private function getTopPerformingPosts($authorId = null) {
        $params = [];
        $authorFilter = '';
        
        if ($authorId) {
            $authorFilter = 'AND p.author_id = :author_id ';
            $params['author_id'] = $authorId;
        }
        
        $sql = "SELECT 
                    p.id,
                    p.content,
                    p.created_at,
                    COUNT(r.id) as like_count,
                    (SELECT COUNT(*) FROM Comments c WHERE c.post_id = p.id) as comment_count
                FROM Posts p
                LEFT JOIN Reactions r ON p.id = r.post_id AND r.type = 'heart'
                WHERE 1=1 $authorFilter
                GROUP BY p.id
                ORDER BY like_count DESC, comment_count DESC
                LIMIT 5";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getTopPerformingPosts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analyze post themes and content
     */
    private function analyzePostThemes($authorId = null) {
        $params = [];
        $authorFilter = '';
        
        if ($authorId) {
            $authorFilter = 'WHERE p.author_id = :author_id ';
            $params['author_id'] = $authorId;
        }
        
        // Get all posts content for analysis
        $sql = "SELECT content, created_at FROM Posts p $authorFilter ORDER BY created_at DESC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            $posts = $query->fetchAll();
            
            $wordFrequency = [];
            $totalWords = 0;
            $postLengths = [];
            $postTimes = [];
            
            foreach ($posts as $post) {
                // Simple word frequency analysis
                $words = str_word_count(strtolower($post['content']), 1);
                $postLengths[] = count($words);
                $postTimes[] = [
                    'time' => $post['created_at'],
                    'word_count' => count($words)
                ];
                
                foreach ($words as $word) {
                    // Skip common words and short words
                    if (strlen($word) < 4 || in_array($word, $this->getCommonWords())) {
                        continue;
                    }
                    
                    if (!isset($wordFrequency[$word])) {
                        $wordFrequency[$word] = 0;
                    }
                    $wordFrequency[$word]++;
                    $totalWords++;
                }
            }
            
            // Sort words by frequency
            arsort($wordFrequency);
            $topWords = array_slice($wordFrequency, 0, 10, true);
            
            // Calculate average post length
            $avgPostLength = count($postLengths) > 0 ? array_sum($postLengths) / count($postLengths) : 0;
            
            return [
                'top_words' => $topWords,
                'avg_post_length' => round($avgPostLength),
                'total_words_analyzed' => $totalWords,
                'post_lengths' => $postLengths,
                'post_times' => $postTimes
            ];
            
        } catch (Exception $e) {
            error_log("Error in analyzePostThemes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get engagement trends over time
     */
    private function getEngagementTrends($authorId = null) {
        $params = [];
        $authorFilter = '';
        
        if ($authorId) {
            $authorFilter = 'AND p.author_id = :author_id ';
            $params['author_id'] = $authorId;
        }
        
        $sql = "SELECT 
                    DATE(p.created_at) as post_date,
                    COUNT(DISTINCT p.id) as post_count,
                    COUNT(DISTINCT r.id) as like_count,
                    COUNT(DISTINCT c.id) as comment_count,
                    HOUR(p.created_at) as hour_of_day
                FROM Posts p
                LEFT JOIN Reactions r ON p.id = r.post_id AND r.type = 'heart'
                LEFT JOIN Comments c ON p.id = c.post_id
                WHERE 1=1 $authorFilter
                GROUP BY DATE(p.created_at), HOUR(p.created_at)
                ORDER BY post_date, hour_of_day";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            $rawData = $query->fetchAll();
            
            // Process data for charting
            $dailyStats = [];
            $hourlyStats = array_fill(0, 24, ['posts' => 0, 'likes' => 0, 'comments' => 0]);
            
            foreach ($rawData as $row) {
                $date = $row['post_date'];
                $hour = (int)$row['hour_of_day'];
                
                if (!isset($dailyStats[$date])) {
                    $dailyStats[$date] = [
                        'date' => $date,
                        'posts' => 0,
                        'likes' => 0,
                        'comments' => 0
                    ];
                }
                
                $dailyStats[$date]['posts'] += $row['post_count'];
                $dailyStats[$date]['likes'] += $row['like_count'];
                $dailyStats[$date]['comments'] += $row['comment_count'];
                
                $hourlyStats[$hour]['posts'] += $row['post_count'];
                $hourlyStats[$hour]['likes'] += $row['like_count'];
                $hourlyStats[$hour]['comments'] += $row['comment_count'];
            }
            
            return [
                'daily' => array_values($dailyStats),
                'hourly' => $hourlyStats
            ];
            
        } catch (Exception $e) {
            error_log("Error in getEngagementTrends: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get AI-powered recommendations based on analytics
     */
    public function getRecommendations($authorId = null) {
        $analytics = $this->getPostAnalytics($authorId);
        $recommendations = [];
        
        // Best time to post
        if (!empty($analytics['time_analysis'])) {
            $bestTime = $analytics['time_analysis'][0];
            $recommendations[] = [
                'type' => 'best_time',
                'title' => 'Best Time to Post',
                'description' => sprintf(
                    'Your posts get the most engagement on %s around %s:00 - %s:59 (average %.1f likes per post)',
                    $bestTime['day_of_week'],
                    $bestTime['hour_of_day'],
                    $bestTime['hour_of_day'] + 1,
                    $bestTime['avg_likes']
                )
            ];
        }
        
        // Post length analysis
        if (!empty($analytics['theme_analysis']['avg_post_length'])) {
            $avgLength = $analytics['theme_analysis']['avg_post_length'];
            $recommendation = [
                'type' => 'post_length',
                'title' => 'Optimal Post Length',
                'description' => sprintf(
                    'Your average post contains %d words. ', 
                    $avgLength
                )
            ];
            
            if ($avgLength < 50) {
                $recommendation['description'] .= 'Consider writing more detailed posts as longer posts tend to get more engagement.';
            } elseif ($avgLength > 300) {
                $recommendation['description'] .= 'Your posts are quite long. Consider breaking them into smaller, more digestible pieces.';
            } else {
                $recommendation['description'] .= 'Your post length is in a good range for engagement.';
            }
            
            $recommendations[] = $recommendation;
        }
        
        // Top themes
        if (!empty($analytics['theme_analysis']['top_words'])) {
            $topWords = array_keys($analytics['theme_analysis']['top_words']);
            $recommendations[] = [
                'type' => 'top_themes',
                'title' => 'Popular Themes',
                'description' => 'Your most discussed topics: ' . implode(', ', array_slice($topWords, 0, 5))
            ];
        }
        
        // Engagement rate
        if (!empty($analytics['time_analysis'])) {
            $totalPosts = array_sum(array_column($analytics['time_analysis'], 'posts_count'));
            $postsWithLikes = 0;
            
            foreach ($analytics['time_analysis'] as $stat) {
                $postsWithLikes += $stat['posts_with_likes'];
            }
            
            $engagementRate = $totalPosts > 0 ? ($postsWithLikes / $totalPosts) * 100 : 0;
            
            $recommendations[] = [
                'type' => 'engagement_rate',
                'title' => 'Engagement Rate',
                'description' => sprintf(
                    '%.1f%% of your posts receive likes. ', 
                    $engagementRate
                ) . ($engagementRate < 50 ? 'Try experimenting with different content types and posting times.' : 'Great job! Your engagement rate is looking good.')
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Get common words to filter out from analysis
     */
    private function getCommonWords() {
        return [
            'the', 'and', 'you', 'that', 'was', 'for', 'are', 'with', 'this', 'have',
            'from', 'they', 'would', 'there', 'their', 'what', 'about', 'when', 'which',
            'your', 'said', 'could', 'been', 'were', 'than', 'that', 'like', 'just',
            'will', 'then', 'them', 'some', 'would', 'make', 'into', 'time', 'look',
            'more', 'go', 'come', 'did', 'number', 'no', 'way', 'people', 'my', 'over',
            'know', 'than', 'then', 'its', 'only', 'think', 'also', 'back', 'after',
            'use', 'two', 'how', 'our', 'work', 'first', 'well', 'even', 'new', 'want',
            'because', 'any', 'these', 'give', 'day', 'most', 'us', 'can', 'has', 'had',
            'but', 'not', 'what', 'all', 'were', 'we', 'when', 'your', 'can', 'said',
            'there', 'use', 'an', 'each', 'which', 'she', 'do', 'how', 'their', 'if',
            'will', 'up', 'other', 'about', 'out', 'many', 'then', 'them', 'these', 'so',
            'some', 'her', 'would', 'make', 'like', 'him', 'into', 'time', 'has', 'look',
            'two', 'more', 'write', 'go', 'see', 'number', 'no', 'way', 'could', 'people',
            'my', 'than', 'first', 'water', 'been', 'call', 'who', 'oil', 'its', 'now',
            'find', 'long', 'down', 'day', 'did', 'get', 'come', 'made', 'may', 'part'
        ];
    }
}

?>
