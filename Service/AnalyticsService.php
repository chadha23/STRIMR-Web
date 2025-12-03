<?php

namespace Service;

class AnalyticsService
{
    private $aiServiceUrl;
    
    public function __construct()
    {
        $this->aiServiceUrl = 'http://localhost:5000';
    }
    
    /**
     * Analyze a single post
     */
    public function analyzePost($postData)
    {
        return $this->makeRequest('/analyze', $postData);
    }
    
    /**
     * Train the AI model with historical data
     */
    public function trainModel($trainingData)
    {
        return $this->makeRequest('/train', ['posts' => $trainingData]);
    }
    
    /**
     * Get engagement analytics for multiple posts
     */
    public function getEngagementAnalytics($posts)
    {
        $results = [];
        foreach ($posts as $post) {
            $results[] = $this->analyzePost([
                'content' => $post['content'],
                'created_at' => $post['created_at']
            ]);
        }
        return $results;
    }
    
    /**
     * Generate insights from analyzed posts
     */
    public function generateInsights($analyzedPosts)
    {
        $insights = [
            'engagement_scores' => [],
            'sentiment_scores' => [],
            'best_time_to_post' => [],
            'topic_distribution' => []
        ];
        
        $hourlyEngagement = array_fill(0, 24, ['count' => 0, 'total' => 0]);
        $topicCounts = [];
        
        foreach ($analyzedPosts as $post) {
            // Track engagement by hour
            $hour = $post['features']['hour_of_day'];
            $hourlyEngagement[$hour]['count']++;
            $hourlyEngagement[$hour]['total'] += $post['engagement_score'];
            
            // Track topics
            $topic = $post['topic'];
            $topicCounts[$topic] = ($topicCounts[$topic] ?? 0) + 1;
            
            // Track sentiment
            $insights['sentiment_scores'][] = $post['sentiment']['compound'];
            $insights['engagement_scores'][] = $post['engagement_score'];
        }
        
        // Calculate average engagement by hour
        foreach ($hourlyEngagement as $hour => $data) {
            $insights['best_time_to_post'][$hour] = $data['count'] > 0 
                ? $data['total'] / $data['count'] 
                : 0;
        }
        
        // Sort topics by frequency
        arsort($topicCounts);
        $insights['topic_distribution'] = $topicCounts;
        
        // Calculate overall metrics
        $insights['average_engagement'] = !empty($insights['engagement_scores']) 
            ? array_sum($insights['engagement_scores']) / count($insights['engagement_scores'])
            : 0;
            
        $insights['average_sentiment'] = !empty($insights['sentiment_scores'])
            ? array_sum($insights['sentiment_scores']) / count($insights['sentiment_scores'])
            : 0;
            
        return $insights;
    }
    
    private function makeRequest($endpoint, $data)
    {
        $ch = curl_init($this->aiServiceUrl . $endpoint);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('AI Service Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new \Exception('AI Service Error: HTTP ' . $httpCode);
        }
        
        return json_decode($response, true) ?? [];
    }
}
